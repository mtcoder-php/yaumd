<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AttendanceNotification;
use App\Models\Contract;
use App\Models\Student;
use App\Models\User;
use App\Services\ContractPaymentScheduleService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Telegram serveridan to'g'ridan-to'g'ri keladigan webhook — Click/Payme
 * callback'lari bilan bir xil naqsh: login talab qilinmaydi, CSRF
 * tekshiruvi o'chirilgan (bootstrap/app.php'ga qarang), o'zining xavfsizlik
 * tekshiruvi esa yo'ldagi maxfiy segment (secret) orqali amalga oshadi —
 * Telegram bu yo'lni bilmagan hech kim bu yerga to'g'ri so'rov yubora
 * olmaydi.
 *
 * BITTA bot ham talabalarga (kontrakt to'lovi holati), ham xodimlarga
 * (kelish-ketish/davomat xabarnomalari) xizmat qiladi — kim ekanligi
 * ulanish jarayonida (Student yoki User modeliga) aniqlanadi, shundan
 * keyin har bir buyruq (masalan /holat) shu turga qarab javob beradi.
 *
 * Ulanish ikki xil usulda:
 *   1) "Telefon raqamni ulashish" tugmasi orqali — raqam avval talabalar,
 *      keyin xodimlar (users, "student" rolisiz) bazasidagi telefon bilan
 *      solishtirilib avtomatik bog'lanadi;
 *   2) Shaxsiy kabinetda ("Mening shartnomam" — talaba) yoki "Mening
 *      profilim" (xodim) sahifasida olingan bir martalik kod orqali —
 *      "/start <kod>" chuqur havolasi yoki "/kod <kod>" matni.
 */
