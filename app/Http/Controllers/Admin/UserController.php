<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private readonly UserImportService $importService,
    ) {
    }

    public function index(Request $request): Response
    {
        // Ro'yxat 20 tadan sahifalangani uchun 77+ foydalanuvchi orasidan
        // (masalan o'zini — Super Admin'ni) qidirish uchun sahifama-sahifa
        // yurishga to'g'ri kelmasligi kerak edi — shu sababli ism/email
        // bo'yicha qidiruv va rol bo'yicha filtr qo'shildi.
        $query = User::with('roles')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }

        // Sahifadagi qatorlar soni — referensdagi kabi tanlanadigan
        // (20/30/50/100/150/200), standart holatda 20 ta. Ruxsat etilgan
        // ro'yxatdan tashqari (masalan qo'lda URL'ga yozilgan) qiymat
        // kelsa — standart 20 ga qaytariladi.
        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [20, 30, 50, 100, 150, 200], true)) {
            $perPage = 20;
        }

        return Inertia::render('Admin/Users/Index', [
            'users'   => $query->paginate($perPage)->withQueryString(),
            'roles'   => Role::all(),
            'filters' => $request->only(['search', 'role', 'per_page']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
        ]);

        // syncRoles — aynan yuborilgan rollar to'plami bilan biriktiradi
        // (bitta foydalanuvchida bir nechta rol bo'lishi mumkin).
        $user->syncRoles($request->roles);

        return redirect()->route('admin.users.index')
            ->with('success', 'Foydalanuvchi yaratildi!');
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user'  => User::with('roles')->findOrFail($id),
            'roles' => Role::all(),
        ]);
    }

    public function update(UpdateUserRequest $request, int $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'full_name' => $request->full_name,
            'email'     => $request->email,
            ...$request->filled('password')
                ? ['password' => Hash::make($request->password)]
                : [],
        ]);

        $user->syncRoles($request->roles);

        return redirect()->route('admin.users.index')
            ->with('success', 'Foydalanuvchi yangilandi!');
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->hasRole('super-admin')) {
            return back()->withErrors(['error' => 'Super adminni o\'chirib bo\'lmaydi!']);
        }

        $user->delete();
        return back()->with('success', 'Foydalanuvchi o\'chirildi!');
    }

    public function template()
    {
        return response($this->importService->template(), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="xodimlar_namuna.xlsx"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'file.required' => 'Fayl yuklang',
            'file.mimes' => 'Faqat .xlsx, .xls yoki .csv fayl qabul qilinadi',
        ]);

        $result = $this->importService->import($request->file('file'));

        if ($result['created'] === 0 && $result['updated'] === 0) {
            return back()->withErrors([
                'file' => "Birorta ham xodim import qilinmadi. Namuna shablonni yuklab ko'rib chiqing.",
            ])->with('importErrors', $result['errors']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Import yakunlandi: {$result['created']} ta yangi, {$result['updated']} ta yangilandi, {$result['skipped']} ta o'tkazib yuborildi.")
            ->with('importErrors', $result['errors']);
    }
}
