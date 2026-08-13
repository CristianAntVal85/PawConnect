<?php

namespace App\Http\Controllers\Web\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        // Filtro de estado
        if ($request->filled('status') && $request->input('status') !== 'all') {
            if ($request->input('status') === 'active') {
                $query->whereNull('blocked_at');
            } elseif ($request->input('status') === 'blocked') {
                $query->whereNotNull('blocked_at');
            }
        }

        // Filtro de rol
        if ($request->filled('role') && $request->input('role') !== 'all') {
            $query->where('role', $request->input('role'));
        }

        $users = $query->with('pets', 'orders')
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function export()
    {
        return Excel::download(new UsersExport(), 'usuarios_' . now()->format('Y-m-d_H-i-s') . '.csv');
    }

    public function show(User $user)
    {
        $user->load('pets', 'orders', 'placeReviews', 'createdPlaces');

        return view('admin.users.show', compact('user'));
    }

    public function updateRole(Request $request, User $user)
    {
        // No permitir cambiar el rol del primer admin
        $adminCount = User::where('role', 'admin')->count();
        if ($user->isAdmin() && $adminCount === 1) {
            return back()->with('error', 'No puedes cambiar el rol del único administrador.');
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_USER, User::ROLE_ADMIN])],
        ]);

        $user->update($validated);

        return back()->with('success', "Rol de {$user->name} actualizado a {$user->role}.");
    }

    public function block(User $user)
    {
        // No permitir bloquear al único admin
        $adminCount = User::where('role', 'admin')->count();
        if ($user->isAdmin() && $adminCount === 1) {
            return back()->with('error', 'No puedes bloquear al único administrador.');
        }

        $user->block();

        return back()->with('success', "{$user->name} ha sido bloqueado.");
    }

    public function unblock(User $user)
    {
        $user->unblock();

        return back()->with('success', "{$user->name} ha sido desbloqueado.");
    }
}
