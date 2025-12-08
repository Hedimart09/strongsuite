<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->with('roles')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        $staff = $query->paginate(20);

        return Inertia::render('Staff/Index', [
            'staff' => $staff,
            'filters' => $request->only(['search', 'role']),
            'roles' => Role::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Staff/Create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member created successfully');
    }

    public function show(User $staff)
    {
        $staff->load('roles', 'permissions');

        return Inertia::render('Staff/Show', [
            'staff' => $staff,
        ]);
    }

    public function edit(User $staff)
    {
        $staff->load('roles');

        return Inertia::render('Staff/Edit', [
            'staff' => $staff,
            'roles' => Role::all(),
        ]);
    }

    public function update(Request $request, User $staff)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$staff->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $staff->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $staff->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        $staff->syncRoles([$validated['role']]);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member updated successfully');
    }

    public function destroy(User $staff)
    {
        $staff->delete();

        return redirect()->route('staff.index')
            ->with('success', 'Staff member deleted successfully');
    }
}
