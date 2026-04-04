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

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Pilih Tim --}}
            <div class="col-span-full">
                <label class="form-label">Tim Produksi <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($teams as $team)
                    <label class="flex items-center gap-2.5 border-2 rounded-lg px-3 py-2.5 cursor-pointer transition-all"
                           :class="selectedTeam == {{ $team->id }} ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="team_id" value="{{ $team->id }}"
                               x-model="selectedTeam"
                               @change="updateNomorSPK({{ $team->id }})"
                               {{ $loop->first ? 'checked' : '' }}
                               class="accent-blue-600">
                        <div class="w-6 h-6 rounded flex items-center justify-center text-white text-[10px] font-700 flex-shrink-0"
                             style="background: {{ $team->warna }}">
                            {{ strtoupper(substr($team->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="text-sm font-600 text-gray-800">{{ $team->name }}</div>
                            <div class="text-[10px] text-gray-400">{{ $team->jalur_label }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('team_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Tipe: General / Corporate --}}
            <div class="col-span-full">
                <label class="form-label">Tipe SPK <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2.5 border-2 rounded-lg px-3 py-2.5 cursor-pointer transition-all"
                           :class="selectedTipe === 'general' ? 'border-gray-500 bg-gray-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="tipe" value="general" x-model="selectedTipe"
                               @change="updateNomorSPK(selectedTeam)"
                               checked class="accent-gray-600">
                        <div>
                            <div class="text-sm font-700 text-gray-800">General</div>
                            <div class="text-[10px] text-gray-400">SPK reguler / biasa</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 border-2 rounded-lg px-3 py-2.5 cursor-pointer transition-all"
                           :class="selectedTipe === 'corporate' ? 'border-amber-500 bg-amber-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="tipe" value="corporate" x-model="selectedTipe"
                               @change="updateNomorSPK(selectedTeam)"
                               class="accent-amber-600">
                        <div>
                            <div class="text-sm font-700 text-amber-700">Corporate</div>
                            <div class="text-[10px] text-amber-600">Diutamakan / prioritas</div>
                        </div>
                    </label>
                </div>
                @error('tipe')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Nomor SPK --}}
            <div>
                <label class="form-label">Nomor SPK <span class="text-red-500">*</span></label>
                <input type="text" name="nomor_spk" x-model="nomorSpk"
                       class="form-input font-mono" required>
                @error('nomor_spk')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Tanggal Pesan --}}
            <div>
                <label class="form-label">Tanggal Pesan <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_pesan"
                       value="{{ old('tanggal_pesan', date('Y-m-d')) }}"
                       class="form-input" required>
            </div>

            {{-- Mulai Produksi + jam --}}
            <div>
                <label class="form-label">Mulai Produksi</label>
                <input type="datetime-local" name="tanggal_produksi"
                       value="{{ old('tanggal_produksi') }}"
                       class="form-input">
            </div>

            {{-- Estimasi Selesai + jam --}}
            <div>
                <label class="form-label">Estimasi Selesai</label>
                <input type="datetime-local" name="tanggal_selesai_estimasi"
                       value="{{ old('tanggal_selesai_estimasi') }}"
                       class="form-input">
            </div>

            {{-- Tanggal Kirim --}}
            <div>
                <label class="form-label">Tanggal Kirim</label>
                <input type="date" name="tanggal_kirim"
                       value="{{ old('tanggal_kirim') }}"
                       class="form-input">
            </div>

            {{-- Customer --}}
            <div>
                <label class="form-label">Nama Customer <span class="text-red-500">*</span></label>
                <input type="text" name="nama_customer" value="{{ old('nama_customer') }}"
                       class="form-input" placeholder="PT. Contoh / CV. XYZ" required>
            </div>

            {{-- Nama Barang --}}
            <div class="col-span-full">
                <label class="form-label">Nama Barang <span class="text-red-500">*</span></label>
                <input type="text" name="nama_barang" value="{{ old('nama_barang') }}"
                       class="form-input" placeholder="Label Botol / Dus Produk / Stiker" required>
            </div>

            {{-- Keterangan --}}
            <div class="col-span-full">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" rows="2" class="form-input"
                          placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
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

        <div class="mb-4">
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

        <div class="space-y-3">
            <template x-for="(proc, index) in processes" :key="proc.id">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
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
                    <div class="p-3 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        {{-- Estimasi selesai proses dengan jam --}}
                        <div class="sm:col-span-2">
                            <label class="form-label">Est. Selesai (Tanggal & Jam)</label>
                            <input type="datetime-local"
                                   :name="'processes['+index+'][estimasi_selesai]'"
                                   x-model="proc.estimasi_selesai"
                                   class="form-input text-xs">
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
                        <div class="sm:col-span-2">
                            <label class="form-label">Catatan</label>
                            <input type="text" :name="'processes['+index+'][catatan_marketing]'" x-model="proc.catatan_marketing" class="form-input text-xs" placeholder="Catatan untuk tim produksi...">
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="processes.length === 0">
                <div class="border-2 border-dashed border-gray-200 rounded-lg p-10 text-center">
                    <p class="text-sm text-gray-400">Belum ada proses. Klik tombol di atas untuk menambahkan.</p>
                </div>
            </template>
        </div>
        @error('processes')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
    </div>

    {{-- Actions --}}
    <div class="flex gap-3 justify-end">
        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary"
                :disabled="processes.length === 0"
                :class="processes.length === 0 ? 'opacity-50 cursor-not-allowed' : ''">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan SPK
        </button>
    </div>
</form>
</div>

@push('scripts')
<script>
const teamNomors = {!! json_encode($teamNomors) !!};

function spkForm() {
    return {
        processes:    [],
        counter:      0,
        selectedTeam: {{ $teams->first()->id ?? 'null' }},
        selectedTipe: 'general',
        nomorSpk:     '{{ $nextNomor }}',

        addProcess(name) {
            this.processes.push({
                id: ++this.counter, nama_proses: name,
                estimasi_selesai: '', jumlah_barang: '', montage: '',
                ukuran: '', warna: '', estimasi_hasil: '', satuan: '', catatan_marketing: '',
            });
        },
        removeProcess(index) {
            this.processes.splice(index, 1);
        },
        updateNomorSPK(teamId) {
            const tipeData = teamNomors[teamId];
            if (tipeData) {
                this.nomorSpk = tipeData[this.selectedTipe] || this.nomorSpk;
            }
        }
    }
}
</script>
@endpush
@endsection