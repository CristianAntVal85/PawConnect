@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header con nombre de mascota y estado general -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Historial Sanitario de {{ $pet->name }}
                        </h1>
                        <p class="text-gray-600 mt-2">
                            {{ ucfirst($pet->species) }} · {{ $pet->breed }}
                        </p>
                    </div>
                    <a href="{{ route('pets.show', $pet) }}"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                        Volver a mascota
                    </a>
                </div>

                <!-- Estado General (Semáforo) -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">Estado de Salud General</h3>
                            <p class="text-gray-600 mt-1">
                                @switch($generalStatus)
                                    @case('green')
                                        ✓ Todo al día
                                    @break

                                    @case('yellow')
                                        ⏰ Hay registros próximos a vencer
                                    @break

                                    @case('red')
                                        ⚠️ Hay registros vencidos
                                    @break
                                @endswitch
                            </p>
                        </div>
                        <div class="text-4xl">
                            @if ($generalStatus === 'green')
                                🟢
                            @elseif ($generalStatus === 'yellow')
                                🟡
                            @else
                                🔴
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline de registros -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @if ($records->isEmpty())
                    <div class="p-8 text-center">
                        <p class="text-gray-500 text-lg">
                            No hay registros sanitarios. Crea uno para comenzar a hacer seguimiento.
                        </p>
                        <a href="{{ route('pets.show', $pet) }}"
                            class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Agregar Registro
                        </a>
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach ($records as $record)
                            @php
                                $status = $record['health_status'];
                                $statusColor = match ($status['status']) {
                                    'green' => 'bg-green-50 border-l-4 border-green-500',
                                    'yellow' => 'bg-yellow-50 border-l-4 border-yellow-500',
                                    'red' => 'bg-red-50 border-l-4 border-red-500',
                                };
                                $iconEmoji = match ($status['status']) {
                                    'green' => '✓',
                                    'yellow' => '⏰',
                                    'red' => '⚠️',
                                };
                                $typeLabel = match ($record['type']) {
                                    'vaccination' => 'Vacunación',
                                    'deworming' => 'Desparasitación',
                                    'checkup' => 'Chequeo',
                                    'surgery' => 'Cirugía',
                                    'medication' => 'Medicamento',
                                    default => ucfirst($record['type']),
                                };
                            @endphp

                            <div class="{{ $statusColor }} p-6 hover:bg-opacity-75 transition">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <!-- Info del registro -->
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-2xl">{{ $iconEmoji }}</span>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $typeLabel }}</h3>
                                        </div>

                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-600">Última revisión</p>
                                                <p class="font-medium text-gray-900">
                                                    {{ $record['last_date'] ? \Carbon\Carbon::parse($record['last_date'])->format('d/m/Y') : 'Sin registrar' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600">Próxima revisión</p>
                                                <p class="font-medium text-gray-900">
                                                    {{ $record['next_due_date'] ? \Carbon\Carbon::parse($record['next_due_date'])->format('d/m/Y') : 'Sin registrar' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600">Periodicidad</p>
                                                <p class="font-medium text-gray-900">
                                                    {{ $record['periodicity_days'] ?? '-' }} días
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600">Estado</p>
                                                <p class="font-medium text-gray-900">{{ $status['message'] }}</p>
                                            </div>
                                        </div>

                                        @if ($record['notes'])
                                            <div class="mt-3 p-3 bg-white bg-opacity-50 rounded text-gray-700">
                                                <p class="text-xs text-gray-600 uppercase tracking-wide">Notas</p>
                                                <p>{{ $record['notes'] }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Acciones -->
                                    <div class="flex flex-col gap-2">
                                        <button type="button" class="renew-btn px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                                            data-pet-id="{{ $pet->id }}" data-health-id="{{ $record['id'] }}">
                                            🔄 Renovar
                                        </button>
                                        <a href="{{ route('pets.show', $pet) }}"
                                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-medium text-center">
                                            ✏️ Editar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Leyenda de colores -->
            <div class="mt-8 grid grid-cols-3 gap-4">
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <p class="text-green-900 font-semibold">🟢 Al día</p>
                    <p class="text-green-700 text-sm">Faltan más de 7 días</p>
                </div>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <p class="text-yellow-900 font-semibold">🟡 Próximo a vencer</p>
                    <p class="text-yellow-700 text-sm">Faltan 0-7 días</p>
                </div>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <p class="text-red-900 font-semibold">🔴 Vencido</p>
                    <p class="text-red-700 text-sm">Revisión pendiente</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div id="renewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md mx-4">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Renovar registro sanitario</h2>
            <p class="text-gray-600 mb-6">¿Deseas renovar este registro? La fecha de última revisión se actualizará a hoy.</p>
            <div class="flex gap-4">
                <button type="button" id="confirmRenew"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    Renovar
                </button>
                <button type="button" id="cancelRenew"
                    class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-medium">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        let renewingPetId = null;
        let renewingHealthId = null;

        document.querySelectorAll('.renew-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                renewingPetId = this.dataset.petId;
                renewingHealthId = this.dataset.healthId;
                document.getElementById('renewModal').classList.remove('hidden');
            });
        });

        document.getElementById('cancelRenew').addEventListener('click', function() {
            document.getElementById('renewModal').classList.add('hidden');
        });

        document.getElementById('confirmRenew').addEventListener('click', async function() {
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Renovando...';

            try {
                const response = await fetch(`/api/v1/pets/${renewingPetId}/health/${renewingHealthId}/renew`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                if (response.ok) {
                    // Mostrar mensaje de éxito
                    alert('✓ Registro renovado correctamente');
                    // Recargar la página
                    location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Error al renovar el registro');
                    btn.disabled = false;
                    btn.textContent = 'Renovar';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión');
                btn.disabled = false;
                btn.textContent = 'Renovar';
            }

            document.getElementById('renewModal').classList.add('hidden');
        });
    </script>
@endsection
