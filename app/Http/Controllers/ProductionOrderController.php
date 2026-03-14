<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\ProductionProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductionOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionOrder::with(['processes', 'creator']);

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

        if ($request->customer) {
            $query->where('nama_customer', 'like', "%{$request->customer}%");
        }

        $orders = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $defaultProcesses = ProductionProcess::defaultProcesses();
        $nextNomor = ProductionOrder::generateNomorSPK();
        return view('orders.create', compact('defaultProcesses', 'nextNomor'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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
            'processes.*.estimasi_selesai' => 'nullable|date',
            'processes.*.jumlah_barang'    => 'nullable|numeric',
            'processes.*.montage'          => 'nullable|string',
            'processes.*.ukuran'           => 'nullable|string',
            'processes.*.warna'            => 'nullable|string',
            'processes.*.estimasi_hasil'   => 'nullable|numeric',
            'processes.*.satuan'           => 'nullable|string',
            'processes.*.catatan_marketing'=> 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $order = ProductionOrder::create([
                'nomor_spk'               => $validated['nomor_spk'],
                'tanggal_pesan'           => $validated['tanggal_pesan'],
                'tanggal_produksi'        => $validated['tanggal_produksi'],
                'tanggal_selesai_estimasi'=> $validated['tanggal_selesai_estimasi'],
                'tanggal_kirim'           => $validated['tanggal_kirim'],
                'nama_customer'           => $validated['nama_customer'],
                'nama_barang'             => $validated['nama_barang'],
                'keterangan'              => $validated['keterangan'],
                'status'                  => 'produksi',
                'created_by'              => auth()->id(),
            ]);

            foreach ($validated['processes'] as $i => $proc) {
                $order->processes()->create(array_merge($proc, ['urutan' => $i + 1]));
            }
        });

        return redirect()->route('orders.index')->with('success', 'SPK berhasil dibuat!');
    }

    public function show(ProductionOrder $order)
    {
        $order->load(['processes', 'creator']);
        return view('orders.show', compact('order'));
    }

    public function edit(ProductionOrder $order)
    {
        $this->authorizeMarketing();
        $order->load('processes');
        $defaultProcesses = ProductionProcess::defaultProcesses();
        return view('orders.edit', compact('order', 'defaultProcesses'));
    }

    public function update(Request $request, ProductionOrder $order)
    {
        $this->authorizeMarketing();

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

        return redirect()->route('orders.show', $order)->with('success', 'SPK berhasil diperbarui!');
    }

    public function destroy(ProductionOrder $order)
    {
        $this->authorizeMarketing();
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'SPK berhasil dihapus.');
    }

    public function exportPdf(ProductionOrder $order)
    {
        $order->load(['processes', 'creator']);
        $pdf = Pdf::loadView('orders.pdf', compact('order'))
                  ->setPaper('a4', 'portrait');
        return $pdf->download('SPK-' . $order->nomor_spk . '.pdf');
    }

    public function history(Request $request)
    {
        $customers = ProductionOrder::distinct()->pluck('nama_customer')->sort();

        $query = ProductionOrder::with(['processes', 'creator']);

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

    private function authorizeMarketing()
    {
        if (!auth()->user()->isMarketing()) {
            abort(403, 'Hanya admin marketing yang dapat melakukan aksi ini.');
        }
    }
}
