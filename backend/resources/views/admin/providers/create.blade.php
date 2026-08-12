@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100">
    <!-- Navbar -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-30">
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Nuevo Proveedor</h1>
                <p class="text-sm text-slate-400">Crear un nuevo proveedor en el sistema</p>
            </div>
            <a href="{{ route('admin.providers.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg font-medium transition">
                ← Atrás
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="p-6 max-w-2xl mx-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-8">
            <form method="POST" action="{{ route('admin.providers.store') }}" class="space-y-6">
                @csrf

                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Nombre del Proveedor *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Ej: ProPets Inc." 
                        class="w-full px-4 py-2 bg-slate-800 border @error('name') border-red-500 @else border-slate-700 @enderror rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @error('name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="contacto@proveedor.com" 
                        class="w-full px-4 py-2 bg-slate-800 border @error('email') border-red-500 @else border-slate-700 @enderror rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @error('email')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Teléfono</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+34 XXX XX XX XX" 
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('phone')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dirección -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Dirección</label>
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="Calle, ciudad, país" 
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('address')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 rounded-lg font-semibold transition">
                        ✓ Crear Proveedor
                    </button>
                    <a href="{{ route('admin.providers.index') }}" class="flex-1 px-6 py-3 bg-slate-700 hover:bg-slate-600 rounded-lg font-semibold text-center transition">
                        ✗ Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
