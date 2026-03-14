<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\ProductionProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Stats
        $totalSpk       = ProductionOrder::count();
        $spkProduksi    = ProductionOrder::where('status', 'produksi')->count();
        $spkSelesai     = ProductionOrder::where('status', 'selesai')->count();
        $spkTelat       = ProductionOrder::whereHas('processes', fn($q) => $q->where('status', 'telat'))->count();

        // Recent orders with late flag
        $recentOrders = ProductionOrder::with(['processes', 'creator'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                $order->is_late = $order->hasLateProcesses();
                return $order;
            });

        // Processes that are late today
        $lateProcesses = ProductionProcess::with('order')
            ->whereHas('order')
            ->where('status', '!=', 'selesai')
            ->whereNotNull('estimasi_selesai')
            ->whereDate('estimasi_selesai', '<', Carbon::today())
            ->orderBy('estimasi_selesai')
            ->limit(5)
            ->get();

        // Monthly stats for chart (last 6 months)
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyStats[] = [
                'label'   => $month->format('M Y'),
                'total'   => ProductionOrder::whereYear('created_at', $month->year)
                                ->whereMonth('created_at', $month->month)->count(),
                'selesai' => ProductionOrder::where('status', 'selesai')
                                ->whereYear('created_at', $month->year)
                                ->whereMonth('created_at', $month->month)->count(),
            ];
        }

        return view('dashboard.index', compact(
            'user', 'totalSpk', 'spkProduksi', 'spkSelesai', 'spkTelat',
            'recentOrders', 'lateProcesses', 'monthlyStats'
        ));
    }
}
