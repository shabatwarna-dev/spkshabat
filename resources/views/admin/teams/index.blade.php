@extends('layouts.app')
@section('title', 'Kelola Tim')
@section('subtitle', 'Atur tim produksi dan anggotanya')

@section('header-actions')
<a href="{{ route('admin.teams.create') }}" class="btn btn-primary btn-sm">
    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
    Buat Tim
</a>
@endsection

@section('content')
<div class="space-y-3 max-w-4xl mx-auto">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @forelse($teams as $team)
        <div class="card overflow-hidden">
            <div class="h-1.5" style="background: {{ $team->warna }}"></div>
            <div class="p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white font-700 text-sm flex-shrink-0"
                             style="background: {{ $team->warna }}">
                            {{ strtoupper(substr($team->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="font-700 text-gray-800 text-sm">{{ $team->name }}</div>
                            <span class="badge badge-{{ $team->jalur }} text-[10px] mt-0.5">{{ $team->jalur_label }}</span>
                        </div>
                    </div>
                    <div class="flex gap-1.5 flex-shrink-0">
                        <a href="{{ route('admin.teams.edit', $team) }}" class="btn btn-secondary btn-xs">Edit</a>
                        <form action="{{ route('admin.teams.destroy', $team) }}" method="POST"
                              onsubmit="return confirm('Hapus tim {{ $team->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                        </form>
                    </div>
                </div>

                @if($team->keterangan)
                <p class="text-xs text-gray-400 mb-3">{{ $team->keterangan }}</p>
                @endif

                <div class="flex gap-4 mb-3">
                    <div class="text-center">
                        <div class="text-lg font-700 text-gray-800">{{ $team->orders_count }}</div>
                        <div class="text-[10px] text-gray-400">Total SPK</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-700 text-gray-800">{{ $team->users_count }}</div>
                        <div class="text-[10px] text-gray-400">Anggota</div>
                    </div>
                    <div class="text-center">
                        <div class="text-[10px] font-600 mt-1 px-2 py-0.5 rounded-full {{ $team->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $team->is_active ? 'Aktif' : 'Nonaktif' }}
                        </div>
                    </div>
                </div>

                {{-- Anggota --}}
                @if($team->users->count() > 0)
                <div class="border-t border-gray-100 pt-3">
                    <p class="text-[10px] font-700 text-gray-400 uppercase tracking-wider mb-2">Anggota</p>
                    <div class="space-y-1.5">
                        @foreach($team->users as $member)
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-700 flex items-center justify-center flex-shrink-0">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            <span class="text-xs text-gray-700 flex-1">{{ $member->name }}</span>
                            <span class="badge {{ $member->isPpic() ? 'badge-ppic' : 'badge-koor' }} text-[10px] px-1.5 py-0.5">
                                {{ $member->role_label }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs text-gray-400 italic">Belum ada anggota</p>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="card p-12 text-center col-span-full">
            <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-gray-500 text-sm">Belum ada tim. Buat tim pertama sekarang.</p>
            <a href="{{ route('admin.teams.create') }}" class="btn btn-primary mt-3 inline-flex">Buat Tim</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
