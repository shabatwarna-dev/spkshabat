@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan produksi hari ini')

@section('content')
<div class="space-y-5 max-w-5xl mx-auto">

    {{-- Greeting bar --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-sm">Selamat datang,</p>
            <h2 class="text-gray-900 font-bold text-lg leading-tight">{{ auth()->user()->name }}
                <span class="text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md px-2 py-0.5 ml-2 uppercase">{{ auth()->user()->role }}</span>
            </h2>
        </div>
        <div class="text-right hidden sm:block">
            <p class="text-xs text-gray-400">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="stat-card">
            <div class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">Total SPK</div>
            <div class="stat-value text-gray-900">{{ $totalSpk }}</div>
            <div class="stat-label">Semua waktu</div>
        </div>
        <div class="stat-card" style="border-top: 3px solid #3b82f6;">
            <div class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">Produksi</div>
            <div class="stat-value text-blue-600">{{ $spkProduksi }}</div>
            <div class="stat-label">Sedang berjalan</div>
        </div>
        <div class="stat-card" style="border-top: 3px solid #22c55e;">
            <div class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">Selesai</div>
            <div class="stat-value text-green-600">{{ $spkSelesai }}</div>
            <div class="stat-label">Sudah selesai</div>
        </div>
        <div class="stat-card" style="border-top: 3px solid #ef4444;">
            <div class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">Terlambat</div>
            <div class="stat-value text-red-600">{{ $spkTelat }}</div>
            <div class="stat-label flex items-center gap-1.5">
                @if($spkTelat > 0)
                <span class="w-1.5 h-1.5 bg-red-500 rounded-full inline-block" style="animation: pulse 1.5s infinite;"></span>
                <span>Perlu perhatian</span>
                @else
                <span>Semua on-track</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Late processes alert --}}
    @if($lateProcesses->count() > 0)
    <div class="alert alert-danger">
        <p class="font-700 text-sm mb-3">Proses melewati batas estimasi</p>
        <div class="space-y-2">
            @foreach($lateProcesses as $proc)
            <div class="flex items-center justify-between bg-white/60 rounded-lg px-3 py-2">
                <div>
                    <a href="{{ route('orders.show', $proc->order) }}" class="text-sm font-600 text-red-800 hover:underline">
                        {{ $proc->order->nomor_spk }} &mdash; {{ $proc->nama_proses }}
                    </a>
                    <div class="text-xs text-red-600 mt-0.5">{{ $proc->order->nama_customer }}</div>
                </div>
                <div class="text-right flex-shrink-0 ml-3">
                    <span class="badge badge-telat text-[10px]">TERLAMBAT</span>
                    <div class="text-xs text-red-500 mt-1">Est: {{ $proc->estimasi_selesai->format('d/m/Y') }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        {{-- Recent SPK --}}
        <div class="card lg:col-span-3">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-700 text-gray-800 text-sm">SPK Terbaru</h3>
                <a href="{{ route('orders.index') }}" class="text-xs text-blue-600 font-600 hover:underline">Lihat semua</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentOrders as $order)
                <a href="{{ route('orders.show', $order) }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-mono font-600 text-sm text-gray-800">{{ $order->nomor_spk }}</span>
                            <span class="badge badge-{{ $order->status }}">{{ $order->status_label }}</span>
                            @if($order->is_late)
                            <span class="badge badge-telat text-[10px]">TELAT</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 truncate mb-2">{{ $order->nama_customer }} &middot; {{ $order->nama_barang }}</div>
                        <div class="flex items-center gap-2">
                            <div class="progress-track flex-1 max-w-24">
                                <div class="progress-fill {{ $order->is_late ? 'danger' : '' }}" style="width:{{ $order->progress_percent }}%"></div>
                            </div>
                            <span class="text-[11px] font-600 text-gray-500">{{ $order->progress_percent }}%</span>
                        </div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @empty
                <div class="px-5 py-10 text-center text-gray-400 text-sm">Belum ada SPK</div>
                @endforelse
            </div>
        </div>

        {{-- Monthly chart --}}
        <div class="card lg:col-span-2 p-5">
            <h3 class="font-700 text-gray-800 text-sm mb-4">Tren 6 Bulan Terakhir</h3>
            <div class="space-y-3">
                @foreach($monthlyStats as $stat)
                @php $maxVal = max(collect($monthlyStats)->pluck('total')->max(), 1); @endphp
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-gray-400 w-14 flex-shrink-0 text-right">{{ $stat['label'] }}</span>
                    <div class="flex-1 flex items-center gap-1">
                        @if($stat['total'] > 0)
                        <div class="h-5 bg-blue-100 rounded flex items-center justify-end pr-1.5 min-w-[28px]"
                             style="width: {{ round($stat['total']/$maxVal*100) }}%">
                            <span class="text-[10px] font-700 text-blue-600">{{ $stat['total'] }}</span>
                        </div>
                        @endif
                        @if($stat['selesai'] > 0)
                        <div class="h-5 bg-green-100 rounded flex items-center justify-end pr-1.5 min-w-[24px]"
                             style="width: {{ round($stat['selesai']/$maxVal*100) }}%">
                            <span class="text-[10px] font-700 text-green-600">{{ $stat['selesai'] }}</span>
                        </div>
                        @endif
                        @if($stat['total'] === 0)
                        <div class="text-[11px] text-gray-300">-</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            <div class="flex gap-4 mt-5 pt-4 border-t border-gray-100">
                <div class="flex items-center gap-1.5">
                    <div class="w-3 h-3 bg-blue-200 rounded"></div>
                    <span class="text-[11px] text-gray-500">Total SPK</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-3 h-3 bg-green-200 rounded"></div>
                    <span class="text-[11px] text-gray-500">Selesai</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
