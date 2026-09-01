<?php

namespace App\Services;

use App\Models\ScormPackage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

/**
 * SCORM 1.2 / SCORM 2004 / xAPI (Tin Can) ZIP paketini qabul qilib:
 *  1) uni storage/app/public/scorm/{uuid}/ ichiga yechadi (shu orqali
 *     brauzer paket ichidagi HTML/JS/CSS fayllarga to'g'ridan-to'g'ri,
 *     "public/storage" yorlig'i orqali kira oladi — Laravel orqali
 *     "proksi" qilinmaydi, bu tezroq va sodda);
 *  2) imsmanifest.xml (SCORM) yoki tincan.xml (xAPI) faylini o'qib,
 *     versiyani (scorm12/scorm2004/xapi) va ishga tushirish faylini
 *     (launch_url) avtomatik aniqlaydi;
 *  3) ScormPackage yozuvini yaratadi.
 *
 * DOIRA: faqat bitta SCO (murakkab sequencing/navigation, bir nechta
 * SCO orasidagi shartli o'tishlar qo'llab-quvvatlanmaydi) — manifestdagi
 * standart (default) tashkilotning BIRINCHI ishga tushiriladigan
 * item'i olinadi.
 */
class ScormPackageService
{
    public function importFromZip(UploadedFile $file, string $title): ScormPackage
    {
        $folder = 'scorm/'.(string) Str::uuid();
        $extractPath = Storage::disk('public')->path($folder);

        if (! is_dir($extractPath)) {
            mkdir($extractPath, 0755, true);
        }

        $zip = new ZipArchive();
        $opened = $zip->open($file->getRealPath());
        if ($opened !== true) {
            throw new RuntimeException("SCORM/xAPI paketini ochib bo'lmadi — zip fayl buzilgan bo'lishi mumkin.");
        }
        $zip->extractTo($extractPath);
        $zip->close();

        // Avval tincan.xml qidiriladi — bo'lsa, bu xAPI (Tin Can) paketi.
        $tincanPath = $this->findFile($extractPath, 'tincan.xml');
        if ($tincanPath) {
            return $this->buildFromTincan($folder, $extractPath, $tincanPath, $title, $file->getSize());
        }

        // Aks holda imsmanifest.xml — SCORM 1.2 yoki SCORM 2004.
        $manifestPath = $this->findFile($extractPath, 'imsmanifest.xml');
        if (! $manifestPath) {
            Storage::disk('public')->deleteDirectory($folder);
            throw new RuntimeException("Paket ichida imsmanifest.xml yoki tincan.xml topilmadi — bu to'g'ri SCORM/xAPI paketi emasga o'xshaydi.");
        }

        return $this->buildFromManifest($folder, $extractPath, $manifestPath, $title, $file->getSize());
    }

    public function delete(ScormPackage $package): void
    {
        if ($package->path) {
            Storage::disk('public')->deleteDirectory($package->path);
        }
        $package->delete();
    }

