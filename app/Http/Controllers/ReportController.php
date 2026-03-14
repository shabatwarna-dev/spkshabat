<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\ProductionProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->month ?? now()->format('Y-m');
        $carbonMonth = Carbon::parse($month . '-01');

        $orders = ProductionOrder::with(['processes', 'creator'])
            ->whereYear('created_at', $carbonMonth->year)
            ->whereMonth('created_at', $carbonMonth->month)
            ->get();

        $stats = [
            'total'    => $orders->count(),
            'selesai'  => $orders->where('status', 'selesai')->count(),
            'produksi' => $orders->where('status', 'produksi')->count(),
            'telat'    => $orders->filter(fn($o) => $o->hasLateProcesses())->count(),
        ];

        // Top customers
        $topCustomers = $orders->groupBy('nama_customer')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->take(5);

        // Process completion rates
        $processStats = ProductionProcess::whereHas('order', function ($q) use ($carbonMonth) {
                $q->whereYear('created_at', $carbonMonth->year)
                  ->whereMonth('created_at', $carbonMonth->month);
            })
            ->get()
            ->groupBy('nama_proses')
            ->map(function ($processes) {
                $total = $processes->count();
                $selesai = $processes->whereIn('status', ['selesai', 'telat'])->count();
                $telat = $processes->where('status', 'telat')->count();
                return [
                    'total'    => $total,
                    'selesai'  => $selesai,
                    'telat'    => $telat,
                    'rate'     => $total > 0 ? round($selesai / $total * 100) : 0,
                ];
            });

        return view('reports.index', compact('orders', 'stats', 'topCustomers', 'processStats', 'month'));
    }
}
