@extends('layouts.app')
@section('title', 'Daftar SPK')
@section('subtitle', 'Surat Perintah Kerja Produksi')

@section('header-actions')
    @if(auth()->user()->isMarketing())
    <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Buat SPK
    </a>
    @endif
@endsection

@section('content')
<div class="space-y-4 max-w-4xl mx-auto">

    {{-- Filter --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div class="flex-1 min-w-44">
                <label class="form-label">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nomor SPK, customer, nama barang..." class="form-input">
            </div>
            <div class="min-w-36">
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="">Semua Status</option>
                    <option value="draft"    {{ request('status')=='draft'    ? 'selected' : '' }}>Draft</option>
                    <option value="produksi" {{ request('status')=='produksi' ? 'selected' : '' }}>Produksi</option>
                    <option value="selesai"  {{ request('status')=='selesai'  ? 'selected' : '' }}>Selesai</option>
                    <option value="kirim"    {{ request('status')=='kirim'    ? 'selected' : '' }}>Dikirim</option>
                    <option value="batal"    {{ request('status')=='batal'    ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('orders.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    {{-- Results count --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $orders->total() }} SPK ditemukan</p>
    </div>

    {{-- SPK List --}}
    <div class="space-y-2">
        @forelse($orders as $order)
        <div class="card hover:shadow-md transition-all duration-150">
            <a href="{{ route('orders.show', $order) }}" class="block p-4">
                <div class="flex items-start gap-4">

                    {{-- Status indicator --}}
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="w-2.5 h-2.5 rounded-full mt-1.5
                            {{ $order->status === 'produksi' ? 'bg-blue-500' : '' }}
                            {{ $order->status === 'selesai'  ? 'bg-green-500' : '' }}
                            {{ $order->status === 'kirim'    ? 'bg-violet-500' : '' }}
                            {{ $order->status === 'batal'    ? 'bg-gray-400' : '' }}
                            {{ $order->status === 'draft'    ? 'bg-yellow-500' : '' }}
                        "></div>
                    </div>

                    <div class="flex-1 min-w-0">
                        {{-- Row 1: Nomor + badges --}}
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            <span class="font-mono font-700 text-sm text-gray-900">{{ $order->nomor_spk }}</span>
                            <span class="badge badge-{{ $order->status }}">{{ $order->status_label }}</span>
                            @if($order->hasLateProcesses())
                            <span class="badge badge-telat">TERLAMBAT</span>
                            @endif
                        </div>

                        {{-- Row 2: Barang + Customer --}}
                        <div class="text-sm font-600 text-gray-700 truncate">{{ $order->nama_barang }}</div>
                        <div class="text-xs text-gray-400 mt-0.5 flex flex-wrap gap-3">
                            <span>{{ $order->nama_customer }}</span>
                            <span>Pesan: {{ $order->tanggal_pesan->format('d/m/Y') }}</span>
                            @if($order->tanggal_kirim)
                            <span>Kirim: {{ $order->tanggal_kirim->format('d/m/Y') }}</span>
                            @endif
                        </div>

                        {{-- Progress --}}
                        <div class="mt-3 flex items-center gap-3">
                            <div class="progress-track flex-1 max-w-48">
                                <div class="progress-fill {{ $order->hasLateProcesses() ? 'danger' : '' }}"
                                     style="width:{{ $order->progress_percent }}%"></div>
                            </div>
                            <span class="text-xs font-600 text-gray-500 flex-shrink-0">
                                {{ $order->processes->whereIn('status',['selesai','telat'])->count() }} / {{ $order->processes->count() }} proses
                                &middot; {{ $order->progress_percent }}%
                            </span>
                        </div>
                    </div>

                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            {{-- Process chips --}}
            @if($order->processes->count() > 0)
            <div class="px-4 pb-3 pt-0 flex flex-wrap gap-1.5 border-t border-gray-50">
                @foreach($order->processes as $proc)
                <span class="text-[11px] px-2.5 py-1 rounded-md font-500 border
                    {{ $proc->status === 'pending' ? 'chip-pending' : '' }}
                    {{ $proc->status === 'proses'  ? 'chip-proses' : '' }}
                    {{ $proc->status === 'selesai' ? 'chip-selesai' : '' }}
                    {{ $proc->status === 'telat'   ? 'chip-telat' : '' }}
                ">
                    {{ $proc->nama_proses }}
                    @if($proc->status === 'telat')
                    <span class="ml-0.5 font-700">!</span>
                    @endif
                </span>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="card p-14 text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-gray-500 font-500">Tidak ada SPK ditemukan</p>
            @if(auth()->user()->isMarketing())
            <a href="{{ route('orders.create') }}" class="btn btn-primary mt-4 inline-flex">Buat SPK Baru</a>
            @endif
        </div>
        @endforelse
    </div>

    {{ $orders->links() }}
</div>
@endsection
