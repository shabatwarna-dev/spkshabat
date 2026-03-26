<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\ProductionProcess;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isMasterAdmin()) {
            return $this->adminDashboard($request);
        }

        return $this->teamDashboard($user);
    }

    // ── Dashboard Master Admin ────────────────────────────────
    private function adminDashboard(Request $request)
    {
        $month  = $request->month ?? now()->format('Y-m');
        [$year, $mon] = explode('-', $month);
        $teamId = $request->team_id;

        $baseQuery = ProductionOrder::query()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $mon);

        if ($teamId) {
            $baseQuery->where('team_id', $teamId);
        }

        // Stats bulan ini
        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'selesai'  => (clone $baseQuery)->where('status', 'selesai')->count(),
            'produksi' => (clone $baseQuery)->where('status', 'produksi')->count(),
            'telat'    => (clone $baseQuery)->whereHas('processes', fn($q) => $q->where('status', 'telat'))->count(),
        ];

        // Stats all time
        $statsAll = [
            'total'   => ProductionOrder::count(),
            'users'   => User::where('role', '!=', 'master_admin')->count(),
            'teams'   => Team::where('is_active', true)->count(),
        ];

        // Per tim bulan ini
        $teamStats = Team::where('is_active', true)
            ->withCount([
                'orders as total_spk'    => fn($q) => $q->whereYear('created_at', $year)->whereMonth('created_at', $mon),
                'orders as spk_selesai'  => fn($q) => $q->whereYear('created_at', $year)->whereMonth('created_at', $mon)->where('status', 'selesai'),
                'orders as spk_produksi' => fn($q) => $q->whereYear('created_at', $year)->whereMonth('created_at', $mon)->where('status', 'produksi'),
                'orders as spk_telat'    => fn($q) => $q->whereYear('created_at', $year)->whereMonth('created_at', $mon)
                                                         ->whereHas('processes', fn($p) => $p->where('status', 'telat')),
            ])
            ->get();

        // SPK terlambat (semua tim)
        $lateProcesses = ProductionProcess::with('order.team')
            ->whereHas('order')
            ->where('status', '!=', 'selesai')
            ->whereNotNull('estimasi_selesai')
            ->whereDate('estimasi_selesai', '<', Carbon::today())
            ->orderBy('estimasi_selesai')
            ->limit(8)
            ->get();

        // Top customer bulan ini
        $topCustomers = (clone $baseQuery)
            ->selectRaw('nama_customer, count(*) as total')
            ->groupBy('nama_customer')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'nama_customer');

        // SPK terbaru bulan ini
        $recentOrders = (clone $baseQuery)
            ->with(['processes', 'team'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn($o) => tap($o, fn($o) => $o->is_late = $o->hasLateProcesses()));

        // Monthly trend 6 bulan
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $monthlyStats[] = [
                'label'   => $m->format('M Y'),
                'total'   => ProductionOrder::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count(),
                'selesai' => ProductionOrder::where('status', 'selesai')->whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count(),
            ];
        }

        $teams = Team::where('is_active', true)->get();

        return view('dashboard.admin', compact(
            'stats', 'statsAll', 'teamStats', 'lateProcesses',
            'topCustomers', 'recentOrders', 'monthlyStats',
            'teams', 'month', 'teamId'
        ));
    }

    // ── Dashboard PPIC & Koor ─────────────────────────────────
    private function teamDashboard($user)
    {
        $baseQuery = ProductionOrder::forUser($user);

        $totalSpk    = (clone $baseQuery)->count();
        $spkProduksi = (clone $baseQuery)->where('status', 'produksi')->count();
        $spkSelesai  = (clone $baseQuery)->where('status', 'selesai')->count();
        $spkTelat    = (clone $baseQuery)->whereHas('processes', fn($q) => $q->where('status', 'telat'))->count();

        $recentOrders = (clone $baseQuery)
            ->with(['processes', 'creator', 'team'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn($o) => tap($o, fn($o) => $o->is_late = $o->hasLateProcesses()));

        $lateProcesses = ProductionProcess::with('order')
            ->whereHas('order', fn($q) => $q->forUser($user))
            ->where('status', '!=', 'selesai')
            ->whereNotNull('estimasi_selesai')
            ->whereDate('estimasi_selesai', '<', Carbon::today())
            ->orderBy('estimasi_selesai')
            ->limit(5)
            ->get();

        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyStats[] = [
                'label'   => $month->format('M Y'),
                'total'   => (clone $baseQuery)->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count(),
                'selesai' => (clone $baseQuery)->where('status', 'selesai')->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count(),
            ];
        }

        return view('dashboard.index', compact(
            'user', 'totalSpk', 'spkProduksi', 'spkSelesai', 'spkTelat',
            'recentOrders', 'lateProcesses', 'monthlyStats'
        ));
    }
}
