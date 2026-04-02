@extends('layouts.app')
@section('title', 'Daftar SPK')
@section('subtitle', 'Surat Perintah Kerja Produksi')

@section('header-actions')
    @if(auth()->user()->isPpic())
    <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        <span class="hidden sm:inline">Buat SPK</span>
    </a>
    @endif
@endsection

@section('content')
<div class="space-y-4 max-w-5xl mx-auto">

    {{-- Filter --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div class="flex-1 min-w-36">
                <label class="form-label">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nomor SPK, customer, barang..." class="form-input">
            </div>
            <div class="min-w-32">
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="">Semua</option>
                    <option value="draft"    {{ request('status')=='draft'    ? 'selected' : '' }}>Draft</option>
                    <option value="produksi" {{ request('status')=='produksi' ? 'selected' : '' }}>Produksi</option>
                    <option value="selesai"  {{ request('status')=='selesai'  ? 'selected' : '' }}>Selesai</option>
                    <option value="kirim"    {{ request('status')=='kirim'    ? 'selected' : '' }}>Dikirim</option>
                    <option value="batal"    {{ request('status')=='batal'    ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            @if($teams->count() > 1)
            <div class="min-w-36">
                <label class="form-label">Tim</label>
                <select name="team_id" class="form-input">
                    <option value="">Semua Tim</option>
                    @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <button type="submit" class="btn btn-primary">Cari</button>
            @if(request()->hasAny(['search','status','team_id']))
            <a href="{{ route('orders.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    {{-- 2 kolom: General | Corporate --}}
    @php
        $generalOrders   = $orders->filter(fn($o) => $o->tipe === 'general');
        $corporateOrders = $orders->filter(fn($o) => $o->tipe === 'corporate');
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- GENERAL --}}
        <div class="space-y-2">
            <div class="flex items-center gap-2 px-1">
                <div class="w-2.5 h-2.5 rounded-full bg-gray-400"></div>
                <h3 class="font-700 text-gray-600 text-sm uppercase tracking-wider">General</h3>
                <span class="text-xs text-gray-400">{{ $generalOrders->count() }} SPK</span>
            </div>

            @forelse($generalOrders as $order)
            @include('orders._card', ['order' => $order])
            @empty
            <div class="card p-8 text-center">
                <p class="text-gray-400 text-sm">Tidak ada SPK general</p>
            </div>
            @endforelse
        </div>

        {{-- CORPORATE --}}
        <div class="space-y-2">
            <div class="flex items-center gap-2 px-1">
                <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                <h3 class="font-700 text-amber-600 text-sm uppercase tracking-wider">Corporate</h3>
                <span class="text-xs text-gray-400">{{ $corporateOrders->count() }} SPK</span>
                @if($corporateOrders->count() > 0)
                <span class="text-[10px] bg-amber-100 text-amber-700 border border-amber-300 px-1.5 py-0.5 rounded-md font-600">Diutamakan</span>
                @endif
            </div>

            @forelse($corporateOrders as $order)
            @include('orders._card', ['order' => $order])
            @empty
            <div class="card p-8 text-center border-2 border-dashed border-amber-200">
                <p class="text-amber-400 text-sm">Tidak ada SPK corporate</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    <div>{{ $orders->links() }}</div>
</div>
@endsection
