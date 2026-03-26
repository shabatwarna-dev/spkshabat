@extends('layouts.app')
@section('title', 'Laporan Semua Tim')
@section('subtitle', 'Pantau produksi seluruh jalur')

@section('content')
<div class="space-y-4 max-w-5xl mx-auto">

    {{-- Filter --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="form-label">Bulan</label>
                <input type="month" name="month" value="{{ $month }}" class="form-input">
            </div>
            <div class="min-w-40">
                <label class="form-label">Filter Tim</label>
                <select name="team_id" class="form-input">
                    <option value="">Semua Tim</option>
                    @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ $teamId == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan</button>
            @if($teamId)
            <a href="{{ route('admin.reports.index', ['month' => $month]) }}" class="btn btn-secondary">Semua Tim</a>
            @endif
        </form>
    </div>

    {{-- Global stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="stat-card">
            <div class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">Total SPK</div>
            <div class="stat-value text-gray-900">{{ $stats['total'] }}</div>
            <div class="stat-label">Bulan ini</div>
        </div>
        <div class="stat-card" style="border-top: 3px solid #22c55e;">
            <div class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">Selesai</div>
            <div class="stat-value text-green-600">{{ $stats['selesai'] }}</div>
            <div class="stat-label">{{ $stats['total'] > 0 ? round($stats['selesai']/$stats['total']*100) : 0 }}% dari total</div>
        </div>
        <div class="stat-card" style="border-top: 3px solid #3b82f6;">
            <div class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">On Progress</div>
            <div class="stat-value text-blue-600">{{ $stats['produksi'] }}</div>
            <div class="stat-label">Sedang berjalan</div>
        </div>
        <div class="stat-card" style="border-top: 3px solid #ef4444;">
            <div class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">Terlambat</div>
            <div class="stat-value text-red-600">{{ $stats['telat'] }}</div>
            <div class="stat-label">{{ $stats['telat'] > 0 ? 'Perlu perhatian' : 'On track' }}</div>
        </div>
    </div>

    {{-- Stats per tim --}}
    @if(!$teamId)
    <div class="card p-5">
        <div class="section-header">
            <div class="section-accent bg-blue-500"></div>
            <h3 class="font-700 text-gray-800 text-sm">Performa per Tim</h3>
        </div>
        <div class="space-y-3">
            @forelse($teamStats as $team)
            <div class="border border-gray-200 rounded-lg p-3">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-700 flex-shrink-0"
                             style="background: {{ $team->warna }}">
                            {{ strtoupper(substr($team->name, 0, 2)) }}
                        </div>
                        <div>
                            <span class="font-600 text-sm text-gray-800">{{ $team->name }}</span>
                            <span class="badge badge-{{ $team->jalur }} text-[10px] ml-1.5">{{ $team->jalur_label }}</span>
                        </div>
                    </div>
                    <div class="flex gap-3 text-right flex-shrink-0">
                        <div>
                            <div class="text-sm font-700 text-gray-800">{{ $team->total_spk }}</div>
                            <div class="text-[10px] text-gray-400">Total</div>
                        </div>
                        <div>
                            <div class="text-sm font-700 text-green-600">{{ $team->spk_selesai }}</div>
                            <div class="text-[10px] text-gray-400">Selesai</div>
                        </div>
                        <div>
                            <div class="text-sm font-700 text-blue-600">{{ $team->spk_produksi }}</div>
                            <div class="text-[10px] text-gray-400">Proses</div>
                        </div>
                        @if($team->spk_telat > 0)
                        <div>
                            <div class="text-sm font-700 text-red-600">{{ $team->spk_telat }}</div>
                            <div class="text-[10px] text-gray-400">Telat</div>
                        </div>
                        @endif
                    </div>
                </div>
                @if($team->total_spk > 0)
                <div class="progress-track">
                    <div class="progress-fill {{ $team->spk_telat > 0 ? 'danger' : '' }}"
                         style="width: {{ round($team->spk_selesai / $team->total_spk * 100) }}%"></div>
                </div>
                <div class="text-[11px] text-gray-400 mt-1">
                    {{ round($team->spk_selesai / $team->total_spk * 100) }}% selesai
                </div>
                @endif
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Belum ada data tim</p>
            @endforelse
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Top customer --}}
        <div class="card p-5">
            <h3 class="font-700 text-gray-800 text-sm mb-4">Customer Terbanyak</h3>
            @forelse($topCustomers as $customer => $count)
            <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-700 flex items-center justify-center flex-shrink-0">{{ $loop->iteration }}</div>
                <div class="flex-1 text-sm text-gray-700 truncate">{{ $customer }}</div>
                <div class="text-sm font-700 text-blue-600 flex-shrink-0">{{ $count }} SPK</div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>

        {{-- SPK list --}}
        <div class="card overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <h3 class="font-700 text-gray-800 text-sm">Daftar SPK Bulan Ini</h3>
            </div>
            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                @forelse($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-mono text-xs font-600 text-gray-800">{{ $order->nomor_spk }}</span>
                            <span class="badge badge-{{ $order->status }} text-[10px]">{{ $order->status_label }}</span>
                            @if($order->team)
                            <span class="text-[10px] px-1.5 py-0.5 rounded font-600"
                                  style="background: {{ $order->team->warna }}20; color: {{ $order->team->warna }};">
                                {{ $order->team->name }}
                            </span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 truncate">{{ $order->nama_customer }} &middot; {{ $order->nama_barang }}</div>
                    </div>
                    <span class="text-xs font-600 text-gray-500 flex-shrink-0">{{ $order->progress_percent }}%</span>
                </a>
                @empty
                <div class="px-4 py-6 text-center text-sm text-gray-400">Tidak ada SPK bulan ini</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
