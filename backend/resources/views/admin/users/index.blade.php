@extends('layouts.admin')

@section('title', 'Gestión de Usuarios | Admin')
@section('page-title', 'Gestión de Usuarios')

@section('content')
<div class="space-y-6">
    <!-- Filtros y Búsqueda -->
    <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-4">
        <form method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
            <div class="flex-1 min-w-0">
                <label class="block text-sm font-medium text-slate-300 mb-2">Buscar usuario</label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Nombre, email..."
                    class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                >
            </div>

            <div class="w-full lg:w-48">
                <label class="block text-sm font-medium text-slate-300 mb-2">Estado</label>
                <select name="status" class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-sky-500 focus:outline-none">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Todos</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqueados</option>
                </select>
            </div>

            <div class="w-full lg:w-48">
                <label class="block text-sm font-medium text-slate-300 mb-2">Rol</label>
                <select name="role" class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-sky-500 focus:outline-none">
                    <option value="all" {{ request('role') === 'all' ? 'selected' : '' }}>Todos</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Usuario</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <button
                type="submit"
                class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded-lg transition"
            >
                Filtrar
            </button>

            @if(request()->filled('search') || request()->filled('status') || request()->filled('role'))
                <a
                    href="{{ route('admin.users.index') }}"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium rounded-lg transition border border-slate-700"
                >
                    Limpiar
                </a>
            @endif

            <a
                href="{{ route('admin.users.export') }}"
                class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Descargar CSV
            </a>
        </form>
    </div>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="rounded-lg bg-emerald-500/20 border border-emerald-500/30 p-4">
            <p class="text-emerald-300">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg bg-red-500/20 border border-red-500/30 p-4">
            <p class="text-red-300">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Tabla de Usuarios -->
    <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700">
            <h3 class="text-base font-semibold text-slate-100">
                Total: {{ $users->total() }} usuario{{ $users->total() !== 1 ? 's' : '' }}
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Mascotas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Registro</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($user->photo)
                                        <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center">
                                            <span class="text-xs font-semibold text-white">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <span class="font-medium text-slate-100">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-400">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded text-xs font-semibold
                                    @if($user->isAdmin())
                                        bg-purple-500/20 text-purple-300 border border-purple-500/30
                                    @else
                                        bg-slate-700 text-slate-300
                                    @endif
                                ">
                                    {{ $user->isAdmin() ? 'Admin' : 'Usuario' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-300">{{ $user->pets_count ?? $user->pets()->count() }}</td>
                            <td class="px-6 py-4 text-slate-400">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                @if($user->isBlocked())
                                    <span class="px-2.5 py-1 rounded text-xs font-semibold bg-red-500/20 text-red-300 border border-red-500/30">
                                        🔴 Bloqueado
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        🟢 Activo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a
                                        href="{{ route('admin.users.show', $user->id) }}"
                                        class="px-3 py-1 bg-sky-600 hover:bg-sky-700 text-white text-xs font-medium rounded transition"
                                    >
                                        Ver
                                    </a>

                                    <div class="relative inline-block group">
                                        <button class="px-3 py-1 bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs font-medium rounded transition">
                                            ⋮
                                        </button>
                                        <div class="absolute right-0 mt-1 w-40 bg-slate-800 border border-slate-700 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition z-10">
                                            <form method="POST" action="{{ route('admin.users.updateRole', $user->id) }}" class="border-b border-slate-700 last:border-0">
                                                @csrf
                                                @if(!$user->isAdmin())
                                                    <input type="hidden" name="role" value="admin">
                                                    <button type="submit" class="w-full text-left px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-slate-100 transition text-xs">
                                                        Hacer Admin
                                                    </button>
                                                @else
                                                    <input type="hidden" name="role" value="user">
                                                    <button
                                                        type="submit"
                                                        @if(\App\Models\User::where('role', 'admin')->count() === 1) disabled @endif
                                                        class="w-full text-left px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-slate-100 transition text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                                                    >
                                                        Hacer Usuario
                                                    </button>
                                                @endif
                                            </form>

                                            @if(!$user->isBlocked())
                                                <form method="POST" action="{{ route('admin.users.block', $user->id) }}" class="border-b border-slate-700 last:border-0">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        @if(\App\Models\User::where('role', 'admin')->count() === 1 && $user->isAdmin()) disabled @endif
                                                        class="w-full text-left px-4 py-2 text-red-400 hover:bg-slate-700 transition text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                                                    >
                                                        Bloquear
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.users.unblock', $user->id) }}" class="border-b border-slate-700 last:border-0">
                                                    @csrf
                                                    <button type="submit" class="w-full text-left px-4 py-2 text-emerald-400 hover:bg-slate-700 transition text-xs">
                                                        Desbloquear
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                No hay usuarios disponibles
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    @if($users->hasPages())
        <div class="flex justify-center">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
