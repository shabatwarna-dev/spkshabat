@extends('layouts.app')
@section('title', 'Riwayat SPK')
@section('subtitle', 'History per customer')

@section('content')
<div class="space-y-4 max-w-4xl mx-auto">

    {{-- Filter --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div class="min-w-44">
                <label class="form-label">Customer</label>
                <select name="customer" class="form-input">
                    <option value="">Semua Customer</option>
                    @foreach($customers as $c)
                    <option value="{{ $c }}" {{ request('customer') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(request()->hasAny(['customer','from','to']))
            <a href="{{ route('orders.history') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    {{-- Results --}}
    <div class="space-y-2">
        @forelse($orders as $order)
        <a href="{{ route('orders.show', $order) }}" class="card block p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-2 h-10 rounded-full flex-shrink-0
                    {{ $order->status === 'produksi' ? 'bg-blue-400' : '' }}
                    {{ $order->status === 'selesai'  ? 'bg-green-400' : '' }}
                    {{ $order->status === 'kirim'    ? 'bg-violet-400' : '' }}
                    {{ $order->status === 'batal'    ? 'bg-gray-300' : '' }}
                    {{ $order->status === 'draft'    ? 'bg-yellow-400' : '' }}
                "></div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="font-mono font-700 text-sm text-gray-900">{{ $order->nomor_spk }}</span>
                        <span class="badge badge-{{ $order->status }}">{{ $order->status_label }}</span>
                    </div>
                    <div class="text-sm font-500 text-gray-700">{{ $order->nama_barang }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        {{ $order->nama_customer }} &middot; {{ $order->tanggal_pesan->format('d M Y') }}
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <div class="text-sm font-700 text-gray-700">{{ $order->progress_percent }}%</div>
                    <div class="text-xs text-gray-400">{{ $order->processes->count() }} proses</div>
                </div>
            </div>
        </a>
        @empty
        <div class="card p-12 text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-gray-500">Tidak ada riwayat ditemukan</p>
        </div>
        @endforelse
    </div>

    {{ $orders->withQueryString()->links() }}
</div>
@endsection
