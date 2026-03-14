@extends('layouts.app')
@section('title', 'Edit SPK')
@section('subtitle', $order->nomor_spk . ' — ' . $order->nama_customer)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    {{-- ── Info SPK ── --}}
    <div class="card p-5">
        <div class="section-header">
            <div class="section-accent bg-blue-500"></div>
            <h3 class="font-700 text-gray-800">Informasi SPK</h3>
            <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md border border-gray-200">
                {{ $order->nomor_spk }}
            </span>
        </div>

        <form action="{{ route('orders.update', $order) }}" method="POST">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Tanggal Pesan <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_pesan"
                           value="{{ old('tanggal_pesan', $order->tanggal_pesan->format('Y-m-d')) }}"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Mulai Produksi</label>
                    <input type="date" name="tanggal_produksi"
                           value="{{ old('tanggal_produksi', $order->tanggal_produksi?->format('Y-m-d')) }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Est. Selesai</label>
                    <input type="date" name="tanggal_selesai_estimasi"
                           value="{{ old('tanggal_selesai_estimasi', $order->tanggal_selesai_estimasi?->format('Y-m-d')) }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Tanggal Kirim</label>
                    <input type="date" name="tanggal_kirim"
                           value="{{ old('tanggal_kirim', $order->tanggal_kirim?->format('Y-m-d')) }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Nama Customer <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_customer"
                           value="{{ old('nama_customer', $order->nama_customer) }}"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        @foreach(['draft'=>'Draft','produksi'=>'Produksi','selesai'=>'Selesai','kirim'=>'Dikirim','batal'=>'Dibatalkan'] as $val => $label)
                        <option value="{{ $val }}" {{ $order->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Nama Barang <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_barang"
                           value="{{ old('nama_barang', $order->nama_barang) }}"
                           class="form-input" required>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" rows="2" class="form-input">{{ old('keterangan', $order->keterangan) }}</textarea>
                </div>
            </div>
            <div class="flex gap-3 justify-end mt-5 pt-4 border-t border-gray-100">
                <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    {{-- ── Kelola Proses ── --}}
    <div class="card p-5">
        <div class="section-header">
            <div class="section-accent bg-blue-500"></div>
            <h3 class="font-700 text-gray-800">Kelola Proses Produksi</h3>
            <span class="text-xs text-gray-400">{{ $order->processes->count() }} proses</span>
        </div>

        <div class="space-y-2 mb-4">
            @forelse($order->processes as $proc)
            <div class="border border-gray-200 rounded-lg overflow-hidden" x-data="{ editMode: false }">
                <div class="flex items-center gap-2.5 px-3.5 py-2.5 bg-gray-50">
                    <span class="w-6 h-6 bg-blue-600 rounded-md text-white text-xs font-700 flex items-center justify-center flex-shrink-0">
                        {{ $proc->urutan }}
                    </span>
                    <span class="flex-1 text-sm font-600 text-gray-800">{{ $proc->nama_proses }}</span>
                    <span class="badge text-[10px]
                        {{ $proc->status === 'pending' ? 'chip-pending' : '' }}
                        {{ $proc->status === 'proses'  ? 'chip-proses' : '' }}
                        {{ $proc->status === 'selesai' ? 'chip-selesai' : '' }}
                        {{ $proc->status === 'telat'   ? 'chip-telat' : '' }}
                    ">{{ $proc->status_label }}</span>
                    <button @click="editMode = !editMode" class="btn btn-secondary btn-xs">
                        Edit
                    </button>
                    <form action="{{ route('processes.destroy', $proc) }}" method="POST"
                          onsubmit="return confirm('Hapus proses ini?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                    </form>
                </div>

                <div x-show="editMode" x-cloak class="p-3 border-t border-gray-100">
                    <form action="{{ route('processes.updateMarketing', $proc) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            <div class="col-span-full">
                                <label class="form-label">Nama Proses <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_proses" value="{{ $proc->nama_proses }}"
                                       class="form-input text-xs" required>
                            </div>
                            <div>
                                <label class="form-label">Est. Selesai</label>
                                <input type="date" name="estimasi_selesai"
                                       value="{{ $proc->estimasi_selesai?->format('Y-m-d') }}" class="form-input text-xs">
                            </div>
                            <div>
                                <label class="form-label">Jumlah Barang</label>
                                <input type="number" name="jumlah_barang" value="{{ $proc->jumlah_barang }}"
                                       class="form-input text-xs" step="1" min="0">
                            </div>
                            <div>
                                <label class="form-label">Satuan</label>
                                <input type="text" name="satuan" value="{{ $proc->satuan }}" class="form-input text-xs">
                            </div>
                            <div>
                                <label class="form-label">Montage</label>
                                <input type="text" name="montage" value="{{ $proc->montage }}" class="form-input text-xs">
                            </div>
                            <div>
                                <label class="form-label">Ukuran</label>
                                <input type="text" name="ukuran" value="{{ $proc->ukuran }}" class="form-input text-xs">
                            </div>
                            <div>
                                <label class="form-label">Warna</label>
                                <input type="text" name="warna" value="{{ $proc->warna }}" class="form-input text-xs">
                            </div>
                            <div>
                                <label class="form-label">Est. Hasil</label>
                                <input type="number" name="estimasi_hasil" value="{{ $proc->estimasi_hasil }}"
                                       class="form-input text-xs" step="1" min="0">
                            </div>
                            <div class="col-span-full">
                                <label class="form-label">Catatan</label>
                                <input type="text" name="catatan_marketing" value="{{ $proc->catatan_marketing }}"
                                       class="form-input text-xs">
                            </div>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                            <button type="button" @click="editMode = false" class="btn btn-secondary btn-sm">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-6">Belum ada proses.</p>
            @endforelse
        </div>

        {{-- Add process --}}
        <div x-data="{ addMode: false }">
            <button @click="addMode = true" x-show="!addMode" class="btn btn-secondary w-full">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Proses
            </button>
            <div x-show="addMode" x-cloak class="border border-dashed border-blue-300 rounded-lg p-4 bg-blue-50/30">
                <p class="text-xs font-700 text-gray-600 uppercase tracking-wider mb-3">Proses Baru</p>
                <form action="{{ route('processes.store', $order) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <div class="col-span-full">
                            <label class="form-label">Nama Proses <span class="text-red-500">*</span></label>
                            <select name="nama_proses" class="form-input text-xs" required>
                                <option value="">Pilih proses...</option>
                                @foreach($defaultProcesses as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="form-label">Est. Selesai</label><input type="date" name="estimasi_selesai" class="form-input text-xs"></div>
                        <div><label class="form-label">Jumlah</label><input type="number" name="jumlah_barang" class="form-input text-xs" step="1" min="0"></div>
                        <div><label class="form-label">Satuan</label><input type="text" name="satuan" class="form-input text-xs" placeholder="pcs"></div>
                        <div><label class="form-label">Ukuran</label><input type="text" name="ukuran" class="form-input text-xs"></div>
                        <div><label class="form-label">Warna</label><input type="text" name="warna" class="form-input text-xs"></div>
                        <div><label class="form-label">Est. Hasil</label><input type="number" name="estimasi_hasil" class="form-input text-xs" step="1" min="0"></div>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary btn-sm">Tambahkan</button>
                        <button type="button" @click="addMode = false" class="btn btn-secondary btn-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
