@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'Pantau seluruh produksi')

@section('content')
<div class="space-y-5 max-w-5xl mx-auto">

    {{-- Filter --}}
    <div class="card p-3">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div class="flex-1 min-w-36">
                <label class="form-label">Bulan</label>
                <input type="month" name="month" value="{{ $month }}" class="form-input">
            </div>
            <div class="flex-1 min-w-36">
                <label class="form-label">Tim</label>
                <select name="team_id" class="form-input">
                    <option value="">Semua Tim</option>
                    @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ $teamId == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if($teamId)
            <a href="{{ route('dashboard', ['month' => $month]) }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </form>
    </div>

    {{-- Summary all time --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="stat-card text-center">
            <div class="stat-value text-gray-900">{{ $statsAll['total'] }}</div>
            <div class="stat-label">Total SPK</div>
        </div>
        <div class="stat-card text-center">
            <div class="stat-value text-blue-600">{{ $statsAll['teams'] }}</div>
            <div class="stat-label">Tim Aktif</div>
        </div>
        <div class="stat-card text-center">
            <div class="stat-value text-gray-700">{{ $statsAll['users'] }}</div>
            <div class="stat-label">Total User</div>
        </div>
    </div>

    {{-- Stats bulan ini --}}
    <div>
        <p class="text-xs font-600 text-gray-400 uppercase tracking-wider mb-2">
            Bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->isoFormat('MMMM Y') }}
            @if($teamId) &mdash; {{ $teams->firstWhere('id', $teamId)?->name }} @endif
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="stat-card">
                <div class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">Total SPK</div>
                <div class="stat-value text-gray-900">{{ $stats['total'] }}</div>
            </div>
            <div class="stat-card" style="border-top: 3px solid #22c55e;">
                <div class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">Selesai</div>
                <div class="stat-value text-green-600">{{ $stats['selesai'] }}</div>
                <div class="stat-label">{{ $stats['total'] > 0 ? round($stats['selesai']/$stats['total']*100) : 0 }}%</div>
            </div>
            <div class="stat-card" style="border-top: 3px solid #3b82f6;">
                <div class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">On Progress</div>
                <div class="stat-value text-blue-600">{{ $stats['produksi'] }}</div>
            </div>
            <div class="stat-card" style="border-top: 3px solid #ef4444;">
                <div class="text-xs font-600 text-gray-500 uppercase tracking-wider mb-2">Terlambat</div>
                <div class="stat-value text-red-600">{{ $stats['telat'] }}</div>
                <div class="stat-label flex items-center gap-1">
                    @if($stats['telat'] > 0)
                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full" style="animation:pulse 1.5s infinite"></span>
                    Perlu perhatian
                    @else
                    On track
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Performa per Tim --}}
    @if(!$teamId)
    <div class="card p-5">
        <div class="section-header">
            <div class="section-accent bg-blue-500"></div>
            <h3 class="font-700 text-gray-800 text-sm">Performa per Tim</h3>
            <span class="text-xs text-gray-400">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->isoFormat('MMMM Y') }}</span>
        </div>
        <div class="space-y-3">
            @forelse($teamStats as $team)
            <div class="border border-gray-200 rounded-lg p-3">
                <div class="flex items-center justify-between gap-3 mb-2 flex-wrap">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-700 flex-shrink-0"
                             style="background: {{ $team->warna }}">
                            {{ strtoupper(substr($team->name, 0, 2)) }}
                        </div>
                        <div>
                            <span class="font-600 text-sm text-gray-800">{{ $team->name }}</span>
                            <span class="badge badge-{{ $team->jalur }} text-[10px] ml-1.5">{{ $team->jalur_label }}</span>
                        </div>
                    </div>
                    <div class="flex gap-4 text-right flex-shrink-0">
                        <div><div class="text-sm font-700 text-gray-800">{{ $team->total_spk }}</div><div class="text-[10px] text-gray-400">Total</div></div>
                        <div><div class="text-sm font-700 text-green-600">{{ $team->spk_selesai }}</div><div class="text-[10px] text-gray-400">Selesai</div></div>
                        <div><div class="text-sm font-700 text-blue-600">{{ $team->spk_produksi }}</div><div class="text-[10px] text-gray-400">Proses</div></div>
                        @if($team->spk_telat > 0)
                        <div><div class="text-sm font-700 text-red-600">{{ $team->spk_telat }}</div><div class="text-[10px] text-gray-400">Telat</div></div>
                        @endif
                    </div>
                </div>
                @if($team->total_spk > 0)
                <div class="progress-track">
                    <div class="progress-fill {{ $team->spk_telat > 0 ? 'danger' : '' }}"
                         style="width: {{ round($team->spk_selesai / $team->total_spk * 100) }}%"></div>
                </div>
                <div class="text-[11px] text-gray-400 mt-1">{{ round($team->spk_selesai / $team->total_spk * 100) }}% selesai</div>
                @else
                <div class="text-[11px] text-gray-300 mt-1">Belum ada SPK bulan ini</div>
                @endif
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Belum ada tim aktif</p>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Proses terlambat --}}
    @if($lateProcesses->count() > 0)
    <div class="alert alert-danger">
        <p class="font-700 text-sm mb-3">Proses melewati batas estimasi</p>
        <div class="space-y-2">
            @foreach($lateProcesses as $proc)
            <div class="flex items-center justify-between bg-white/60 rounded-lg px-3 py-2 flex-wrap gap-2">
                <div class="min-w-0">
                    <a href="{{ route('orders.show', $proc->order) }}" class="text-sm font-600 text-red-800 hover:underline">
                        {{ $proc->order->nomor_spk }} &mdash; {{ $proc->nama_proses }}
                    </a>
                    <div class="text-xs text-red-600 mt-0.5 flex items-center gap-2">
                        <span>{{ $proc->order->nama_customer }}</span>
                        @if($proc->order->team)
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-600"
                              style="background: {{ $proc->order->team->warna }}20; color: {{ $proc->order->team->warna }}">
                            {{ $proc->order->team->name }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="badge badge-telat text-[10px]">TERLAMBAT</span>
                    <div class="text-xs text-red-500 mt-1">Est: {{ $proc->estimasi_selesai->format('d/m/Y') }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        {{-- SPK terbaru --}}
        <div class="card lg:col-span-3">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-700 text-gray-800 text-sm">SPK Terbaru</h3>
                <span class="text-xs text-gray-400">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->isoFormat('MMMM Y') }}</span>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentOrders as $order)
                <a href="{{ route('orders.show', $order) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5 mb-0.5">
                            <span class="font-mono font-600 text-xs text-gray-800">{{ $order->nomor_spk }}</span>
                            <span class="badge badge-{{ $order->status }} text-[10px]">{{ $order->status_label }}</span>
                            @if($order->team)
                            <span class="text-[10px] px-1.5 py-0.5 rounded font-600"
                                  style="background: {{ $order->team->warna }}20; color: {{ $order->team->warna }}">
                                {{ $order->team->name }}
                            </span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 truncate">{{ $order->nama_customer }} &middot; {{ $order->nama_barang }}</div>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="progress-track flex-1 max-w-24">
                                <div class="progress-fill {{ $order->is_late ? 'danger' : '' }}" style="width:{{ $order->progress_percent }}%"></div>
                            </div>
                            <span class="text-[11px] font-600 text-gray-500">{{ $order->progress_percent }}%</span>
                        </div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Belum ada SPK bulan ini</div>
                @endforelse
            </div>
        </div>

        {{-- Sidebar kanan --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Top customer --}}
            <div class="card p-4">
                <h3 class="font-700 text-gray-800 text-sm mb-3">Customer Terbanyak</h3>
                @forelse($topCustomers as $customer => $count)
                <div class="flex items-center gap-2.5 py-2 border-b border-gray-50 last:border-0">
                    <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-700 flex items-center justify-center flex-shrink-0">{{ $loop->iteration }}</div>
                    <div class="flex-1 text-xs text-gray-700 truncate">{{ $customer }}</div>
                    <div class="text-xs font-700 text-blue-600 flex-shrink-0">{{ $count }}</div>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-3">Tidak ada data</p>
                @endforelse
            </div>

            {{-- Tren 6 bulan --}}
            <div class="card p-4">
                <h3 class="font-700 text-gray-800 text-sm mb-3">Tren 6 Bulan</h3>
                <div class="space-y-2.5">
                    @foreach($monthlyStats as $stat)
                    @php $maxVal = max(collect($monthlyStats)->pluck('total')->max(), 1); @endphp
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] text-gray-400 w-14 flex-shrink-0 text-right">{{ $stat['label'] }}</span>
                        <div class="flex-1 flex items-center gap-1">
                            @if($stat['total'] > 0)
                            <div class="h-4 bg-blue-100 rounded flex items-center justify-end pr-1 min-w-[20px]"
                                 style="width: {{ round($stat['total']/$maxVal*100) }}%">
                                <span class="text-[9px] font-700 text-blue-600">{{ $stat['total'] }}</span>
                            </div>
                            @endif
                            @if($stat['selesai'] > 0)
                            <div class="h-4 bg-green-100 rounded flex items-center justify-end pr-1 min-w-[18px]"
                                 style="width: {{ round($stat['selesai']/$maxVal*100) }}%">
                                <span class="text-[9px] font-700 text-green-600">{{ $stat['selesai'] }}</span>
                            </div>
                            @endif
                            @if($stat['total'] === 0)<div class="text-[11px] text-gray-200">-</div>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="flex gap-3 mt-3 pt-3 border-t border-gray-100">
                    <div class="flex items-center gap-1"><div class="w-2.5 h-2.5 bg-blue-200 rounded"></div><span class="text-[10px] text-gray-400">Total</span></div>
                    <div class="flex items-center gap-1"><div class="w-2.5 h-2.5 bg-green-200 rounded"></div><span class="text-[10px] text-gray-400">Selesai</span></div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection