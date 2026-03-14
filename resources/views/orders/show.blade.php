@extends('layouts.app')
@section('title', $order->nomor_spk)
@section('subtitle', $order->nama_customer . ' — ' . $order->nama_barang)

@section('header-actions')
    <a href="{{ route('orders.pdf', $order) }}" class="btn btn-secondary btn-sm" target="_blank">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Cetak PDF
    </a>
    @if(auth()->user()->isMarketing())
    <a href="{{ route('orders.edit', $order) }}" class="btn btn-primary btn-sm">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit SPK
    </a>
    @endif
@endsection

@section('content')
<div class="space-y-4 max-w-3xl mx-auto">

    {{-- ── HEADER SPK ── --}}
    <div class="card overflow-hidden">
        {{-- Color bar by status --}}
        <div class="h-1.5
            {{ $order->status === 'produksi' ? 'bg-blue-500' : '' }}
            {{ $order->status === 'selesai'  ? 'bg-green-500' : '' }}
            {{ $order->status === 'kirim'    ? 'bg-violet-500' : '' }}
            {{ $order->status === 'batal'    ? 'bg-gray-400' : '' }}
            {{ $order->status === 'draft'    ? 'bg-yellow-400' : '' }}
        "></div>

        <div class="p-5">
            {{-- Top row --}}
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="font-mono font-800 text-xl text-gray-900">{{ $order->nomor_spk }}</span>
                        <span class="badge badge-{{ $order->status }}">{{ $order->status_label }}</span>
                        @if($order->hasLateProcesses())
                        <span class="badge badge-telat">ADA KETERLAMBATAN</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400">
                        Dibuat oleh <strong class="text-gray-600">{{ $order->creator->name }}</strong>
                        &middot; {{ $order->created_at->format('d M Y, H:i') }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-800 text-gray-800">{{ $order->progress_percent }}<span class="text-xl text-gray-400">%</span></div>
                    <div class="text-xs text-gray-400">progress keseluruhan</div>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="progress-track mb-5" style="height:8px;">
                <div class="progress-fill {{ $order->hasLateProcesses() ? 'danger' : '' }}"
                     style="width:{{ $order->progress_percent }}%; height:8px;"></div>
            </div>

            {{-- Info grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4">
                <div>
                    <div class="info-label">Customer</div>
                    <div class="info-value">{{ $order->nama_customer }}</div>
                </div>
                <div>
                    <div class="info-label">Nama Barang</div>
                    <div class="info-value">{{ $order->nama_barang }}</div>
                </div>
                <div>
                    <div class="info-label">Tanggal Pesan</div>
                    <div class="info-value">{{ $order->tanggal_pesan->format('d M Y') }}</div>
                </div>
                @if($order->tanggal_produksi)
                <div>
                    <div class="info-label">Mulai Produksi</div>
                    <div class="info-value">{{ $order->tanggal_produksi->format('d M Y') }}</div>
                </div>
                @endif
                @if($order->tanggal_selesai_estimasi)
                <div>
                    <div class="info-label">Estimasi Selesai</div>
                    <div class="info-value {{ now()->gt($order->tanggal_selesai_estimasi) && $order->status !== 'selesai' ? 'text-red-600 font-700' : '' }}">
                        {{ $order->tanggal_selesai_estimasi->format('d M Y') }}
                    </div>
                </div>
                @endif
                @if($order->tanggal_kirim)
                <div>
                    <div class="info-label">Tanggal Kirim</div>
                    <div class="info-value">{{ $order->tanggal_kirim->format('d M Y') }}</div>
                </div>
                @endif
                @if($order->keterangan)
                <div class="col-span-2 sm:col-span-3">
                    <div class="info-label">Keterangan</div>
                    <div class="mt-1 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700">{{ $order->keterangan }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── PROSES PRODUKSI ── --}}
    <div class="card">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="section-accent bg-blue-500"></div>
                <h3 class="font-700 text-gray-800">Proses Produksi</h3>
                <span class="text-xs bg-blue-50 border border-blue-200 text-blue-700 px-2 py-0.5 rounded-md font-600">
                    {{ $order->processes->count() }} proses
                </span>
            </div>
            <div class="text-xs text-gray-400">
                {{ $order->processes->whereIn('status',['selesai','telat'])->count() }} selesai
            </div>
        </div>

        <div class="p-4 space-y-2.5">
            @forelse($order->processes->load('editLogs.editor', 'inputBy') as $process)

            <div class="process-row status-{{ $process->status }}"
                 x-data="{ open: false, editMode: false, showLog: false }">

                {{-- ── Process header row ── --}}
                <div class="process-row-header" @click="open = !open">

                    {{-- Status dot --}}
                    <div class="w-2.5 h-2.5 rounded-full flex-shrink-0 status-dot-{{ $process->status }}
                        {{ $process->status === 'proses' ? 'ring-2 ring-blue-200 ring-offset-1' : '' }}"></div>

                    {{-- Number --}}
                    <span class="w-6 h-6 rounded-md bg-gray-100 text-gray-600 text-xs font-700 flex items-center justify-center flex-shrink-0">
                        {{ $process->urutan }}
                    </span>

                    {{-- Name + badges --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-600 text-sm text-gray-800">{{ $process->nama_proses }}</span>
                            <span class="badge text-[10px] px-2 py-0.5
                                {{ $process->status === 'pending' ? 'chip-pending' : '' }}
                                {{ $process->status === 'proses'  ? 'chip-proses' : '' }}
                                {{ $process->status === 'selesai' ? 'chip-selesai' : '' }}
                                {{ $process->status === 'telat'   ? 'chip-telat' : '' }}
                            ">{{ $process->status_label }}</span>

                            @if($process->is_edited)
                            <span class="badge badge-edited text-[10px] px-2 py-0.5">
                                Diedit {{ $process->edit_count }}x
                            </span>
                            @endif

                            @if($process->isTelat() && $process->status !== 'selesai')
                            <span class="badge badge-telat text-[10px] px-2 py-0.5">TERLAMBAT</span>
                            @endif
                        </div>

                        {{-- Quick info --}}
                        <div class="flex flex-wrap gap-3 mt-1 text-xs text-gray-400">
                            @if($process->estimasi_selesai)
                            <span class="{{ $process->isTelat() ? 'text-red-500 font-600' : '' }}">
                                Est: {{ $process->estimasi_selesai->format('d/m/Y') }}
                            </span>
                            @endif
                            @if($process->jumlah_barang)
                            <span>{{ number_format($process->jumlah_barang,0,',','.') }} {{ $process->satuan }}</span>
                            @endif
                            @if($process->hasil_jadi)
                            <span class="text-green-600 font-500">Hasil: {{ number_format($process->hasil_jadi,0,',','.') }}</span>
                            @endif
                            @if($process->tanggal_selesai_aktual)
                            <span class="{{ $process->isTelat() ? 'text-red-500' : 'text-green-600' }}">
                                Selesai: {{ $process->tanggal_selesai_aktual->format('d/m/Y') }}
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Chevron --}}
                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 transition-transform duration-200"
                         :class="open ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- ── Expanded detail ── --}}
                <div x-show="open" x-cloak class="border-t border-gray-100">

                    {{-- Marketing data --}}
                    <div class="p-4 bg-gray-50">
                        <p class="text-[10px] font-700 text-gray-400 uppercase tracking-wider mb-3">Data dari Marketing</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <div class="info-label">Montage</div>
                                <div class="info-value text-sm">{{ $process->montage ?: '-' }}</div>
                            </div>
                            <div>
                                <div class="info-label">Ukuran</div>
                                <div class="info-value text-sm">{{ $process->ukuran ?: '-' }}</div>
                            </div>
                            <div>
                                <div class="info-label">Warna</div>
                                <div class="info-value text-sm">{{ $process->warna ?: '-' }}</div>
                            </div>
                            <div>
                                <div class="info-label">Est. Hasil</div>
                                <div class="info-value text-sm">
                                    {{ $process->estimasi_hasil ? number_format($process->estimasi_hasil,0,',','.') : '-' }}
                                    {{ $process->satuan }}
                                </div>
                            </div>
                            @if($process->catatan_marketing)
                            <div class="col-span-full">
                                <div class="info-label">Catatan Marketing</div>
                                <div class="text-sm text-gray-600 mt-1">{{ $process->catatan_marketing }}</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Hasil produksi (jika sudah diinput) --}}
                    @if($process->sudahDiInput())
                    <div class="p-4 bg-green-50 border-t border-green-100">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[10px] font-700 text-green-700 uppercase tracking-wider">Hasil Produksi</p>
                            @if($process->inputBy)
                            <span class="text-[11px] text-green-600">
                                Input: <strong>{{ $process->inputBy->name }}</strong>
                                @if($process->first_input_at)
                                &middot; {{ $process->first_input_at->format('d/m/Y H:i') }}
                                @endif
                            </span>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div>
                                <div class="info-label text-green-700">Hasil Jadi</div>
                                <div class="text-base font-800 text-green-800">
                                    {{ number_format($process->hasil_jadi,0,',','.') }}
                                    <span class="text-sm font-500">{{ $process->satuan }}</span>
                                </div>
                            </div>
                            @if($process->jumlah_reject)
                            <div>
                                <div class="info-label text-red-600">Reject</div>
                                <div class="text-base font-700 text-red-700">{{ number_format($process->jumlah_reject,0,',','.') }}</div>
                            </div>
                            @endif
                            @if($process->tanggal_selesai_aktual)
                            <div>
                                <div class="info-label text-green-700">Tgl Selesai Aktual</div>
                                <div class="text-sm font-700 {{ $process->isTelat() ? 'text-red-700' : 'text-green-800' }} mt-0.5">
                                    {{ $process->tanggal_selesai_aktual->format('d M Y') }}
                                    @if($process->isTelat())
                                    <span class="badge badge-telat ml-1 text-[10px]">TELAT</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                            @if($process->catatan_produksi)
                            <div class="col-span-full">
                                <div class="info-label text-green-700">Catatan Produksi</div>
                                <div class="text-sm text-gray-700 mt-1">{{ $process->catatan_produksi }}</div>
                            </div>
                            @endif

                            {{-- ── FOTO HASIL (fixed) ── --}}
                            @if($process->foto_hasil)
                            <div class="col-span-full">
                                <div class="info-label text-green-700 mb-2">Foto Hasil</div>
                                <a href="{{ Storage::url($process->foto_hasil) }}" target="_blank"
                                   title="Klik untuk perbesar">
                                    <img src="{{ Storage::url($process->foto_hasil) }}"
                                         alt="Foto hasil {{ $process->nama_proses }}"
                                         class="w-32 h-32 object-cover rounded-lg border-2 border-green-200 hover:border-green-400 hover:shadow-md transition-all cursor-zoom-in"
                                         onerror="this.parentElement.innerHTML='<span class=\'text-xs text-gray-400 italic\'>Foto tidak dapat ditampilkan</span>'">
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- ── RIWAYAT EDIT ── --}}
                    @if($process->is_edited && $process->editLogs->count() > 0)
                    <div class="border-t border-orange-100">
                        <button type="button" @click="showLog = !showLog"
                                class="w-full flex items-center justify-between px-4 py-3 bg-orange-50 hover:bg-orange-100 transition-colors text-left">
                            <span class="text-xs font-700 text-orange-700 uppercase tracking-wider">
                                Riwayat Edit &mdash; {{ $process->editLogs->count() }} kali perubahan
                            </span>
                            <svg class="w-3.5 h-3.5 text-orange-500 transition-transform"
                                 :class="showLog ? 'rotate-180' : ''"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="showLog" x-cloak class="divide-y divide-orange-100">
                            @foreach($process->editLogs as $log)
                            <div class="px-4 py-3 bg-white">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-700 text-gray-700">
                                        Perubahan #{{ $loop->iteration }}
                                        &mdash; <span class="text-orange-700">{{ $log->editor->name }}</span>
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                </div>

                                {{-- Alasan --}}
                                <div class="bg-orange-50 border border-orange-200 rounded-md px-3 py-2 mb-2">
                                    <span class="text-[10px] font-700 text-orange-600 uppercase tracking-wider">Alasan: </span>
                                    <span class="text-xs text-orange-900">{{ $log->alasan_edit }}</span>
                                </div>

                                {{-- Field changes --}}
                                <div class="space-y-1.5">
                                    @foreach($log->changed_fields as $change)
                                    <div class="flex items-center gap-2 text-xs bg-gray-50 rounded px-2.5 py-1.5">
                                        <span class="font-700 text-gray-600 w-28 flex-shrink-0">{{ $change['field'] }}</span>
                                        <span class="text-red-500 line-through">{{ $change['before'] }}</span>
                                        <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        <span class="text-green-700 font-700">{{ $change['after'] }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- ── INPUT HASIL (hanya Admin Produksi) ── --}}
                    @if(auth()->user()->isProduksi())

                    <div class="border-t border-gray-100 p-4">
                        <div x-show="!editMode">
                            <button @click="editMode = true; open = true"
                                    class="btn {{ $process->sudahDiInput() ? 'btn-secondary' : 'btn-primary' }} btn-sm">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if($process->sudahDiInput())
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    @endif
                                </svg>
                                {{ $process->sudahDiInput() ? 'Edit Hasil Produksi' : 'Input Hasil Produksi' }}
                            </button>
                        </div>

                        <div x-show="editMode" x-cloak>
                            <form action="{{ route('processes.updateProduksi', $process) }}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf @method('PUT')

                                @if($process->sudahDiInput())
                                <div class="alert alert-warning mb-4 text-sm">
                                    <strong>Mode Edit</strong> &mdash; Seluruh perubahan akan dicatat dalam riwayat edit.
                                    Wajib mengisi alasan perubahan.
                                </div>
                                @else
                                <p class="text-xs font-700 text-gray-600 uppercase tracking-wider mb-4">Input Hasil Produksi</p>
                                @endif

                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="form-label">Hasil Jadi <span class="text-red-500">*</span></label>
                                        <input type="number" name="hasil_jadi"
                                               value="{{ $process->hasil_jadi }}"
                                               class="form-input" placeholder="0" step="1" min="0" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Jumlah Reject</label>
                                        <input type="number" name="jumlah_reject"
                                               value="{{ $process->jumlah_reject ?? 0 }}"
                                               class="form-input" placeholder="0" step="1" min="0">
                                    </div>

                                    @if($process->sudahDiInput())
                                    {{-- Edit mode: tanggal bisa diubah tapi maks hari ini --}}
                                    <div>
                                        <label class="form-label">Tgl Selesai Aktual</label>
                                        <input type="date" name="tanggal_selesai_aktual"
                                               value="{{ $process->tanggal_selesai_aktual?->format('Y-m-d') }}"
                                               class="form-input" max="{{ date('Y-m-d') }}">
                                    </div>
                                    @else
                                    {{-- Input pertama: tanggal otomatis hari ini, tidak bisa dimanipulasi --}}
                                    <div>
                                        <label class="form-label">Tanggal Selesai</label>
                                        <div class="form-input readonly text-sm">
                                            {{ now()->format('d M Y') }}
                                            <span class="text-xs text-gray-400 ml-1">(otomatis)</span>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="col-span-2">
                                        <label class="form-label">Catatan Produksi</label>
                                        <input type="text" name="catatan_produksi"
                                               value="{{ $process->catatan_produksi }}"
                                               class="form-input" placeholder="Keterangan hasil produksi...">
                                    </div>

                                    <div>
                                        <label class="form-label">Foto Hasil</label>
                                        <input type="file" name="foto_hasil" accept="image/*"
                                               class="form-input text-xs py-1.5">
                                        @if($process->foto_hasil)
                                        <p class="text-[11px] text-gray-400 mt-1">Sudah ada foto sebelumnya</p>
                                        @endif
                                    </div>

                                    @if($process->sudahDiInput())
                                    <div class="col-span-full">
                                        <label class="form-label text-orange-700">
                                            Alasan Perubahan <span class="text-red-500">*</span>
                                        </label>
                                        <textarea name="alasan_edit" rows="2"
                                                  class="form-input" style="border-color: #fb923c;"
                                                  placeholder="Jelaskan alasan mengapa data ini diubah... (minimal 10 karakter)"
                                                  required minlength="10"></textarea>
                                    </div>
                                    @endif
                                </div>

                                <div class="flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        {{ $process->sudahDiInput() ? 'Simpan Perubahan' : 'Simpan Hasil' }}
                                    </button>
                                    <button type="button" @click="editMode = false" class="btn btn-secondary btn-sm">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @elseif(auth()->user()->isMarketing())
                    <div class="border-t border-gray-100 px-4 py-3">
                        <p class="text-xs text-gray-400 italic">Input hasil hanya dapat dilakukan oleh Admin Produksi.</p>
                    </div>
                    @endif

                </div>{{-- end expanded --}}
            </div>{{-- end process-row --}}

            @empty
            <div class="py-10 text-center text-gray-400 text-sm">Belum ada proses ditambahkan.</div>
            @endforelse
        </div>
    </div>

    {{-- Delete button --}}
    @if(auth()->user()->isMarketing())
    <div class="flex justify-end">
        <form action="{{ route('orders.destroy', $order) }}" method="POST"
              onsubmit="return confirm('Hapus SPK {{ $order->nomor_spk }}? Tindakan ini tidak dapat dibatalkan.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Hapus SPK Ini</button>
        </form>
    </div>
    @endif

</div>
@endsection
