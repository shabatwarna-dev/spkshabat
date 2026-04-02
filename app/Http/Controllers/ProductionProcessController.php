<?php

namespace App\Http\Controllers;

use App\Models\ProductionProcess;
use App\Models\ProductionOrder;
use App\Models\ProcessEditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProductionProcessController extends Controller
{
    public function store(Request $request, ProductionOrder $order)
    {
        $validated = $request->validate([
            'nama_proses'      => 'required|string|max:255',
            'estimasi_selesai' => 'nullable|date',
            'jumlah_barang'    => 'nullable|numeric|min:0',
            'montage'          => 'nullable|string',
            'ukuran'           => 'nullable|string',
            'warna'            => 'nullable|string',
            'estimasi_hasil'   => 'nullable|numeric|min:0',
            'satuan'           => 'nullable|string',
            'catatan_marketing'=> 'nullable|string',
        ]);

        $urutan = $order->processes()->max('urutan') + 1;
        $order->processes()->create(array_merge($validated, ['urutan' => $urutan]));

        return back()->with('success', 'Proses berhasil ditambahkan.');
    }

    public function updateMarketing(Request $request, ProductionProcess $process)
    {
        $validated = $request->validate([
            'nama_proses'      => 'required|string|max:255',
            'estimasi_selesai' => 'nullable|date_format:Y-m-d\TH:i',
            'jumlah_barang'    => 'nullable|numeric|min:0',
            'montage'          => 'nullable|string',
            'ukuran'           => 'nullable|string',
            'warna'            => 'nullable|string',
            'estimasi_hasil'   => 'nullable|numeric|min:0',
            'satuan'           => 'nullable|string',
            'catatan_marketing'=> 'nullable|string',
        ]);

        $process->update($validated);
        return back()->with('success', 'Proses berhasil diperbarui.');
    }

    public function updateStatus(Request $request, ProductionProcess $process)
    {
        $request->validate(['status' => 'required|in:pending,proses,selesai,telat']);
        $process->update(['status' => $request->status]);
        return back()->with('success', 'Status diperbarui.');
    }

    public function updateProduksi(Request $request, ProductionProcess $process)
    {
        $user = auth()->user();

        // Validasi operator hanya bisa input proses yang sesuai namanya
        if ($user->isOperator() && !$user->canInputProcess($process)) {
            abort(403, 'Kamu hanya bisa input hasil untuk proses ' . $user->nama_proses . '.');
        }

        $isEdit = $process->sudahDiInput();

        $rules = [
            'hasil_jadi'    => 'required|numeric|min:0',
            'jumlah_reject' => 'nullable|numeric|min:0',
            'catatan_produksi' => 'nullable|string',
            'foto_hasil'    => 'nullable|image|max:5120',
        ];

        if ($isEdit) {
            $rules['tanggal_selesai_aktual'] = 'nullable|date_format:Y-m-d\TH:i';
            $rules['alasan_edit'] = 'required|string|min:10';
        }

        $validated = $request->validate($rules);

        // Simpan nilai lama untuk log
        $oldValues = [
            'hasil_jadi'            => $process->hasil_jadi,
            'jumlah_reject'         => $process->jumlah_reject,
            'tanggal_selesai_aktual'=> $process->tanggal_selesai_aktual?->format('d/m/Y H:i'),
            'catatan_produksi'      => $process->catatan_produksi,
        ];

        // Handle foto
        $fotoPath = $process->foto_hasil;
        if ($request->hasFile('foto_hasil')) {
            if ($fotoPath) Storage::disk('public')->delete($fotoPath);
            $fotoPath = $request->file('foto_hasil')->store('foto-hasil', 'public');
        }

        // Set tanggal selesai
        if ($isEdit) {
            $tanggalSelesai = $request->tanggal_selesai_aktual
                ? Carbon::parse($request->tanggal_selesai_aktual)
                : $process->tanggal_selesai_aktual;
        } else {
            $tanggalSelesai = Carbon::now();
        }

        // Update status berdasarkan estimasi
        $status = 'selesai';
        if ($process->estimasi_selesai && $tanggalSelesai->gt($process->estimasi_selesai)) {
            $status = 'telat';
        }

        // Data yang diupdate
        $updateData = [
            'hasil_jadi'            => $validated['hasil_jadi'],
            'jumlah_reject'         => $validated['jumlah_reject'] ?? 0,
            'catatan_produksi'      => $validated['catatan_produksi'] ?? null,
            'foto_hasil'            => $fotoPath,
            'tanggal_selesai_aktual'=> $tanggalSelesai,
            'status'                => $status,
        ];

        if (!$isEdit) {
            $updateData['first_input_at'] = now();
            $updateData['input_by']       = $user->id;
        } else {
            $updateData['is_edited']  = true;
            $updateData['edit_count'] = $process->edit_count + 1;
        }

        $process->update($updateData);

        // Simpan edit log
        if ($isEdit) {
            $changedFields = [];
            $newValues = [
                'hasil_jadi'            => $validated['hasil_jadi'],
                'jumlah_reject'         => $validated['jumlah_reject'] ?? 0,
                'tanggal_selesai_aktual'=> $tanggalSelesai->format('d/m/Y H:i'),
                'catatan_produksi'      => $validated['catatan_produksi'],
            ];

            $fieldLabels = [
                'hasil_jadi'            => 'Hasil Jadi',
                'jumlah_reject'         => 'Jumlah Reject',
                'tanggal_selesai_aktual'=> 'Tgl Selesai',
                'catatan_produksi'      => 'Catatan',
            ];

            foreach ($newValues as $key => $newVal) {
                if ((string)$oldValues[$key] !== (string)$newVal) {
                    $changedFields[] = [
                        'field'  => $fieldLabels[$key],
                        'before' => $oldValues[$key] ?? '-',
                        'after'  => $newVal ?? '-',
                    ];
                }
            }

            if (!empty($changedFields)) {
                ProcessEditLog::create([
                    'production_process_id' => $process->id,
                    'edited_by'             => $user->id,
                    'alasan_edit'           => $validated['alasan_edit'],
                    'changed_fields'        => json_encode($changedFields),
                ]);
            }
        }

        // Auto update status order
        $order = $process->order;
        $allDone = $order->processes->every(fn($p) => in_array($p->fresh()->status, ['selesai', 'telat']));
        if ($allDone && $order->status === 'produksi') {
            $order->update(['status' => 'selesai']);
        }

        return back()->with('success', $isEdit ? 'Hasil berhasil diperbarui.' : 'Hasil produksi berhasil disimpan.');
    }

    public function destroy(ProductionProcess $process)
    {
        $process->delete();
        return back()->with('success', 'Proses berhasil dihapus.');
    }

    public function reorder(Request $request, ProductionOrder $order)
    {
        $request->validate(['processes' => 'required|array']);
        foreach ($request->processes as $i => $id) {
            ProductionProcess::where('id', $id)->update(['urutan' => $i + 1]);
        }
        return response()->json(['status' => 'ok']);
    }
}
