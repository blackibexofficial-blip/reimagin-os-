<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->orderBy('role')->orderBy('name')->paginate(20),
            'roles' => Role::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(['role' => Role::Staff, 'is_active' => true]),
            'roles' => Role::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10'],
            'role' => ['required', Rule::enum(Role::class)],
            'job_title' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        User::create($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.users.index')->with('status', 'Team member invited.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user, 'roles' => Role::cases()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:10'],
            'role' => ['required', Rule::enum(Role::class)],
            'job_title' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        /* Role escalation guard: only an owner may mint another owner, and
         * nobody may demote the last remaining owner. */
        if ($data['role'] === Role::Owner->value && ! $request->user()->isOwner()) {
            return back()->with('error', 'Only an owner can grant owner access.');
        }

        if ($user->isOwner() && $data['role'] !== Role::Owner->value
            && User::query()->where('role', Role::Owner->value)->count() <= 1) {
            return back()->with('error', 'The studio must keep at least one owner.');
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'Team member updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($user->isOwner() && User::query()->where('role', Role::Owner->value)->count() <= 1) {
            return back()->with('error', 'The studio must keep at least one owner.');
        }

        /* Deactivated rather than deleted: orders, quotations and the activity
         * log keep pointing at a real person. */
        $user->update(['is_active' => false]);

        return back()->with('status', $user->name.' can no longer sign in.');
    }
}
