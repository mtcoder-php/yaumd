<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Talaba (va boshqa har qanday login qilgan foydalanuvchi) uchun kurslar
 * katalogi — o'zi ko'rib, tanlab, kerak bo'lsa sotib olib/yozilib
 * boshlashi mumkin bo'lgan sahifa. "Kurslarim" (StudentCourseController)
 * bilan ADASHTIRMASLIK kerak: u FAQAT allaqachon yozilgan (Enrollment
 * mavjud) kurslarni ko'rsatadi, bu yerda esa hali yozilmagan kurslar ham
 * ko'rinadi — "sotib olish"/"bepul yozilish" tugmasi shu yerda joylashadi.
 *
 * MUHIM (ataylab qilingan cheklov): faqat 'open', 'free' va 'paid'
 * turidagi kurslar ko'rsatiladi. 'students_only' turi — yo'nalish/guruh
 * bo'yicha cheklangan kurslar — bu yerga ATAYLAB kiritilmagan, chunki bu
 * boshqa (yo'nalish/guruhga biriktirish) mantiqni talab qiladi va joriy
 * vazifa doirasidan tashqarida.
 */
class CourseCatalogController extends Controller
{
    private const CATALOG_TYPES = ['open', 'free', 'paid'];

    public function index(Request $request): Response
    {
        $query = Course::with('category')
            ->where('status', 'published')
            ->whereIn('type', self::CATALOG_TYPES)
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('search'), fn ($q) => $q->where('title_uz', 'like', '%'.$request->search.'%'))
            ->orderByDesc('created_at');

        $userId = $request->user()->id;
        $enrolledCourseIds = Enrollment::where('user_id', $userId)->pluck('course_id');

        $courses = $query->paginate(24)->withQueryString();
        $courses->getCollection()->transform(function (Course $course) use ($enrolledCourseIds) {
            $course->is_enrolled = $enrolledCourseIds->contains($course->id);

            return $course;
        });

        return Inertia::render('Student/Courses/Index', [
            'courses'    => $courses,
            'categories' => CourseCategory::where('is_active', true)->orderBy('name_uz')->get(['id', 'name_uz']),
            'filters'    => $request->only(['category_id', 'search']),
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $course = Course::with('category', 'instructors')
            ->where('status', 'published')
            ->whereIn('type', self::CATALOG_TYPES)
            ->findOrFail($id);

        $userId = $request->user()->id;

        return Inertia::render('Student/Courses/Show', [
            'course'     => $course,
            // Talaba bu kursga ALLAQACHON yozilganmi (bepul yoki pullik,
            // farqi yo'q) — true bo'lsa "Kurslarimga o'tish" ko'rsatiladi.
            'isEnrolled' => Enrollment::where('course_id', $id)->where('user_id', $userId)->exists(),
        ]);
    }

    // Bepul ('free') yoki 'open' turidagi kurslarga bir bosishda,
    // to'lovsiz yozilish. Pullik ('paid') kurslar uchun bu yo'l YOPIQ —
    // ular faqat CoursePurchaseController orqali (Click/Payme) o'tadi.
    public function enrollFree(Request $request, int $id)
    {
        $course = Course::where('status', 'published')
            ->whereIn('type', ['open', 'free'])
            ->findOrFail($id);

        Enrollment::updateOrCreate([
            'course_id' => $course->id,
            'user_id'   => $request->user()->id,
        ], [
            'payment_type'   => 'free',
            'payment_status' => 'paid',
            'amount'         => 0,
            'status'         => 'active',
            'enrolled_at'    => now(),
        ]);

        return redirect()->route('admin.my-courses.show', $course->id)
            ->with('success', "Kursga muvaffaqiyatli yozildingiz!");
    }
}
