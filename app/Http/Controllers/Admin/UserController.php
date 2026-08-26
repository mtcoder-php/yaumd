<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::with('roles')
                ->latest()
                ->paginate(20),
            'roles' => Role::all(),
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

        $user->assignRole($request->role);

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

        $user->syncRoles([$request->role]);

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
