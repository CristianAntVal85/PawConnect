<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pet;
use App\Models\PetMatch;
use App\Models\User;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function dashboard()
    {
        // El middleware 'is_admin' ya verifica el rol, así que podemos proceder
        return $this->success([
            'totals' => [
                'users' => User::count(),
                'pets' => Pet::count(),
                'matches' => PetMatch::count(),
                'orders' => Order::count(),
                'revenue' => Order::sum('total_price') ?? 0,
            ],
            'monthly' => $this->getMonthlyStats(),
            'ordersByStatus' => $this->getOrdersByStatus(),
            'metrics' => $this->calculateMetrics(),
        ], 'Estadísticas obtenidas correctamente.');
    }

    private function getMonthlyStats()
    {
        $months = [];
        $users = [];
        $orders = [];
        $revenue = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            $users[] = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $monthOrders = Order::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $orders[] = $monthOrders;

            $monthRevenue = Order::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_price') ?? 0;
            $revenue[] = $monthRevenue;
        }

        return [
            'months' => $months,
            'users' => $users,
            'orders' => $orders,
            'revenue' => $revenue,
        ];
    }

    private function getOrdersByStatus()
    {
        return Order::select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    private function calculateMetrics()
    {
        $totalUsers = User::count();
        $totalPets = Pet::count();
        $totalMatches = PetMatch::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_price') ?? 0;

        return [
            'avgPetsPerUser' => $totalUsers > 0 ? round($totalPets / $totalUsers, 2) : 0,
            'matchConversionRate' => $totalPets > 0 ? round(($totalMatches / $totalPets) * 100, 2) : 0,
            'avgTicket' => $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0,
            'userConversionRate' => $totalUsers > 0 ? round((Order::distinct('user_id')->count() / $totalUsers) * 100, 2) : 0,
        ];
    }
}
