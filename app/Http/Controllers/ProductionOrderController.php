<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\ProductionProcess;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductionOrderController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = ProductionOrder::with(['processes', 'creator', 'team'])
                                ->forUser($user);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nomor_spk', 'like', "%{$request->search}%")
                  ->orWhere('nama_customer', 'like', "%{$request->search}%")
                  ->orWhere('nama_barang', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->team_id) {
            $query->where('team_id', $request->team_id);
        }

        $orders = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        // Untuk filter dropdown tim
        $teams = $user->isMasterAdmin()
            ? Team::where('is_active', true)->get()
            : Team::whereIn('id', $user->teamIds())->get();

        return view('orders.index', compact('orders', 'teams'));
    }

    public function create()
    {
        $user  = auth()->user();
        $teams = Team::whereIn('id', $user->teamIds())
                    ->where('is_active', true)
                    ->get();

        if ($teams->isEmpty()) {
            return redirect()->route('orders.index')
                            ->with('error', 'Kamu belum tergabung dalam tim manapun.');
        }

        $defaultProcesses = ProductionProcess::defaultProcesses();
        $nextNomor = ProductionOrder::generateNomorSPK($teams->first(), 'general');

        // Generate nomor untuk semua kombinasi tim + tipe
        $teamNomors = [];
        foreach ($teams as $team) {
            $teamNomors[$team->id] = [
                'general'   => ProductionOrder::generateNomorSPK($team, 'general'),
                'corporate' => ProductionOrder::generateNomorSPK($team, 'corporate'),
            ];
        }

        return view('orders.create', compact('teams', 'defaultProcesses', 'nextNomor', 'teamNomors'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'team_id'                 => 'required|exists:teams,id',
            'tipe'                    => 'required|in:general,corporate',
            'nomor_spk'               => 'required|unique:production_orders',
            'tanggal_pesan'           => 'required|date',
            'tanggal_produksi'        => 'nullable|date',
            'tanggal_selesai_estimasi'=> 'nullable|date',
            'tanggal_kirim'           => 'nullable|date',
            'nama_customer'           => 'required|string|max:255',
            'nama_barang'             => 'required|string|max:255',
            'keterangan'              => 'nullable|string',
            'processes'               => 'required|array|min:1',
            'processes.*.nama_proses' => 'required|string',
            'processes.*.estimasi_selesai'  => 'nullable|date',
            'processes.*.jumlah_barang'     => 'nullable|numeric',
            'processes.*.montage'           => 'nullable|string',
            'processes.*.ukuran'            => 'nullable|string',
            'processes.*.warna'             => 'nullable|string',
            'processes.*.estimasi_hasil'    => 'nullable|numeric',
            'processes.*.satuan'            => 'nullable|string',
            'processes.*.catatan_marketing' => 'nullable|string',
        ]);

        // Pastikan user memang anggota tim yang dipilih
        if (!$user->canAccessTeam($validated['team_id'])) {
            abort(403, 'Kamu tidak bisa membuat SPK untuk tim ini.');
        }

        DB::transaction(function () use ($validated, $user) {
            $order = ProductionOrder::create([
                'team_id'                 => $validated['team_id'],
                'nomor_spk'               => $validated['nomor_spk'],
                'tanggal_pesan'           => $validated['tanggal_pesan'],
                'tanggal_produksi'        => $validated['tanggal_produksi'],
                'tanggal_selesai_estimasi'=> $validated['tanggal_selesai_estimasi'],
                'tanggal_kirim'           => $validated['tanggal_kirim'],
                'nama_customer'           => $validated['nama_customer'],
                'nama_barang'             => $validated['nama_barang'],
                'keterangan'              => $validated['keterangan'],
                'status'                  => 'produksi',
                'tipe'                    => $validated['tipe'],
                'created_by'              => $user->id,
            ]);

            foreach ($validated['processes'] as $i => $proc) {
                $order->processes()->create(array_merge($proc, ['urutan' => $i + 1]));
            }
        });

        return redirect()->route('orders.index')->with('success', 'SPK berhasil dibuat.');
    }

    public function show(ProductionOrder $order)
    {
        $this->authorizeAccess($order);
        $order->load(['processes', 'creator', 'team']);
        return view('orders.show', compact('order'));
    }

    public function edit(ProductionOrder $order)
    {
        $this->authorizeAccess($order);
        $order->load('processes');
        $defaultProcesses = ProductionProcess::defaultProcesses();
        return view('orders.edit', compact('order', 'defaultProcesses'));
    }

    public function update(Request $request, ProductionOrder $order)
    {
        $this->authorizeAccess($order);

        $validated = $request->validate([
            'tanggal_pesan'           => 'required|date',
            'tanggal_produksi'        => 'nullable|date',
            'tanggal_selesai_estimasi'=> 'nullable|date',
            'tanggal_kirim'           => 'nullable|date',
            'nama_customer'           => 'required|string|max:255',
            'nama_barang'             => 'required|string|max:255',
            'keterangan'              => 'nullable|string',
            'status'                  => 'required|in:draft,produksi,selesai,kirim,batal',
        ]);

        $order->update($validated);

        return redirect()->route('orders.show', $order)->with('success', 'SPK berhasil diperbarui.');
    }

    public function destroy(ProductionOrder $order)
    {
        $this->authorizeAccess($order);
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'SPK berhasil dihapus.');
    }

    public function exportPdf(ProductionOrder $order)
    {
        $this->authorizeAccess($order);
        $order->load(['processes', 'creator', 'team']);
        $pdf = Pdf::loadView('orders.pdf', compact('order'))
                  ->setPaper('a4', 'portrait');
        return $pdf->download('SPK-' . $order->nomor_spk . '.pdf');
    }

    public function history(Request $request)
    {
        $user      = auth()->user();
        $customers = ProductionOrder::forUser($user)->distinct()->pluck('nama_customer')->sort();

        $query = ProductionOrder::with(['processes', 'creator', 'team'])->forUser($user);

        if ($request->customer) {
            $query->where('nama_customer', $request->customer);
        }
        if ($request->from) {
            $query->whereDate('tanggal_pesan', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('tanggal_pesan', '<=', $request->to);
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('orders.history', compact('orders', 'customers'));
    }

    // ── Private helpers ───────────────────────────────────────

    private function authorizeAccess(ProductionOrder $order): void
    {
        $user = auth()->user();

        // Master admin bisa lihat semua SPK
        if ($user->isMasterAdmin()) return;

        if (!$user->canAccessTeam($order->team_id)) {
            abort(403, 'Kamu tidak memiliki akses ke SPK ini.');
        }
    }
}