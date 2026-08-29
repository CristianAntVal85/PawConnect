<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pet;
use App\Models\PetMatch;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Total statistics
        $totalUsers = User::count();
        $totalPets = Pet::count();
        $totalMatches = PetMatch::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_price');

        // Monthly statistics
        $monthlyData = $this->getMonthlyStats();

        // Orders by status
        $ordersByStatus = $this->getOrdersByStatus();

        // Recent orders
        $recentOrders = Order::with('user', 'product')
            ->latest()
            ->limit(10)
            ->get();

        // Percentage of users who have placed at least one order
        $repeatCustomersPercentage = $totalUsers > 0
            ? number_format((Order::distinct('user_id')->count() / $totalUsers) * 100, 1)
            : 0;

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalPets' => $totalPets,
            'totalMatches' => $totalMatches,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'monthlyData' => $monthlyData,
            'ordersByStatus' => $ordersByStatus,
            'recentOrders' => $recentOrders,
            'repeatCustomersPercentage' => $repeatCustomersPercentage,
        ]);
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
                ->sum('total_price');
            $revenue[] = $monthRevenue ?? 0;
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
}
