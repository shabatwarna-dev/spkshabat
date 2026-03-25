<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\ProductionProcess;
use App\Models\Team;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    // Laporan untuk PPIC & Koor — hanya tim sendiri
    public function index(Request $request)
    {
        $user  = auth()->user();
        $month = $request->month ?? now()->format('Y-m');
        [$year, $mon] = explode('-', $month);

        $baseQuery = ProductionOrder::forUser($user)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $mon);

        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'selesai'  => (clone $baseQuery)->where('status', 'selesai')->count(),
            'produksi' => (clone $baseQuery)->where('status', 'produksi')->count(),
            'telat'    => (clone $baseQuery)->whereHas('processes', fn($q) => $q->where('status', 'telat'))->count(),
        ];

        $orders = (clone $baseQuery)->with(['processes', 'team'])->orderByDesc('created_at')->get();

        $topCustomers = (clone $baseQuery)
            ->selectRaw('nama_customer, count(*) as total')
            ->groupBy('nama_customer')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'nama_customer');

        $processStats = $this->getProcessStats($year, $mon, $user);

        return view('reports.index', compact('stats', 'orders', 'topCustomers', 'processStats', 'month'));
    }

    // Laporan untuk Master Admin — semua tim, bisa filter per tim
    public function adminIndex(Request $request)
    {
        $month = $request->month ?? now()->format('Y-m');
        [$year, $mon] = explode('-', $month);

        $teams     = Team::where('is_active', true)->withCount('orders')->get();
        $teamId    = $request->team_id;

        $baseQuery = ProductionOrder::with(['processes', 'team', 'creator'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $mon);

        if ($teamId) {
            $baseQuery->where('team_id', $teamId);
        }

        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'selesai'  => (clone $baseQuery)->where('status', 'selesai')->count(),
            'produksi' => (clone $baseQuery)->where('status', 'produksi')->count(),
            'telat'    => (clone $baseQuery)->whereHas('processes', fn($q) => $q->where('status', 'telat'))->count(),
        ];

        $orders = (clone $baseQuery)->orderByDesc('created_at')->get();

        // Stats per tim bulan ini
        $teamStats = Team::where('is_active', true)
            ->withCount([
                'orders as total_spk'    => fn($q) => $q->whereYear('created_at', $year)->whereMonth('created_at', $mon),
                'orders as spk_selesai'  => fn($q) => $q->whereYear('created_at', $year)->whereMonth('created_at', $mon)->where('status', 'selesai'),
                'orders as spk_produksi' => fn($q) => $q->whereYear('created_at', $year)->whereMonth('created_at', $mon)->where('status', 'produksi'),
                'orders as spk_telat'    => fn($q) => $q->whereYear('created_at', $year)->whereMonth('created_at', $mon)
                                                         ->whereHas('processes', fn($p) => $p->where('status', 'telat')),
            ])
            ->get();

        $topCustomers = (clone $baseQuery)
            ->selectRaw('nama_customer, count(*) as total')
            ->groupBy('nama_customer')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'nama_customer');

        return view('reports.admin', compact(
            'stats', 'orders', 'teams', 'teamStats',
            'topCustomers', 'month', 'teamId'
        ));
    }

    private function getProcessStats(string $year, string $mon, $user): array
    {
        $processes = ProductionProcess::whereHas('order', function ($q) use ($year, $mon, $user) {
            $q->forUser($user)
              ->whereYear('created_at', $year)
              ->whereMonth('created_at', $mon);
        })->get();

        $stats = [];
        foreach ($processes->groupBy('nama_proses') as $name => $group) {
            $total   = $group->count();
            $selesai = $group->whereIn('status', ['selesai', 'telat'])->count();
            $telat   = $group->where('status', 'telat')->count();
            $stats[$name] = [
                'total'   => $total,
                'selesai' => $selesai,
                'telat'   => $telat,
                'rate'    => $total > 0 ? round($selesai / $total * 100) : 0,
            ];
        }

        return $stats;
    }
}