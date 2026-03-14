<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\ProductionProcess;
use App\Models\ProcessEditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductionProcessController extends Controller
{
    // Marketing: Add process to order
    public function store(Request $request, ProductionOrder $order)
    {
        $this->authorizeMarketing();

        $validated = $request->validate([
            'nama_proses'       => 'required|string',
            'estimasi_selesai'  => 'nullable|date',
            'jumlah_barang'     => 'nullable|numeric',
            'montage'           => 'nullable|string',
            'ukuran'            => 'nullable|string',
            'warna'             => 'nullable|string',
            'estimasi_hasil'    => 'nullable|numeric',
            'satuan'            => 'nullable|string',
            'catatan_marketing' => 'nullable|string',
        ]);

        $urutan = $order->processes()->max('urutan') + 1;
        $order->processes()->create(array_merge($validated, ['urutan' => $urutan]));

        return back()->with('success', 'Proses berhasil ditambahkan.');
    }

    // Marketing: Update process details (hanya field marketing, BUKAN hasil produksi)
    public function updateMarketing(Request $request, ProductionProcess $process)
    {
        $this->authorizeMarketing();

        $validated = $request->validate([
            'nama_proses'       => 'required|string',
            'estimasi_selesai'  => 'nullable|date',
            'jumlah_barang'     => 'nullable|numeric',
            'montage'           => 'nullable|string',
            'ukuran'            => 'nullable|string',
            'warna'             => 'nullable|string',
            'estimasi_hasil'    => 'nullable|numeric',
            'satuan'            => 'nullable|string',
            'catatan_marketing' => 'nullable|string',
        ]);

        $process->update($validated);

        return back()->with('success', 'Detail proses diperbarui.');
    }

    // Produksi: Input/edit hasil — HANYA role produksi
    public function updateProduksi(Request $request, ProductionProcess $process)
    {
        // ✅ Hanya produksi yang boleh input hasil
        if (!auth()->user()->isProduksi()) {
            abort(403, 'Hanya admin produksi yang dapat menginput hasil produksi.');
        }

        $isEditMode = $process->sudahDiInput(); // sudah pernah diinput sebelumnya?

        $rules = [
            'hasil_jadi'             => 'nullable|numeric',
            'jumlah_reject'          => 'nullable|numeric',
            'catatan_produksi'       => 'nullable|string',
            'foto_hasil'             => 'nullable|image|max:5120',
        ];

        // Tanggal: jika input pertama → otomatis hari ini (tidak bisa diubah)
        // Jika edit → boleh ubah tapi wajib isi alasan
        if ($isEditMode) {
            $rules['tanggal_selesai_aktual'] = 'nullable|date';
            $rules['alasan_edit'] = 'required|string|min:10';
        }

        $validated = $request->validate($rules, [
            'alasan_edit.required' => 'Wajib isi alasan pengeditan (minimal 10 karakter).',
            'alasan_edit.min'      => 'Alasan edit minimal 10 karakter.',
        ]);

        // Simpan nilai lama sebelum di-update (untuk log)
        $before = [
            'hasil_jadi'             => $process->hasil_jadi,
            'jumlah_reject'          => $process->jumlah_reject,
            'tanggal_selesai_aktual' => $process->tanggal_selesai_aktual,
            'catatan_produksi'       => $process->catatan_produksi,
            'foto_hasil'             => $process->foto_hasil,
        ];

        // Handle photo upload
        $fotoPath = $process->foto_hasil;
        if ($request->hasFile('foto_hasil')) {
            $fotoPath = $request->file('foto_hasil')->store('foto-hasil', 'public');
        }

        // Tanggal selesai:
        // - Input pertama: otomatis hari ini (tidak bisa dimanipulasi)
        // - Edit: pakai nilai dari form (boleh diubah, tapi tercatat di log)
        if ($isEditMode) {
            $tanggalSelesai = $validated['tanggal_selesai_aktual'] ?? $process->tanggal_selesai_aktual;
        } else {
            $tanggalSelesai = Carbon::today()->toDateString(); // otomatis hari ini
        }

        // Auto-set status
        $status = $process->status;
        if ($tanggalSelesai) {
            $estimasi = $process->estimasi_selesai;
            $status = ($estimasi && Carbon::parse($tanggalSelesai)->gt($estimasi)) ? 'telat' : 'selesai';
        } elseif (!empty($validated['hasil_jadi'])) {
            $status = 'proses';
        }

        // Bangun data update
        $updateData = [
            'hasil_jadi'             => $validated['hasil_jadi'] ?? $process->hasil_jadi,
            'jumlah_reject'          => $validated['jumlah_reject'] ?? 0,
            'tanggal_selesai_aktual' => $tanggalSelesai,
            'catatan_produksi'       => $validated['catatan_produksi'] ?? $process->catatan_produksi,
            'foto_hasil'             => $fotoPath,
            'status'                 => $status,
        ];

        if ($isEditMode) {
            // Tandai sudah diedit
            $updateData['is_edited']  = true;
            $updateData['edit_count'] = $process->edit_count + 1;

            // Simpan log edit
            ProcessEditLog::create([
                'production_process_id'  => $process->id,
                'edited_by'              => auth()->id(),
                'hasil_jadi_before'      => $before['hasil_jadi'],
                'jumlah_reject_before'   => $before['jumlah_reject'],
                'tanggal_selesai_before' => $before['tanggal_selesai_aktual'],
                'catatan_before'         => $before['catatan_produksi'],
                'foto_before'            => $before['foto_hasil'],
                'hasil_jadi_after'       => $updateData['hasil_jadi'],
                'jumlah_reject_after'    => $updateData['jumlah_reject'],
                'tanggal_selesai_after'  => $tanggalSelesai,
                'catatan_after'          => $updateData['catatan_produksi'],
                'foto_after'             => $fotoPath,
                'alasan_edit'            => $validated['alasan_edit'],
            ]);
        } else {
            // Input pertama: catat siapa dan kapan
            $updateData['first_input_at'] = now();
            $updateData['input_by']       = auth()->id();
        }

        $process->update($updateData);

        // Cek apakah semua proses sudah selesai → update status order
        $order = $process->order;
        $allDone = $order->processes()->whereNotIn('status', ['selesai', 'telat'])->doesntExist();
        if ($allDone) {
            $order->update(['status' => 'selesai', 'tanggal_selesai_aktual' => now()->toDateString()]);
        }

        $msg = $isEditMode ? 'Hasil produksi berhasil diperbarui. Edit tercatat dalam log.' : 'Hasil produksi berhasil disimpan.';
        return back()->with('success', $msg);
    }

    // Marketing: Change process status manually
    public function updateStatus(Request $request, ProductionProcess $process)
    {
        $this->authorizeMarketing();

        $validated = $request->validate([
            'status' => 'required|in:pending,proses,selesai,telat',
        ]);

        $process->update($validated);

        return back()->with('success', 'Status proses diperbarui.');
    }

    // Reorder processes
    public function reorder(Request $request, ProductionOrder $order)
    {
        $this->authorizeMarketing();

        $validated = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:production_processes,id',
        ]);

        foreach ($validated['order'] as $i => $id) {
            ProductionProcess::where('id', $id)->update(['urutan' => $i + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(ProductionProcess $process)
    {
        $this->authorizeMarketing();
        $process->delete();
        return back()->with('success', 'Proses dihapus.');
    }

    private function authorizeMarketing()
    {
        if (!auth()->user()->isMarketing()) {
            abort(403, 'Hanya admin marketing yang dapat melakukan aksi ini.');
        }
    }
}
