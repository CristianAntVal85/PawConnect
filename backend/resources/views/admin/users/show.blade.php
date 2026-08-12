@extends('layouts.admin')

@section('title', 'Usuario: ' . $user->name . ' | Admin')
@section('page-title', $user->name)

@section('content')
<div class="space-y-6">
    <!-- Mensajes -->
    @if(session('success'))
        <div class="rounded-lg bg-emerald-500/20 border border-emerald-500/30 p-4">
            <p class="text-emerald-300">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Información General -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Perfil -->
        <div class="lg:col-span-1 rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-6">
            <div class="text-center">
                @if($user->photo)
                    <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full object-cover mx-auto mb-4">
                @else
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl font-semibold text-white">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                @endif

                <h2 class="text-2xl font-bold text-slate-100 mb-1">{{ $user->name }}</h2>
                <p class="text-slate-400 mb-4">{{ $user->email }}</p>

                <div class="space-y-2 mb-6">
                    <div class="flex items-center justify-center gap-2">
                        <span class="px-3 py-1 rounded text-xs font-semibold
                            @if($user->isAdmin())
                                bg-purple-500/20 text-purple-300 border border-purple-500/30
                            @else
                                bg-slate-700 text-slate-300
                            @endif
                        ">
                            {{ $user->isAdmin() ? 'Admin' : 'Usuario' }}
                        </span>
                    </div>

                    <div>
                        @if($user->isBlocked())
                            <span class="px-3 py-1 rounded text-xs font-semibold bg-red-500/20 text-red-300 border border-red-500/30">
                                🔴 Bloqueado
                            </span>
                        @else
                            <span class="px-3 py-1 rounded text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                🟢 Activo
                            </span>
                        @endif
                    </div>
                </div>

                <div class="space-y-3 border-t border-slate-700 pt-4">
                    <div class="text-left">
                        <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Registrado</p>
                        <p class="text-sm text-slate-200">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div class="text-left">
                        <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Email verificado</p>
                        <p class="text-sm text-slate-200">
                            {{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : 'No verificado' }}
                        </p>
                    </div>

                    @if($user->isBlocked())
                        <div class="text-left">
                            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Bloqueado desde</p>
                            <p class="text-sm text-slate-200">{{ $user->blocked_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                </div>

                <div class="flex gap-2 mt-4">
                    @if(!$user->isBlocked())
                        <form method="POST" action="{{ route('admin.users.block', $user->id) }}" class="flex-1">
                            @csrf
                            <button 
                                type="submit"
                                @if(User::where('role', 'admin')->count() === 1 && $user->isAdmin()) disabled @endif
                                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Bloquear
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.users.unblock', $user->id) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                                Desbloquear
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Datos y Acciones -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Información de Contacto -->
            <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-6">
                <h3 class="text-lg font-semibold text-slate-100 mb-4">Información de Contacto</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Email</p>
                        <p class="text-sm text-slate-200">{{ $user->email }}</p>
                    </div>

                    @if($user->address)
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Dirección</p>
                            <p class="text-sm text-slate-200">{{ $user->address }}</p>
                        </div>
                    @endif

                    @if($user->billing_address)
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Dirección de Facturación</p>
                            <p class="text-sm text-slate-200">{{ $user->billing_address }}</p>
                        </div>
                    @endif

                    @if($user->lat && $user->lng)
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Ubicación</p>
                            <p class="text-sm text-slate-200">Lat: {{ $user->lat }}, Lng: {{ $user->lng }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cambiar Rol -->
            <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-6">
                <h3 class="text-lg font-semibold text-slate-100 mb-4">Cambiar Rol</h3>
                <form method="POST" action="{{ route('admin.users.updateRole', $user->id) }}">
                    @csrf
                    <div class="flex gap-4 items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Rol Actual</label>
                            <select name="role" class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-sky-500 focus:outline-none">
                                <option value="user" {{ !$user->isAdmin() ? 'selected' : '' }}>Usuario</option>
                                <option value="admin" {{ $user->isAdmin() ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        <button 
                            type="submit"
                            @if(User::where('role', 'admin')->count() === 1 && $user->isAdmin()) disabled @endif
                            class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Mascotas -->
    <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-6">
        <h3 class="text-lg font-semibold text-slate-100 mb-4">Mascotas ({{ $user->pets->count() }})</h3>

        @if($user->pets->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($user->pets as $pet)
                    <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h4 class="font-semibold text-slate-100">{{ $pet->name }}</h4>
                                <p class="text-xs text-slate-400">{{ ucfirst($pet->species) }}</p>
                            </div>
                            <span class="px-2 py-1 bg-sky-500/20 text-sky-300 text-xs rounded font-semibold">
                                {{ $pet->age ?? 'N/A' }} años
                            </span>
                        </div>
                        <p class="text-sm text-slate-300 mb-2">{{ $pet->breed ?? 'Raza desconocida' }}</p>
                        <div class="flex gap-2 text-xs">
                            @if($pet->weight)
                                <span class="bg-slate-700 text-slate-300 px-2 py-1 rounded">{{ $pet->weight }}kg</span>
                            @endif
                            @if($pet->size)
                                <span class="bg-slate-700 text-slate-300 px-2 py-1 rounded">{{ ucfirst($pet->size) }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-slate-400">No tiene mascotas registradas</p>
        @endif
    </div>

    <!-- Pedidos -->
    <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-6">
        <h3 class="text-lg font-semibold text-slate-100 mb-4">Pedidos Recientes ({{ $user->orders->count() }})</h3>

        @if($user->orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-300">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-300">Producto</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-300">Monto</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-300">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-300">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @foreach($user->orders->take(5) as $order)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-4 py-2 text-slate-200">#{{ $order->id }}</td>
                                <td class="px-4 py-2 text-slate-200">{{ $order->product->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2 font-semibold text-slate-100">${{ number_format($order->total_price, 2) }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @if($order->status === 'completed')
                                            bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                                        @elseif($order->status === 'pending')
                                            bg-amber-500/20 text-amber-300 border border-amber-500/30
                                        @else
                                            bg-red-500/20 text-red-300 border border-red-500/30
                                        @endif
                                    ">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-slate-400">{{ $order->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($user->orders->count() > 5)
                <p class="text-xs text-slate-400 mt-3">
                    Mostrando 5 de {{ $user->orders->count() }} pedidos
                </p>
            @endif
        @else
            <p class="text-slate-400">No tiene pedidos</p>
        @endif
    </div>

    <!-- Volver -->
    <div>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sky-400 hover:text-sky-300 font-medium transition">
            ← Volver a usuarios
        </a>
    </div>
</div>