    /**
     * Berilgan nom bilan faylni avval papka ildizida, topilmasa birinchi
     * darajadagi pastki papkalarda qidiradi (ba'zi eksport vositalari
     * manifestni bitta ichki papka ichiga joylaydi).
     */
    private function findFile(string $root, string $name): ?string
    {
        if (is_file($root.'/'.$name)) {
            return $root.'/'.$name;
        }

        foreach (glob($root.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
            if (is_file($dir.'/'.$name)) {
                return $dir.'/'.$name;
            }
        }

        return null;
    }

    private function relativeToRoot(string $root, string $absoluteFile): string
    {
        $rel = ltrim(str_replace('\\', '/', substr($absoluteFile, strlen($root))), '/');

        return $rel;
    }

    private function buildFromManifest(string $folder, string $root, string $manifestPath, string $title, int $size): ScormPackage
    {
        $xml = @simplexml_load_file($manifestPath);
        if (! $xml) {
            Storage::disk('public')->deleteDirectory($folder);
            throw new RuntimeException("imsmanifest.xml faylini o'qib bo'lmadi (XML formati noto'g'ri).");
        }

        $rawXml = (string) file_get_contents($manifestPath);
        $schemaVersionText = (string) ($xml->metadata->schemaversion ?? '');
        $isScorm2004 = str_contains($schemaVersionText, '2004')
            || str_contains($rawXml, 'adlcp_v1p3')
            || str_contains($rawXml, 'imsss');
        $version = $isScorm2004 ? 'scorm2004' : 'scorm12';

        $identifier = (string) ($xml['identifier'] ?? (string) Str::uuid());

        $defaultOrgId = (string) ($xml->organizations['default'] ?? '');
        $organization = null;
        foreach ($xml->organizations->organization as $org) {
            if ($defaultOrgId === '' || (string) $org['identifier'] === $defaultOrgId) {
                $organization = $org;
                break;
            }
        }
        if (! $organization && isset($xml->organizations->organization[0])) {
            $organization = $xml->organizations->organization[0];
        }
        if (! $organization) {
            Storage::disk('public')->deleteDirectory($folder);
            throw new RuntimeException('manifest ichida <organization> topilmadi.');
        }

        $launchItem = $this->firstLaunchableItem($organization);
        if (! $launchItem) {
            Storage::disk('public')->deleteDirectory($folder);
            throw new RuntimeException("manifest ichida ishga tushiriladigan <item> (identifierref bilan) topilmadi.");
        }

        $refId = (string) $launchItem['identifierref'];
        $resource = null;
        foreach ($xml->resources->resource as $res) {
            if ((string) $res['identifier'] === $refId) {
                $resource = $res;
                break;
            }
        }

        $href = $resource ? (string) $resource['href'] : '';
        if ($href === '') {
            Storage::disk('public')->deleteDirectory($folder);
            throw new RuntimeException("manifest ichida ishga tushirish fayli (resource href) topilmadi.");
        }

        $launchAbsolute = $root.'/'.ltrim($href, '/');
        if (! is_file($launchAbsolute)) {
            Storage::disk('public')->deleteDirectory($folder);
            throw new RuntimeException("Manifestda ko'rsatilgan ishga tushirish fayli ({$href}) paket ichida topilmadi.");
        }

        return ScormPackage::create([
            'title'      => $title,
            'version'    => $version,
            'path'       => $folder,
            'launch_url' => $this->relativeToRoot($root, $launchAbsolute),
            'identifier' => $identifier,
            'manifest'   => [
                'identifier'      => $identifier,
                'schema_version'  => $schemaVersionText ?: null,
                'organization_id' => (string) $organization['identifier'],
                'title'           => (string) ($organization->title ?? $title),
                'launch_href'     => $href,
            ],
            'file_size'  => $size,
            'is_active'  => true,
        ]);
    }

    /**
     * Organization (yoki ichma-ich item-klaster) ichidan birinchi
     * "identifierref"ga ega item'ni topadi — shu bizning yagona SCO'miz
     * bo'ladi (murakkab sequencing qo'llab-quvvatlanmaydi).
     */
    private function firstLaunchableItem(SimpleXMLElement $container): ?SimpleXMLElement
    {
        foreach ($container->item as $item) {
            if ((string) $item['identifierref'] !== '') {
                return $item;
            }

            $inner = $this->firstLaunchableItem($item);
            if ($inner) {
                return $inner;
            }
        }

        return null;
    }

    private function buildFromTincan(string $folder, string $root, string $tincanPath, string $title, int $size): ScormPackage
    {
        $xml = @simplexml_load_file($tincanPath);
        if (! $xml) {
            Storage::disk('public')->deleteDirectory($folder);
            throw new RuntimeException("tincan.xml faylini o'qib bo'lmadi (XML formati noto'g'ri).");
        }

        $activity = $xml->activities->activity ?? null;
        $launchHref = $activity ? (string) $activity->launch : '';
        if ($launchHref === '') {
            Storage::disk('public')->deleteDirectory($folder);
            throw new RuntimeException("tincan.xml ichida <launch> topilmadi.");
        }

        $launchAbsolute = $root.'/'.ltrim($launchHref, '/');
        if (! is_file($launchAbsolute)) {
            Storage::disk('public')->deleteDirectory($folder);
            throw new RuntimeException("tincan.xml'da ko'rsatilgan ishga tushirish fayli ({$launchHref}) paket ichida topilmadi.");
        }

        $activityId = $activity && (string) $activity['id'] !== '' ? (string) $activity['id'] : 'urn:yaumd:'.(string) Str::uuid();
        $activityName = $activity ? (string) ($activity->name ?? $title) : $title;

        return ScormPackage::create([
            'title'      => $title,
            'version'    => 'xapi',
            'path'       => $folder,
            'launch_url' => $this->relativeToRoot($root, $launchAbsolute),
            'identifier' => $activityId,
            'manifest'   => [
                'activity_id'   => $activityId,
                'activity_name' => $activityName,
                'launch_href'   => $launchHref,
            ],
            'file_size'  => $size,
            'is_active'  => true,
        ]);
    }
}
