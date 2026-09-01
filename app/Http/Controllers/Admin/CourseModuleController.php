<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseModuleRequest;
use App\Http\Requests\UpdateCourseModuleRequest;
use App\Models\Course;
use App\Models\CourseModule;

class CourseModuleController extends Controller
{
    public function store(StoreCourseModuleRequest $request, int $courseId)
    {
        $course = Course::findOrFail($courseId);
        $data = $request->validated();
        $data['order'] = $data['order'] ?? (($course->modules()->max('order') ?? 0) + 1);

        $course->modules()->create($data);

        return back()->with('success', "Modul qo'shildi!");
    }

    public function update(UpdateCourseModuleRequest $request, int $courseId, int $moduleId)
    {
        $module = CourseModule::where('course_id', $courseId)->findOrFail($moduleId);
        $module->update($request->validated());

        return back()->with('success', 'Modul yangilandi!');
    }

    public function destroy(int $courseId, int $moduleId)
    {
        $module = CourseModule::where('course_id', $courseId)->withCount('lessons')->findOrFail($moduleId);

        if ($module->lessons_count > 0) {
            return back()->with('error', "Bu modulda {$module->lessons_count} ta dars bor. Avval darslarni o'chiring!");
        }

        $module->delete();

        return back()->with('success', "Modul o'chirildi!");
    }
}
