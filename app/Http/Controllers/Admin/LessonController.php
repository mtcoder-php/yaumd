<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Services\LmsProgressService;
use App\Services\ScormPackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class LessonController extends Controller
{
    public function __construct(
        private ScormPackageService $scormPackages,
        private LmsProgressService $progress,
    ) {
    }

    public function create(int $courseId, int $moduleId): Response
    {
        return Inertia::render('Admin/Lessons/Create', [
            'course' => Course::select('id', 'title_uz')->findOrFail($courseId),
            'module' => CourseModule::where('course_id', $courseId)->findOrFail($moduleId),
        ]);
    }

    public function store(StoreLessonRequest $request, int $courseId, int $moduleId)
    {
        $module = CourseModule::where('course_id', $courseId)->findOrFail($moduleId);

        $data = $request->validated();
        unset($data['video_source'], $data['video_url'], $data['video_file'], $data['files'], $data['scorm_file']);
        $data['order'] = $data['order'] ?? (($module->lessons()->max('order') ?? 0) + 1);

        $lesson = $module->lessons()->create($data);

        if ($lesson->type === 'video') {
            $this->saveVideo($lesson, $request);
        }
        if ($lesson->type === 'scorm' && $request->hasFile('scorm_file')) {
            try {
                $this->saveScormPackage($lesson, $request);
            } catch (Throwable $e) {
                return redirect()->route('admin.courses.show', $courseId)
                    ->with('error', "Dars qo'shildi, lekin SCORM/xAPI paketini ochishda xatolik: {$e->getMessage()}");
            }
        }
        $this->saveFiles($lesson, $request);

        return redirect()->route('admin.courses.show', $courseId)
            ->with('success', "Dars qo'shildi!");
    }

    public function edit(int $courseId, int $lessonId): Response
    {
        $lesson = Lesson::with(['video', 'attachments', 'scormPackage'])
            ->whereHas('module', fn ($q) => $q->where('course_id', $courseId))
            ->findOrFail($lessonId);

        return Inertia::render('Admin/Lessons/Edit', [
            'course' => Course::select('id', 'title_uz')->findOrFail($courseId),
            'module' => CourseModule::select('id', 'title_uz', 'course_id')->findOrFail($lesson->module_id),
            'lesson' => $lesson,
        ]);
    }

    public function update(UpdateLessonRequest $request, int $courseId, int $lessonId)
    {
        $lesson = Lesson::with('scormPackage')
            ->whereHas('module', fn ($q) => $q->where('course_id', $courseId))
            ->findOrFail($lessonId);

        $data = $request->validated();
        unset($data['video_source'], $data['video_url'], $data['video_file'], $data['files'], $data['scorm_file']);
        $lesson->update($data);

        if ($lesson->type === 'video' && ($request->hasFile('video_file') || $request->filled('video_url'))) {
            $this->saveVideo($lesson, $request);
        }
        if ($lesson->type === 'scorm' && $request->hasFile('scorm_file')) {
            try {
                $this->saveScormPackage($lesson, $request);
            } catch (Throwable $e) {
                return redirect()->route('admin.courses.show', $courseId)
                    ->with('error', "Dars yangilandi, lekin SCORM/xAPI paketini ochishda xatolik: {$e->getMessage()}");
            }
        }
        $this->saveFiles($lesson, $request);

        return redirect()->route('admin.courses.show', $courseId)
            ->with('success', 'Dars yangilandi!');
    }

    public function destroy(int $courseId, int $lessonId)
    {
        $lesson = Lesson::with(['video', 'attachments', 'scormPackage'])
            ->whereHas('module', fn ($q) => $q->where('course_id', $courseId))
            ->findOrFail($lessonId);

        if ($lesson->video) {
            foreach (['path_360', 'path_720', 'path_1080', 'path_2k', 'path_4k', 'path_8k'] as $col) {
                if ($lesson->video->{$col}) {
                    Storage::disk('public')->delete($lesson->video->{$col});
                }
            }
        }
        foreach ($lesson->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->path);
        }
        if ($lesson->scormPackage) {
            $this->scormPackages->delete($lesson->scormPackage);
        }

        $lesson->delete();

        // Dars o'chirilgach, shu kursning BARCHA talabalari uchun
        // progress-foizni qayta hisoblaymiz — aks holda "Enrollment.progress"
        // dars o'chirilishidan OLDINGI (endi noto'g'ri) qiymatda qolib
        // ketadi, chunki u faqat dars TUGALLANGANDA yangilanadi.
        $this->progress->recalculateCourseEnrollments($courseId);

        return back()->with('success', "Dars o'chirildi!");
    }

    public function destroyAttachment(int $courseId, int $lessonId, int $attachmentId)
    {
        $attachment = LessonAttachment::where('lesson_id', $lessonId)->findOrFail($attachmentId);
        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', "Fayl o'chirildi!");
    }

    private function saveVideo(Lesson $lesson, Request $request): void
    {
        $source = $request->input('video_source', 'upload');
        $videoData = ['source' => $source];

        if ($source === 'upload' && $request->hasFile('video_file')) {
            $videoData['path_720'] = $request->file('video_file')->store('courses/videos', 'public');
        } elseif (in_array($source, ['youtube', 'vimeo'], true) && $request->filled('video_url')) {
            $videoData['url'] = $request->input('video_url');
        } else {
            return;
        }

        $lesson->video()->updateOrCreate([], $videoData);
    }

    /**
     * SCORM/xAPI ZIP paketini yechib, ScormPackage yozuvini yaratadi va
     * darsni shunga bog'laydi. Bitta darsda bir vaqtning o'zida faqat
     * bitta paket bo'lishi mumkin — yangisi yuklansa, eskisi (fayllari
     * bilan birga) o'chiriladi.
     */
    private function saveScormPackage(Lesson $lesson, Request $request): void
    {
        if ($lesson->scormPackage) {
            $this->scormPackages->delete($lesson->scormPackage);
            $lesson->scorm_package_id = null;
        }

        $package = $this->scormPackages->importFromZip(
            $request->file('scorm_file'),
            $lesson->title_uz
        );

        $lesson->update(['scorm_package_id' => $package->id]);
    }

    private function saveFiles(Lesson $lesson, Request $request): void
    {
        if (! $request->hasFile('files')) {
            return;
        }

        $nextOrder = ($lesson->attachments()->max('order') ?? -1) + 1;

        foreach ($request->file('files') as $i => $file) {
            $lesson->attachments()->create([
                'title'     => $file->getClientOriginalName(),
                'path'      => $file->store('courses/attachments', 'public'),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'type'      => $this->attachmentType($file->getClientOriginalExtension()),
                'order'     => $nextOrder + $i,
            ]);
        }
    }

    private function attachmentType(string $extension): string
    {
        return match (strtolower($extension)) {
            'pdf'         => 'pdf',
            'doc', 'docx' => 'docx',
            'ppt', 'pptx' => 'pptx',
            'xls', 'xlsx' => 'xlsx',
            default       => 'other',
        };
    }
}
