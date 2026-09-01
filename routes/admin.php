<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ApplicantController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TestQuestionController;
use App\Http\Controllers\Admin\DirectionSubjectController;
use App\Http\Controllers\Admin\TestSessionController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\DirectionController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\InterviewController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentGroupController;
use App\Http\Controllers\Admin\CourseCategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseModuleController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\StudentCourseController;


// Har bir marshrutga qo'yilgan 'permission:...' RolePermissionSeeder'dagi
// permissionlar bilan bir xil nomlanadi. Ko'rish (GET) uchun '.view',
// qo'shish uchun '.create', tahrirlash uchun '.edit', o'chirish uchun
// '.delete'. Bu Spatie Laravel Permission middleware'i orqali serverda
// haqiqiy tekshiruv o'rnatadi — AppLayout.vue'dagi rolga qarab menyu esa
// faqat interfeys qulayligi, xavfsizlik chegarasi emas edi.
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Qulaylik uchun: /admin -> /admin/dashboard (barcha login qilgan
    // foydalanuvchilar uchun ochiq — alohida permission talab qilinmaydi)
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    })->name('home');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Abituriyentlar
    Route::prefix('applicants')->name('applicants.')->group(function () {
        Route::get('/', [ApplicantController::class, 'index'])->name('index')->middleware('permission:admission.view');
        Route::get('/{id}', [ApplicantController::class, 'show'])->name('show')->middleware('permission:admission.view');
        Route::get('/{id}/edit', [ApplicantController::class, 'edit'])->name('edit')->middleware('permission:admission.edit');
        Route::put('/{id}', [ApplicantController::class, 'update'])->name('update')->middleware('permission:admission.edit');
        Route::patch('/{id}/status', [ApplicantController::class, 'updateStatus'])->name('status')->middleware('permission:admission.edit');
        Route::patch('/bulk-status', [ApplicantController::class, 'bulkUpdateStatus'])->name('bulk-status')->middleware('permission:admission.edit');
    });

    // Audit log — faqat ko'rish, faqat shu permissionga ega bo'lganlar (admin/super-admin)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index')->middleware('permission:audit.view');

    // Fanlar va savollar
    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', [SubjectController::class, 'index'])->name('index')->middleware('permission:test.view');
        Route::get('/create', [SubjectController::class, 'create'])->name('create')->middleware('permission:test.create');
        Route::post('/', [SubjectController::class, 'store'])->name('store')->middleware('permission:test.create');
        Route::get('/{id}/edit', [SubjectController::class, 'edit'])->name('edit')->middleware('permission:test.edit');
        Route::put('/{id}', [SubjectController::class, 'update'])->name('update')->middleware('permission:test.edit');
        Route::delete('/{id}', [SubjectController::class, 'destroy'])->name('destroy')->middleware('permission:test.delete');

        // Savollar — fan ichida
        Route::get('/{id}/questions', [TestQuestionController::class, 'index'])->name('questions.index')->middleware('permission:test.view');
        Route::get('/{id}/questions/create', [TestQuestionController::class, 'create'])->name('questions.create')->middleware('permission:test.create');
        Route::post('/{id}/questions', [TestQuestionController::class, 'store'])->name('questions.store')->middleware('permission:test.create');
        Route::get('/{id}/questions/template', [TestQuestionController::class, 'template'])->name('questions.template')->middleware('permission:test.view');
        Route::post('/{id}/questions/import', [TestQuestionController::class, 'import'])->name('questions.import')->middleware('permission:test.create');
        Route::get('/{id}/questions/{qId}/edit', [TestQuestionController::class, 'edit'])->name('questions.edit')->middleware('permission:test.edit');
        Route::put('/{id}/questions/{qId}', [TestQuestionController::class, 'update'])->name('questions.update')->middleware('permission:test.edit');
        Route::delete('/{id}/questions/{qId}', [TestQuestionController::class, 'destroy'])->name('questions.destroy')->middleware('permission:test.delete');
    });

    // Yo'nalish-fanlar
    Route::prefix('direction-subjects')->name('direction-subjects.')->group(function () {
        Route::get('/', [DirectionSubjectController::class, 'index'])->name('index')->middleware('permission:test.view');
        Route::post('/', [DirectionSubjectController::class, 'store'])->name('store')->middleware('permission:test.create');
        Route::put('/{id}', [DirectionSubjectController::class, 'update'])->name('update')->middleware('permission:test.edit');
        Route::delete('/{id}', [DirectionSubjectController::class, 'destroy'])->name('destroy')->middleware('permission:test.delete');
    });

    Route::prefix('test-sessions')->name('test-sessions.')->group(function () {
        Route::get('/', [TestSessionController::class, 'index'])->name('index')->middleware('permission:test.view');
        Route::post('/{id}/reset', [TestSessionController::class, 'reset'])->name('reset')->middleware('permission:test.edit');
        Route::delete('/{id}', [TestSessionController::class, 'destroy'])->name('destroy')->middleware('permission:test.delete');
    });

    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/',          [ContractController::class, 'index'])->name('index')->middleware('permission:contract.view');
        Route::get('/create',    [ContractController::class, 'create'])->name('create')->middleware('permission:contract.create');
        Route::post('/',         [ContractController::class, 'store'])->name('store')->middleware('permission:contract.create');
        Route::get('/{id}',      [ContractController::class, 'show'])->name('show')->middleware('permission:contract.view');
        Route::get('/{id}/edit', [ContractController::class, 'edit'])->name('edit')->middleware('permission:contract.edit');
        Route::put('/{id}',      [ContractController::class, 'update'])->name('update')->middleware('permission:contract.edit');
        Route::get('/{id}/pdf',  [ContractController::class, 'generatePdf'])->name('pdf')->middleware('permission:contract.view');
        Route::delete('/{id}',   [ContractController::class, 'destroy'])->name('destroy')->middleware('permission:contract.delete');
    });

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index')->middleware('permission:payment.view');
        Route::post('/', [PaymentController::class, 'store'])->name('store')->middleware('permission:payment.create');
        Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy')->middleware('permission:payment.delete');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',          [UserController::class, 'index'])->name('index')->middleware('permission:user.view');
        Route::get('/create',    [UserController::class, 'create'])->name('create')->middleware('permission:user.create');
        Route::post('/',         [UserController::class, 'store'])->name('store')->middleware('permission:user.create');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit')->middleware('permission:user.edit');
        Route::put('/{id}',      [UserController::class, 'update'])->name('update')->middleware('permission:user.edit');
        Route::delete('/{id}',   [UserController::class, 'destroy'])->name('destroy')->middleware('permission:user.delete');
    });



    Route::prefix('faculties')->name('faculties.')->group(function () {
        Route::get('/',          [FacultyController::class, 'index'])->name('index')->middleware('permission:academic.view');
        Route::get('/create',    [FacultyController::class, 'create'])->name('create')->middleware('permission:academic.create');
        Route::post('/',         [FacultyController::class, 'store'])->name('store')->middleware('permission:academic.create');
        Route::get('/{id}/edit', [FacultyController::class, 'edit'])->name('edit')->middleware('permission:academic.edit');
        Route::put('/{id}',      [FacultyController::class, 'update'])->name('update')->middleware('permission:academic.edit');
        Route::delete('/{id}',   [FacultyController::class, 'destroy'])->name('destroy')->middleware('permission:academic.delete');
    });

    Route::prefix('directions')->name('directions.')->group(function () {
        Route::get('/',          [DirectionController::class, 'index'])->name('index')->middleware('permission:academic.view');
        Route::get('/create',    [DirectionController::class, 'create'])->name('create')->middleware('permission:academic.create');
        Route::post('/',         [DirectionController::class, 'store'])->name('store')->middleware('permission:academic.create');
        Route::get('/{id}/edit', [DirectionController::class, 'edit'])->name('edit')->middleware('permission:academic.edit');
        Route::put('/{id}',      [DirectionController::class, 'update'])->name('update')->middleware('permission:academic.edit');
        Route::delete('/{id}',   [DirectionController::class, 'destroy'])->name('destroy')->middleware('permission:academic.delete');
    });


    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/',          [DepartmentController::class, 'index'])->name('index')->middleware('permission:academic.view');
        Route::get('/create',    [DepartmentController::class, 'create'])->name('create')->middleware('permission:academic.create');
        Route::post('/',         [DepartmentController::class, 'store'])->name('store')->middleware('permission:academic.create');
        Route::get('/{id}/edit', [DepartmentController::class, 'edit'])->name('edit')->middleware('permission:academic.edit');
        Route::put('/{id}',      [DepartmentController::class, 'update'])->name('update')->middleware('permission:academic.edit');
        Route::delete('/{id}',   [DepartmentController::class, 'destroy'])->name('destroy')->middleware('permission:academic.delete');
    });


    Route::prefix('interviews')->name('interviews.')->group(function () {
        Route::get('/',      [InterviewController::class, 'index'])->name('index')->middleware('permission:admission.view');
        Route::post('/',     [InterviewController::class, 'store'])->name('store')->middleware('permission:admission.create');
    });

    // Talabalar — Akademik yillar
    Route::prefix('academic-years')->name('academic-years.')->group(function () {
        Route::get('/',          [AcademicYearController::class, 'index'])->name('index')->middleware('permission:academic.view');
        Route::get('/create',    [AcademicYearController::class, 'create'])->name('create')->middleware('permission:academic.create');
        Route::post('/',         [AcademicYearController::class, 'store'])->name('store')->middleware('permission:academic.create');
        Route::get('/{id}/edit', [AcademicYearController::class, 'edit'])->name('edit')->middleware('permission:academic.edit');
        Route::put('/{id}',      [AcademicYearController::class, 'update'])->name('update')->middleware('permission:academic.edit');
        Route::delete('/{id}',   [AcademicYearController::class, 'destroy'])->name('destroy')->middleware('permission:academic.delete');
    });

    // Talabalar — Talabalar ro'yxati + HEMIS Excel import
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/',           [StudentController::class, 'index'])->name('index')->middleware('permission:student.view');
        Route::get('/create',     [StudentController::class, 'create'])->name('create')->middleware('permission:student.create');
        Route::post('/',          [StudentController::class, 'store'])->name('store')->middleware('permission:student.create');
        Route::get('/template',   [StudentController::class, 'template'])->name('template')->middleware('permission:student.view');
        Route::post('/import',    [StudentController::class, 'import'])->name('import')->middleware('permission:student.create');
        Route::get('/{id}',       [StudentController::class, 'show'])->name('show')->middleware('permission:student.view');
        Route::get('/{id}/edit',  [StudentController::class, 'edit'])->name('edit')->middleware('permission:student.edit');
        Route::put('/{id}',       [StudentController::class, 'update'])->name('update')->middleware('permission:student.edit');
        Route::delete('/{id}',    [StudentController::class, 'destroy'])->name('destroy')->middleware('permission:student.delete');
    });

    // Guruhlar — o'quv guruhlari va ularning a'zolari
    Route::prefix('student-groups')->name('student-groups.')->group(function () {
        Route::get('/',           [StudentGroupController::class, 'index'])->name('index')->middleware('permission:group.view');
        Route::get('/create',     [StudentGroupController::class, 'create'])->name('create')->middleware('permission:group.create');
        Route::post('/',          [StudentGroupController::class, 'store'])->name('store')->middleware('permission:group.create');
        Route::get('/{id}',       [StudentGroupController::class, 'show'])->name('show')->middleware('permission:group.view');
        Route::get('/{id}/edit',  [StudentGroupController::class, 'edit'])->name('edit')->middleware('permission:group.edit');
        Route::put('/{id}',       [StudentGroupController::class, 'update'])->name('update')->middleware('permission:group.edit');
        Route::delete('/{id}',    [StudentGroupController::class, 'destroy'])->name('destroy')->middleware('permission:group.delete');
        Route::post('/{id}/students',                 [StudentGroupController::class, 'addStudent'])->name('students.add')->middleware('permission:group.edit');
        Route::delete('/{id}/students/{studentId}',   [StudentGroupController::class, 'removeStudent'])->name('students.remove')->middleware('permission:group.edit');
    });

    // Kurs kategoriyalari
    Route::prefix('course-categories')->name('course-categories.')->group(function () {
        Route::get('/',          [CourseCategoryController::class, 'index'])->name('index')->middleware('permission:lms.view');
        Route::get('/create',    [CourseCategoryController::class, 'create'])->name('create')->middleware('permission:lms.create');
        Route::post('/',         [CourseCategoryController::class, 'store'])->name('store')->middleware('permission:lms.create');
        Route::get('/{id}/edit', [CourseCategoryController::class, 'edit'])->name('edit')->middleware('permission:lms.edit');
        Route::put('/{id}',      [CourseCategoryController::class, 'update'])->name('update')->middleware('permission:lms.edit');
        Route::delete('/{id}',   [CourseCategoryController::class, 'destroy'])->name('destroy')->middleware('permission:lms.delete');
    });

    // Kurslar — kurs + modul + darslar (LMS asosiy qurilmasi)
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/',           [CourseController::class, 'index'])->name('index')->middleware('permission:lms.view');
        Route::get('/create',     [CourseController::class, 'create'])->name('create')->middleware('permission:lms.create');
        Route::post('/',          [CourseController::class, 'store'])->name('store')->middleware('permission:lms.create');
        Route::get('/{id}',       [CourseController::class, 'show'])->name('show')->middleware('permission:lms.view');
        Route::get('/{id}/edit',  [CourseController::class, 'edit'])->name('edit')->middleware('permission:lms.edit');
        Route::put('/{id}',       [CourseController::class, 'update'])->name('update')->middleware('permission:lms.edit');
        Route::delete('/{id}',    [CourseController::class, 'destroy'])->name('destroy')->middleware('permission:lms.delete');

        // Modullar (kurs ichida)
        Route::post('/{id}/modules',               [CourseModuleController::class, 'store'])->name('modules.store')->middleware('permission:lms.create');
        Route::put('/{id}/modules/{moduleId}',     [CourseModuleController::class, 'update'])->name('modules.update')->middleware('permission:lms.edit');
        Route::delete('/{id}/modules/{moduleId}',  [CourseModuleController::class, 'destroy'])->name('modules.destroy')->middleware('permission:lms.delete');

        // Darslar (modul ichida)
        Route::get('/{id}/modules/{moduleId}/lessons/create', [LessonController::class, 'create'])->name('lessons.create')->middleware('permission:lms.create');
        Route::post('/{id}/modules/{moduleId}/lessons',       [LessonController::class, 'store'])->name('lessons.store')->middleware('permission:lms.create');
        Route::get('/{id}/lessons/{lessonId}/edit',           [LessonController::class, 'edit'])->name('lessons.edit')->middleware('permission:lms.edit');
        Route::put('/{id}/lessons/{lessonId}',                [LessonController::class, 'update'])->name('lessons.update')->middleware('permission:lms.edit');
        Route::delete('/{id}/lessons/{lessonId}',             [LessonController::class, 'destroy'])->name('lessons.destroy')->middleware('permission:lms.delete');
        Route::delete('/{id}/lessons/{lessonId}/attachments/{attachmentId}', [LessonController::class, 'destroyAttachment'])->name('lessons.attachments.destroy')->middleware('permission:lms.edit');

        // Kursga yozilganlar (Enrollment) — talaba/guruhni kursga biriktirish
        Route::get('/{id}/enrollments',       [EnrollmentController::class, 'index'])->name('enrollments.index')->middleware('permission:lms.view');
        Route::post('/{id}/enrollments',      [EnrollmentController::class, 'store'])->name('enrollments.store')->middleware('permission:lms.edit');
        Route::patch('/{id}/enrollments/{enrollmentId}/payment-status', [EnrollmentController::class, 'updatePaymentStatus'])->name('enrollments.payment-status')->middleware('permission:lms.edit');
        Route::delete('/{id}/enrollments/{enrollmentId}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy')->middleware('permission:lms.delete');
    });

    // "Kurslarim" — joriy foydalanuvchining o'ziga yozilgan (Enrollment)
    // kurslarini ko'rishi va darslarni o'tishi. Ma'lumotlar har doim
    // auth()->id() bilan cheklanadi (StudentCourseController ichida), shu
    // sabab bu yerga hech qanday 'permission:' qo'yilmagan — faqat login
    // qilingan bo'lish (tashqi guruhdagi 'auth') yetarli.
    //
    // MUHIM: avval bu yerda ham 'permission:lms.view' bor edi — bu esa
    // yuqoridagi /admin/courses/{id} (kurs QURUVCHI, ya'ni admin/o'qituvchi
    // sahifasi, xuddi shu 'lms.view' bilan himoyalangan) sahifasini ham
    // talabaga ochib qo'ygan edi, chunki 'student' roliga "Kurslarim"
    // ishlashi uchun 'lms.view' berilgan edi. Endi ikkalasi mustaqil.
    Route::prefix('my-courses')->name('my-courses.')->group(function () {
        Route::get('/',                              [StudentCourseController::class, 'index'])->name('index');
        Route::get('/{id}',                          [StudentCourseController::class, 'show'])->name('show');
        Route::get('/{id}/lessons/{lessonId}',       [StudentCourseController::class, 'lesson'])->name('lesson');
        Route::post('/{id}/lessons/{lessonId}/complete', [StudentCourseController::class, 'markComplete'])->name('lessons.complete');
    });
});
