<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $professors = User::role('professor')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'active', 'created_at']);

        return Inertia::render('admin/users/Index', [
            'professors' => $professors,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/users/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'active'   => true,
        ]);
        $user->assignRole('professor');

        return redirect()->route('admin.users.index')
            ->with('success', 'Professor criado com sucesso.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('admin/users/Edit', [
            'professor' => $user->only(['id', 'name', 'email', 'active']),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Professor atualizado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Deactivate, never delete — preserves referential integrity for future phases
        $user->update(['active' => false]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Conta desativada.');
    }

    public function restore(User $user): RedirectResponse
    {
        $user->update(['active' => true]);
        return redirect()->route('admin.users.index')
            ->with('success', 'Conta reativada com sucesso.');
    }
}
