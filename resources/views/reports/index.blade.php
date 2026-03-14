@extends('layouts.app')
@section('title', 'Laporan Bulanan')
@section('subtitle', 'Rekap produksi per bulan')

@section('content')
<div class="space-y-4 max-w-3xl mx-auto">

    {{-- Filter --}}
    <div class="card p-4">
        <form method="GET" class="flex gap-3 items-end">
            <div>
                <label class="form-label">Pilih Bulan</label>
                <input type="month" name="month" value="{{ $month }}" class="form-input">
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </form>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="stat-card text-center">
            <div class="stat-value text-gray-900">{{ $stats['total'] }}</div>
            <div class="stat-label">Total SPK</div>
        </div>
        <div class="stat-card text-center" style="border-top: 3px solid #22c55e;">
            <div class="stat-value text-green-600">{{ $stats['selesai'] }}</div>
            <div class="stat-label">Selesai</div>
        </div>
        <div class="stat-card text-center" style="border-top: 3px solid #3b82f6;">
            <div class="stat-value text-blue-600">{{ $stats['produksi'] }}</div>
            <div class="stat-label">On Progress</div>
        </div>
        <div class="stat-card text-center" style="border-top: 3px solid #ef4444;">
            <div class="stat-value text-red-600">{{ $stats['telat'] }}</div>
            <div class="stat-label">Ada Keterlambatan</div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Top customer --}}
        <div class="card p-5">
            <h3 class="font-700 text-gray-800 text-sm mb-4">Customer Terbanyak</h3>
            @forelse($topCustomers as $customer => $count)
            <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-700 flex items-center justify-center flex-shrink-0">
                    {{ $loop->iteration }}
                </div>
                <div class="flex-1 text-sm text-gray-700 truncate">{{ $customer }}</div>
                <div class="text-sm font-700 text-blue-600 flex-shrink-0">{{ $count }} SPK</div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>

        {{-- Process stats --}}
        <div class="card p-5">
            <h3 class="font-700 text-gray-800 text-sm mb-4">Statistik per Proses</h3>
            @forelse($processStats as $name => $stat)
            <div class="py-2.5 border-b border-gray-50 last:border-0">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-sm text-gray-700">{{ $name }}</span>
                    <div class="flex items-center gap-2">
                        @if($stat['telat'] > 0)
                        <span class="badge badge-telat text-[10px]">{{ $stat['telat'] }} telat</span>
                        @endif
                        <span class="text-xs font-700 {{ $stat['telat'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $stat['rate'] }}%
                        </span>
                    </div>
                </div>
                <div class="progress-track">
                    <div class="progress-fill {{ $stat['telat'] > 0 ? 'danger' : '' }}" style="width:{{ $stat['rate'] }}%"></div>
                </div>
                <div class="text-[11px] text-gray-400 mt-1">{{ $stat['selesai'] }} / {{ $stat['total'] }} selesai</div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>
    </div>

    {{-- SPK list this month --}}
    <div class="card">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-700 text-gray-800 text-sm">SPK Bulan Ini</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-sm font-600 text-gray-800">{{ $order->nomor_spk }}</span>
                        <span class="badge badge-{{ $order->status }}">{{ $order->status_label }}</span>
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ $order->nama_customer }} &middot; {{ $order->nama_barang }}</div>
                </div>
                <div class="text-xs font-600 text-gray-500 flex-shrink-0">{{ $order->progress_percent }}%</div>
            </a>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">Tidak ada SPK di bulan ini</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
