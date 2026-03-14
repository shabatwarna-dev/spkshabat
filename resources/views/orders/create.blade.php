@extends('layouts.app')
@section('title', 'Buat SPK Baru')
@section('subtitle', 'Isi Surat Perintah Kerja')

@section('content')
<div x-data="spkForm()" class="max-w-3xl mx-auto space-y-4">
<form action="{{ route('orders.store') }}" method="POST">
    @csrf

    {{-- ── INFO SPK ── --}}
    <div class="card p-5">
        <div class="section-header">
            <div class="section-accent bg-blue-500"></div>
            <h3 class="font-700 text-gray-800">Informasi SPK</h3>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2 sm:col-span-1">
                <label class="form-label">Nomor SPK <span class="text-red-500">*</span></label>
                <input type="text" name="nomor_spk" value="{{ old('nomor_spk', $nextNomor) }}"
                       class="form-input font-mono" required>
                @error('nomor_spk')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="form-label">Tanggal Pesan <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_pesan" value="{{ old('tanggal_pesan', date('Y-m-d')) }}"
                       class="form-input" required>
            </div>
            <div>
                <label class="form-label">Tanggal Mulai Produksi</label>
                <input type="date" name="tanggal_produksi" value="{{ old('tanggal_produksi') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Estimasi Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai_estimasi" value="{{ old('tanggal_selesai_estimasi') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Tanggal Kirim</label>
                <input type="date" name="tanggal_kirim" value="{{ old('tanggal_kirim') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Nama Customer <span class="text-red-500">*</span></label>
                <input type="text" name="nama_customer" value="{{ old('nama_customer') }}"
                       class="form-input" placeholder="PT. Contoh / CV. XYZ" required>
            </div>
            <div class="col-span-2">
                <label class="form-label">Nama Barang <span class="text-red-500">*</span></label>
                <input type="text" name="nama_barang" value="{{ old('nama_barang') }}"
                       class="form-input" placeholder="Label Botol / Dus Produk / Stiker" required>
            </div>
            <div class="col-span-2">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" rows="2" class="form-input"
                          placeholder="Catatan tambahan untuk tim produksi...">{{ old('keterangan') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ── PROSES PRODUKSI ── --}}
    <div class="card p-5">
        <div class="section-header">
            <div class="section-accent bg-blue-500"></div>
            <h3 class="font-700 text-gray-800">Proses Produksi</h3>
            <span class="text-xs bg-blue-50 border border-blue-200 text-blue-700 px-2 py-0.5 rounded-md font-600"
                  x-text="processes.length + ' proses'"></span>
        </div>

        {{-- Quick-add buttons --}}
        <div class="mb-5">
            <p class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">Tambah proses cepat</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach(App\Models\ProductionProcess::defaultProcesses() as $p)
                <button type="button" @click="addProcess('{{ $p }}')"
                        class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600
                               hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition-colors font-500">
                    + {{ $p }}
                </button>
                @endforeach
                <button type="button" @click="addProcess('')"
                        class="text-xs px-3 py-1.5 border border-dashed border-blue-300 rounded-lg
                               text-blue-600 hover:bg-blue-50 transition-colors font-500">
                    + Proses Lain
                </button>
            </div>
        </div>

        {{-- Process list --}}
        <div class="space-y-3">
            <template x-for="(proc, index) in processes" :key="proc.id">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    {{-- Header --}}
                    <div class="bg-gray-50 px-3 py-2.5 flex items-center gap-2.5 border-b border-gray-200">
                        <span class="w-6 h-6 bg-blue-600 rounded-md text-white text-xs font-700 flex items-center justify-center flex-shrink-0"
                              x-text="index + 1"></span>
                        <input type="text" :name="'processes['+index+'][nama_proses]'" x-model="proc.nama_proses"
                               class="flex-1 text-sm font-600 bg-transparent border-none outline-none text-gray-800 placeholder-gray-400"
                               placeholder="Nama proses...">
                        <button type="button" @click="removeProcess(index)"
                                class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    {{-- Fields --}}
                    <div class="p-3 grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <div>
                            <label class="form-label">Est. Selesai</label>
                            <input type="date" :name="'processes['+index+'][estimasi_selesai]'" x-model="proc.estimasi_selesai" class="form-input text-xs">
                        </div>
                        <div>
                            <label class="form-label">Jumlah Barang</label>
                            <input type="number" :name="'processes['+index+'][jumlah_barang]'" x-model="proc.jumlah_barang" class="form-input text-xs" placeholder="0" step="1" min="0">
                        </div>
                        <div>
                            <label class="form-label">Satuan</label>
                            <input type="text" :name="'processes['+index+'][satuan]'" x-model="proc.satuan" class="form-input text-xs" placeholder="pcs / lembar / paket">
                        </div>
                        <div>
                            <label class="form-label">Montage</label>
                            <input type="text" :name="'processes['+index+'][montage]'" x-model="proc.montage" class="form-input text-xs" placeholder="1 Mata / 2 Mata">
                        </div>
                        <div>
                            <label class="form-label">Ukuran</label>
                            <input type="text" :name="'processes['+index+'][ukuran]'" x-model="proc.ukuran" class="form-input text-xs" placeholder="36x63 cm">
                        </div>
                        <div>
                            <label class="form-label">Warna</label>
                            <input type="text" :name="'processes['+index+'][warna]'" x-model="proc.warna" class="form-input text-xs" placeholder="4 warna">
                        </div>
                        <div>
                            <label class="form-label">Est. Hasil</label>
                            <input type="number" :name="'processes['+index+'][estimasi_hasil]'" x-model="proc.estimasi_hasil" class="form-input text-xs" placeholder="0" step="1" min="0">
                        </div>
                        <div class="col-span-2">
                            <label class="form-label">Catatan</label>
                            <input type="text" :name="'processes['+index+'][catatan_marketing]'" x-model="proc.catatan_marketing" class="form-input text-xs" placeholder="Catatan untuk tim produksi...">
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="processes.length === 0">
                <div class="border-2 border-dashed border-gray-200 rounded-lg p-10 text-center">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <p class="text-sm text-gray-400">Belum ada proses. Klik tombol di atas untuk menambahkan.</p>
                </div>
            </template>
        </div>

        @error('processes')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
    </div>

    {{-- Actions --}}
    <div class="flex gap-3 justify-end">
        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary" :disabled="processes.length === 0"
                :class="processes.length === 0 ? 'opacity-50 cursor-not-allowed' : ''">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan SPK
        </button>
    </div>
</form>
</div>

@push('scripts')
<script>
function spkForm() {
    return {
        processes: [], counter: 0,
        addProcess(name) {
            this.processes.push({
                id: ++this.counter, nama_proses: name,
                estimasi_selesai: '', jumlah_barang: '', montage: '',
                ukuran: '', warna: '', estimasi_hasil: '', satuan: '', catatan_marketing: '',
            });
        },
        removeProcess(index) { this.processes.splice(index, 1); }
    }
}
</script>
@endpush
@endsection
