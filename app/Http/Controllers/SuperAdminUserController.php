<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SuperAdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::orderByDesc('created_at')->paginate(15);

        return view('superadmin.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = ['user', 'admin', 'expert', 'mao'];

        return view('superadmin.users.create', compact('roles'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()->route('superadmin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $roles = ['user', 'admin', 'expert', 'mao'];

        return view('superadmin.users.edit', compact('user', 'roles'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        return redirect()->route('superadmin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('superadmin.users.index')->with('success', 'User deleted successfully.');
    }
}
