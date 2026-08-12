@extends('layouts.admin')

@section('title', 'Dashboard | Admin')
@section('page-title', 'Dashboard de Estadísticas')

@section('content')
<div class="space-y-6">
    <!-- Key Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Users -->
        <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-5 backdrop-blur">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400">Total Usuarios</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ $totalUsers }}</p>
                </div>
                <div class="bg-blue-500/20 rounded-full p-2 border border-blue-500/30">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m4 4H9m6-11l4 4m-4-4l-4 4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Pets -->
        <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-5 backdrop-blur">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400">Total Mascotas</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ $totalPets }}</p>
                </div>
                <div class="bg-amber-500/20 rounded-full p-2 border border-amber-500/30">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 4l-4-1L5 7l-1 5h14l-1-5-5-4z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Matches -->
        <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-5 backdrop-blur">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400">Matches</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ $totalMatches }}</p>
                </div>
                <div class="bg-red-500/20 rounded-full p-2 border border-red-500/30">
                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-5 backdrop-blur">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400">Pedidos</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ $totalOrders }}</p>
                </div>
                <div class="bg-green-500/20 rounded-full p-2 border border-green-500/30">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-5 backdrop-blur">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400">Ingresos</p>
                    <p class="text-3xl font-bold text-white mt-2">${{ number_format($totalRevenue, 0) }}</p>
                </div>
                <div class="bg-purple-500/20 rounded-full p-2 border border-purple-500/30">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Users & Orders by Month -->
        <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-6 backdrop-blur">
            <h3 class="text-base font-semibold text-slate-100 mb-4">Usuarios y Pedidos (Últimos 6 meses)</h3>
            <div style="position: relative; height: 350px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Revenue by Month -->
        <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-6 backdrop-blur">
            <h3 class="text-base font-semibold text-slate-100 mb-4">Ingresos Mensuales</h3>
            <div style="position: relative; height: 350px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Orders by Status -->
        <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-6 backdrop-blur">
            <h3 class="text-base font-semibold text-slate-100 mb-4">Pedidos por Estado</h3>
            <div style="position: relative; height: 280px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-6 backdrop-blur">
            <h3 class="text-base font-semibold text-slate-100 mb-4">Resumen de Actividad</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-slate-700">
                    <span class="text-slate-300 text-sm">Promedio de mascotas por usuario</span>
                    <span class="font-semibold text-sky-400">{{ $totalUsers > 0 ? number_format($totalPets / $totalUsers, 2) : 0 }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-700">
                    <span class="text-slate-300 text-sm">Tasa de conversión (Matches/Mascotas)</span>
                    <span class="font-semibold text-emerald-400">{{ $totalPets > 0 ? number_format(($totalMatches / $totalPets) * 100, 1) : 0 }}%</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-700">
                    <span class="text-slate-300 text-sm">Ticket promedio</span>
                    <span class="font-semibold text-orange-400">${{ $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 2) : 0 }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-slate-300 text-sm">Conversión de usuarios (con compras)</span>
                    <span class="font-semibold text-violet-400">{{ $totalUsers > 0 ? number_format((Order::distinct('user_id')->count() / $totalUsers) * 100, 1) : 0 }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700">
            <h3 class="text-base font-semibold text-slate-100">Pedidos Recientes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Monto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-6 py-4 text-slate-200">#{{ $order->id }}</td>
                            <td class="px-6 py-4 text-slate-200">{{ $order->user->name }}</td>
                            <td class="px-6 py-4 text-slate-200">{{ $order->product->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-100">${{ number_format($order->total_price, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded text-xs font-semibold
                                    @if($order->status === 'completed')
                                        bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                                    @elseif($order->status === 'pending')
                                        bg-amber-500/20 text-amber-300 border border-amber-500/30
                                    @elseif($order->status === 'cancelled')
                                        bg-red-500/20 text-red-300 border border-red-500/30
                                    @else
                                        bg-sky-500/20 text-sky-300 border border-sky-500/30
                                    @endif
                                ">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-400">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="text-sky-400 hover:text-sky-300 font-medium transition">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                No hay pedidos disponibles
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Set Chart.js defaults for dark theme
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = '#334155';

    // Chart Colors
    const colors = {
        primary: '#0ea5e9',
        secondary: '#10b981',
        danger: '#ef4444',
        warning: '#f59e0b',
        purple: '#a855f7'
    };

    // Monthly Chart (Users & Orders)
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: @json($monthlyData['months']),
            datasets: [
                {
                    label: 'Usuarios',
                    data: @json($monthlyData['users']),
                    borderColor: colors.primary,
                    backgroundColor: 'rgba(14, 165, 233, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#1e293b',
                    pointBorderWidth: 2,
                    pointRadius: 5
                },
                {
                    label: 'Pedidos',
                    data: @json($monthlyData['orders']),
                    borderColor: colors.secondary,
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: colors.secondary,
                    pointBorderColor: '#1e293b',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        font: { size: 12 }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { drawBorder: false }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: @json($monthlyData['months']),
            datasets: [{
                label: 'Ingresos ($)',
                data: @json($monthlyData['revenue']),
                backgroundColor: colors.purple,
                borderColor: '#9333ea',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { drawBorder: false },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(0);
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Orders by Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(@json($ordersByStatus)).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
            datasets: [{
                data: Object.values(@json($ordersByStatus)),
                backgroundColor: [
                    colors.secondary,
                    colors.warning,
                    colors.danger,
                    colors.primary
                ],
                borderColor: '#1e293b',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        font: { size: 12 },
                        padding: 15
                    }
                }
            }
        }
    });
</script>
@endpush
