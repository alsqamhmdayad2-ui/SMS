<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $roleFilter = $request->get('role');

        $users = User::with('roles')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->when($roleFilter, fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', $roleFilter)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $roles = Role::all();

        return view('panels.admin.users.index', compact('users', 'roles', 'search', 'roleFilter'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        return view('panels.admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRole = $user->roles->first()?->name;
        return view('panels.admin.users.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role'  => ['required', 'exists:roles,name'],
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'تم تحديث بيانات المستخدم بنجاح.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'تم حذف المستخدم بنجاح.');
    }
}
