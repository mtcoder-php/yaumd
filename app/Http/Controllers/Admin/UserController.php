<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
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

        return Inertia::render('Admin/Users/Index', [
            'users'   => $query->paginate(20)->withQueryString(),
            'roles'   => Role::all(),
            'filters' => $request->only(['search', 'role']),
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
}
