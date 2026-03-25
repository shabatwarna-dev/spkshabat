<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\ProductionProcess;
use App\Models\Team;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Base query difilter per tim
        $baseQuery = ProductionOrder::forUser($user);

        // Stats
        $totalSpk    = (clone $baseQuery)->count();
        $spkProduksi = (clone $baseQuery)->where('status', 'produksi')->count();
        $spkSelesai  = (clone $baseQuery)->where('status', 'selesai')->count();
        $spkTelat    = (clone $baseQuery)->whereHas('processes', fn($q) => $q->where('status', 'telat'))->count();

        // Recent orders
        $recentOrders = (clone $baseQuery)
            ->with(['processes', 'creator', 'team'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                $order->is_late = $order->hasLateProcesses();
                return $order;
            });

        // Late processes — filter per tim
        $lateProcessQuery = ProductionProcess::with('order')
            ->whereHas('order', function ($q) use ($user) {
                $q->forUser($user);
            })
            ->where('status', '!=', 'selesai')
            ->whereNotNull('estimasi_selesai')
            ->whereDate('estimasi_selesai', '<', Carbon::today())
            ->orderBy('estimasi_selesai')
            ->limit(5);

        $lateProcesses = $lateProcessQuery->get();

        // Monthly stats (6 bulan terakhir)
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyStats[] = [
                'label'   => $month->format('M Y'),
                'total'   => (clone $baseQuery)
                                ->whereYear('created_at', $month->year)
                                ->whereMonth('created_at', $month->month)
                                ->count(),
                'selesai' => (clone $baseQuery)
                                ->where('status', 'selesai')
                                ->whereYear('created_at', $month->year)
                                ->whereMonth('created_at', $month->month)
                                ->count(),
            ];
        }

        // Tim stats (hanya untuk master admin)
        $teamStats = null;
        if ($user->isMasterAdmin()) {
            $teamStats = Team::where('is_active', true)
                ->withCount([
                    'orders as total_spk',
                    'orders as spk_produksi' => fn($q) => $q->where('status', 'produksi'),
                    'orders as spk_selesai'  => fn($q) => $q->where('status', 'selesai'),
                    'orders as spk_telat'    => fn($q) => $q->whereHas('processes', fn($p) => $p->where('status', 'telat')),
                ])
                ->get();
        }

        return view('dashboard.index', compact(
            'user', 'totalSpk', 'spkProduksi', 'spkSelesai', 'spkTelat',
            'recentOrders', 'lateProcesses', 'monthlyStats', 'teamStats'
        ));
    }
}