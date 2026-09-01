<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Direction;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Course::with(['category', 'creator'])
            ->withCount('enrollments')
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q) use ($search) {
                    $q->where('title_uz', 'like', "%{$search}%")
                        ->orWhere('title_ru', 'like', "%{$search}%")
                        ->orWhere('title_en', 'like', "%{$search}%");
                });
            })
            ->latest();

        return Inertia::render('Admin/Courses/Index', [
            'courses'    => $query->paginate(20)->withQueryString(),
            'categories' => CourseCategory::orderBy('name_uz')->get(['id', 'name_uz']),
            'filters'    => $request->only(['category_id', 'status', 'type', 'search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Courses/Create', $this->formData());
    }

    public function store(StoreCourseRequest $request)
    {
        $data = $this->prepareData($request);
        $data['created_by'] = auth()->id();

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course = Course::create($data);
        $this->syncRelations($course, $request);

        return redirect()->route('admin.courses.show', $course->id)
            ->with('success', 'Kurs yaratildi! Endi modul va darslar qo\'shishingiz mumkin.');
    }

    public function show(int $id): Response
    {
        $course = Course::with([
            'category', 'creator', 'instructors', 'directions', 'groups',
            'modules.lessons.video', 'modules.lessons.attachments',
        ])->withCount('enrollments')->findOrFail($id);

        return Inertia::render('Admin/Courses/Show', [
            'course' => $course,
        ]);
    }

    public function edit(int $id): Response
    {
        $course = Course::with(['instructors:id', 'directions:id', 'groups:id'])->findOrFail($id);

        return Inertia::render('Admin/Courses/Edit', array_merge(
            $this->formData(),
            ['course' => $course],
        ));
    }

    public function update(UpdateCourseRequest $request, int $id)
    {
        $course = Course::findOrFail($id);
        $data = $this->prepareData($request);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course->update($data);
        $this->syncRelations($course, $request);

        return redirect()->route('admin.courses.show', $course->id)
            ->with('success', 'Kurs yangilandi!');
    }

    public function destroy(int $id)
    {
        $course = Course::withCount('enrollments')->findOrFail($id);

        if ($course->enrollments_count > 0) {
            return back()->with('error', "Bu kursga {$course->enrollments_count} ta talaba yozilgan — o'chirib bo'lmaydi. Uni arxivlashingiz mumkin.");
        }

        $course->delete();

        return back()->with('success', "Kurs o'chirildi!");
    }

    private function prepareData(StoreCourseRequest|UpdateCourseRequest $request): array
    {
        $data = $request->validated();

        unset($data['instructor_ids'], $data['direction_ids'], $data['group_ids']);

        $data['what_you_learn'] = $this->linesToArray($request->input('what_you_learn'));
        $data['requirements']   = $this->linesToArray($request->input('requirements'));

        return $data;
    }

    private function linesToArray(?string $text): array
    {
        if (! $text) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function syncRelations(Course $course, Request $request): void
    {
        $course->instructors()->sync($request->input('instructor_ids', []));
        $course->directions()->sync($request->input('direction_ids', []));
        $course->groups()->sync($request->input('group_ids', []));
    }

    private function formData(): array
    {
        return [
            'categories' => CourseCategory::orderBy('name_uz')->get(['id', 'name_uz']),
            'directions' => Direction::orderBy('name_uz')->get(['id', 'name_uz']),
            'groups'     => StudentGroup::orderBy('name')->get(['id', 'name']),
            'teachers'   => User::role('teacher')->orderBy('full_name')->get(['id', 'full_name']),
        ];
    }
}