class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramService $telegram,
        private ContractPaymentScheduleService $schedule,
    ) {
    }

    public function handle(Request $request, string $secret)
    {
        abort_unless(hash_equals((string) config('services.telegram.webhook_secret'), $secret), 404);

        $message = $request->input('message');

        if (! is_array($message)) {
            // Boshqa turdagi yangilanishlar (edited_message, callback_query
            // va h.k.) hozircha e'tiborga olinmaydi — Telegram baribir tez
            // 200 javobni kutadi, aks holda qayta-qayta yuborishga urinadi.
            return response()->json(['ok' => true]);
        }

        $chatId = (string) ($message['chat']['id'] ?? '');
        $text = trim((string) ($message['text'] ?? ''));
        $contact = $message['contact'] ?? null;

        if (! $chatId) {
            return response()->json(['ok' => true]);
        }

        if (is_array($contact) && ! empty($contact['phone_number'])) {
            $this->linkByPhone($chatId, (string) $contact['phone_number']);
        } elseif (str_starts_with($text, '/start')) {
            $this->handleStart($chatId, trim(substr($text, 6)));
        } elseif (str_starts_with($text, '/kod') || str_starts_with($text, '/link')) {
            $code = trim(preg_replace('/^\/(kod|link)\s*/i', '', $text));
            $this->linkByCode($chatId, $code);
        } elseif (str_starts_with($text, '/holat') || str_starts_with($text, '/status')) {
            $this->sendStatus($chatId);
        } elseif (str_starts_with($text, '/yordam') || str_starts_with($text, '/help')) {
            $this->sendHelp($chatId);
        } else {
            $this->telegram->sendMessage($chatId, "Tushunmadim 🤔\n\nBuyruqlar ro'yxati uchun /yordam yozing.");
        }

        return response()->json(['ok' => true]);
    }

    private function handleStart(string $chatId, string $payload): void
    {
        // Chuqur havola orqali kelgan bo'lsa ("t.me/bot?start=123456"),
        // Telegram shu kodni "/start 123456" ko'rinishida yuboradi —
        // to'g'ridan-to'g'ri kod bilan bog'lashga urinamiz.
        if ($payload !== '' && ctype_digit($payload)) {
            $this->linkByCode($chatId, $payload);

            return;
        }

        if ($this->findLinked($chatId)) {
            $this->sendHelp($chatId);

            return;
        }

        $this->telegram->sendMessage(
            $chatId,
            "Assalomu alaykum! 👋\n\n".
            "Bu bot orqali <b>Yangi Asr Universiteti</b>da talaba bo'lsangiz — shartnoma to'lovingiz holatini, xodim bo'lsangiz — kelish-ketish (davomat) holatingizni kuzatib borishingiz mumkin.\n\n".
            "Hisobingizni ulash uchun quyidagi tugma orqali telefon raqamingizni yuboring, YOKI shaxsiy kabinetingizdagi (\"Mening shartnomam\" yoki \"Mening profilim\") \"Telegram bot\" bo'limidan olingan kodni <code>/kod 123456</code> ko'rinishida yuboring.",
            [[['text' => '📱 Telefon raqamni ulashish', 'request_contact' => true]]]
        );
    }

    /**
     * Shu chat_id allaqachon Student yoki User (xodim)ga ulanganmi —
     * ulangan bo'lsa, aynan qaysi biriga ekanini birga qaytaradi.
     *
     * @return array{type: string, model: Student|User}|null
     */
    private function findLinked(string $chatId): ?array
    {
        if ($student = Student::where('telegram_chat_id', $chatId)->first()) {
            return ['type' => 'student', 'model' => $student];
        }

        if ($user = User::where('telegram_chat_id', $chatId)->first()) {
            return ['type' => 'staff', 'model' => $user];
        }

        return null;
    }

    private function linkByPhone(string $chatId, string $phone): void
    {
        $normalized = $this->normalizePhone($phone);

        $students = Student::whereNotNull('phone')->get()
            ->filter(fn (Student $s) => $this->normalizePhone((string) $s->phone) === $normalized);

        // Xodimlar — "student" rolidagi (faqat talaba portaliga kirish
        // uchun yaratilgan) hisoblar bundan mustasno, PersonMatchingService'
        // dagi buildStaffPool() bilan bir xil mantiq.
        $staff = User::whereNotNull('phone')
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'student'))
            ->get()
            ->filter(fn (User $u) => $this->normalizePhone((string) $u->phone) === $normalized);

        $totalMatches = $students->count() + $staff->count();

        if ($totalMatches !== 1) {
            $this->telegram->sendMessage(
                $chatId,
                $totalMatches === 0
                    ? "Kechirasiz, bu telefon raqami bo'yicha yozuv topilmadi. Iltimos, shaxsiy kabinetingizdagi bir martalik kod orqali ulaning (\"Telegram bot\" bo'limi)."
                    : "Bu telefon raqami bir nechta yozuvga tegishli topildi — iltimos, shaxsiy kabinetingizdagi bir martalik kod orqali ulaning."
            );

            return;
        }

        if ($students->isNotEmpty()) {
            $this->completeLink($chatId, 'student', $students->first());
        } else {
            $this->completeLink($chatId, 'staff', $staff->first());
        }
    }

    private function linkByCode(string $chatId, string $code): void
    {
        $code = preg_replace('/\D/', '', $code);

        $cached = $code !== '' ? Cache::get("telegram-link-code.{$code}") : null;

        $type = is_array($cached) ? ($cached['type'] ?? null) : null;
        $id = is_array($cached) ? ($cached['id'] ?? null) : null;

        $model = match ($type) {
            'student' => $id ? Student::find($id) : null,
            'staff' => $id ? User::find($id) : null,
            default => null,
        };

        if (! $model) {
            $this->telegram->sendMessage($chatId, "Kod noto'g'ri yoki muddati o'tgan. Shaxsiy kabinetingizdan yangi kod oling.");

            return;
        }

        Cache::forget("telegram-link-code.{$code}");
        $this->completeLink($chatId, $type, $model);
    }

    private function completeLink(string $chatId, string $type, Student|User $person): void
    {
        // Bitta Telegram hisobi faqat bitta shaxsga bog'lansin — avval shu
        // chat_id boshqa talaba/xodimga ulangan bo'lsa, eskisi bo'shatiladi.
        Student::where('telegram_chat_id', $chatId)->update(['telegram_chat_id' => null, 'telegram_linked_at' => null]);
        User::where('telegram_chat_id', $chatId)->update(['telegram_chat_id' => null, 'telegram_linked_at' => null]);

        $person->telegram_chat_id = $chatId;
        $person->telegram_linked_at = now();
        $person->save();

        $name = $type === 'student' ? $person->fullName() : $person->full_name;

        $followUp = $type === 'student'
            ? "Endi shartnoma to'lovingiz holatini istalgan payt /holat buyrug'i orqali bilib turishingiz, shuningdek to'lov muddati yaqinlashganda avtomatik eslatma olishingiz mumkin."
            : "Endi turniketdan o'tganingizda kelish-ketish vaqtingiz, shuningdek ish kuni yakunida kech qolgan/erta ketgan bo'lsangiz shu haqda avtomatik xabar olasiz.";

        $this->telegram->sendMessage(
            $chatId,
            "Xush kelibsiz, <b>{$name}</b>! ✅\n\nHisobingiz muvaffaqiyatli ulandi. {$followUp}"
        );
    }

    private function sendStatus(string $chatId): void
    {
        $linked = $this->findLinked($chatId);

        if (! $linked) {
            $this->telegram->sendMessage($chatId, "Hisobingiz hali ulanmagan. Boshlash uchun /start yozing.");

            return;
        }

        if ($linked['type'] === 'staff') {
            $this->sendStaffStatus($chatId, $linked['model']);

            return;
        }

        $student = $linked['model'];

        if ($student->funding_type === 'grant') {
            $this->telegram->sendMessage($chatId, "🎓 Siz grant asosida o'qiysiz — kontrakt to'lovi talab qilinmaydi.");

            return;
        }

        $contract = $this->schedule->findActiveContract($student);

        if (! $contract) {
            $this->telegram->sendMessage($chatId, "Sizga biriktirilgan faol kontrakt topilmadi.");

            return;
        }

        $status = $this->schedule->currentStatus($contract);
        $this->telegram->sendMessage($chatId, $this->formatStatusMessage($student, $contract, $status));
    }

    /**
     * Xodim uchun /holat — bugungi kech qolish/erta ketish xabarnomasi
     * allaqachon yuborilgan bo'lsa shuni eslatadi, aks holda hali hech
     * narsa aniqlanmaganini aytadi (kunlik tekshiruv kechqurun ishlaydi,
     * qarang: NotifyStaffAttendance).
     */
    private function sendStaffStatus(string $chatId, User $user): void
    {
        $today = AttendanceNotification::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->get();

        if ($today->isEmpty()) {
            $this->telegram->sendMessage(
                $chatId,
                "👤 Xodim sifatida ulangansiz.\n\nBugungi kelish-ketish holatingiz ish kuni yakunida (kechqurun) avtomatik tekshiriladi — muammo bo'lsa shu yerga xabar keladi."
            );

            return;
        }

        $lines = $today->map(function (AttendanceNotification $n) {
            return $n->type === AttendanceNotification::TYPE_LATE
                ? "⏰ Bugun {$n->minutes} daqiqa kech qoldingiz."
                : "🚪 Bugun {$n->minutes} daqiqa erta ketdingiz.";
        });

        $this->telegram->sendMessage($chatId, $lines->implode("\n"));
    }

    public function formatStatusMessage(Student $student, Contract $contract, array $status): string
    {
        $fmt = fn (float $v) => number_format($v, 0, '.', ' ') . " so'm";

        if ($status['is_fully_paid']) {
            return "✅ Tabriklaymiz! Kontraktingiz to'liq to'langan ({$fmt((float) $contract->amount)}).";
        }

        $lines = [];

        if (! $status['known']) {
            $lines[] = "⚠️ Guruh/o'quv yili ma'lumoti to'liq emas — aniq holatni admin bilan tekshiring.";
            $lines[] = "Jami to'langan: " . $fmt($status['paid_amount']);

            return implode("\n", $lines);
        }

        if ($status['is_compliant']) {
            $lines[] = "✅ Bu oy uchun to'lov muammosi yo'q, turniketda muammo bo'lmaydi.";
        } else {
            $lines[] = "❌ To'lovda muammo bor — turniketdan o'tishda qiyinchilik bo'lishi mumkin.";
            $lines[] = "Qarz: <b>" . $fmt($status['debt_amount']) . "</b>";
        }

        $lines[] = "";
        $lines[] = "Jami to'langan: " . $fmt($status['paid_amount']);
        $lines[] = "Shu kungacha kerak edi: " . $fmt($status['required_amount']);

        if ($status['next_deadline']) {
            $lines[] = "Keyingi muddat: " . $status['next_deadline']->format('d.m.Y')
                . " (" . $fmt((float) $status['next_required_amount']) . " gacha)";

            // MUHIM: "is_compliant" faqat O'TGAN muddat bo'yicha qarz
            // yo'qligini bildiradi — masalan birinchi muddatdan OLDIN u
            // har doim true bo'ladi, lekin talaba hali KELAYOTGAN muddat
            // uchun yetarli to'lamagan bo'lishi mumkin. Shu sababli, agar
            // hozirgi holat "muammo yo'q" bo'lsa ham, kelayotgan muddat
            // uchun alohida ogohlantirish ko'rsatiladi (aks holda talaba
            // noto'g'ri tinch bo'lib qolishi mumkin).
            if ($status['is_compliant'] && ! $status['is_compliant_for_next']) {
                $amountNeeded = max(0, $status['next_required_amount'] - $status['paid_amount']);
                $lines[] = "⏳ Bu muddatgacha yana <b>" . $fmt($amountNeeded) . "</b> to'lashingiz kerak.";
            }
        }

        return implode("\n", $lines);
    }

    private function sendHelp(string $chatId): void
    {
        $this->telegram->sendMessage(
            $chatId,
            "<b>Buyruqlar:</b>\n".
            "/holat — shartnoma to'lovi holatini ko'rish\n".
            "/yordam — shu xabarni qayta ko'rsatish"
        );
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        // Oxirgi 9 ta raqam bilan solishtiramiz — "+998901234567",
        // "998901234567", "901234567", "0901234567" barchasi bir xil
        // hisoblanishi uchun (mamlakat/operator kod prefikslaridagi farq
        // ta'sir qilmasin).
        return substr($digits, -9);
    }
}
