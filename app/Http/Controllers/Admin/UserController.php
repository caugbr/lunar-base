<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::whereIn('role', ['admin', 'editor'])
            ->orderBy('created_at', 'desc')
            ->paginate(setting('reading.pagination_max_items'));
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = config('rolesPermissions.roles');
        return view('admin.users.create')->with('roles', $roles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,editor',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        log_admin("Usuário criado: {$validated['name']}", "users");

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    public function edit($id)
    {
        $user = User::whereIn('role', ['admin', 'editor'])->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::whereIn('role', ['admin', 'editor'])->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,editor',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        log_admin("Usuário atualizado: {$validated['name']}", "users");

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $user = User::whereIn('role', ['admin', 'editor'])->findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        $user->delete();

        log_admin("Usuário removido: {$validated['name']}", "users");

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário removido com sucesso!');
    }
}
