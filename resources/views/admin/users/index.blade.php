@extends('layouts.app')
@section('title', 'Kelola Akun')
@section('subtitle', 'Manajemen user dan hak akses')

@section('header-actions')
<a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
    <span class="hidden sm:inline">Buat Akun</span>
</a>
@endsection

@section('content')
<div class="space-y-4 max-w-3xl mx-auto">

    {{-- Filter --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div class="flex-1 min-w-36">
                <label class="form-label">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nama atau email..." class="form-input">
            </div>
            <div class="min-w-32">
                <label class="form-label">Role</label>
                <select name="role" class="form-input">
                    <option value="">Semua Role</option>
                    <option value="ppic"         {{ request('role') === 'ppic'         ? 'selected' : '' }}>PPIC</option>
                    <option value="koor"         {{ request('role') === 'koor'         ? 'selected' : '' }}>Koordinator</option>
                    <option value="master_admin" {{ request('role') === 'master_admin' ? 'selected' : '' }}>Master Admin</option>
                </select>
            </div>
            <div class="min-w-36">
                <label class="form-label">Tim</label>
                <select name="team_id" class="form-input">
                    <option value="">Semua Tim</option>
                    @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
            @if(request()->hasAny(['search','role','team_id']))
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    <p class="text-xs text-gray-400">{{ $users->total() }} akun ditemukan</p>

    {{-- Card list — responsive untuk semua ukuran layar --}}
    <div class="space-y-2">
        @forelse($users as $user)
        <div class="card p-4">
            <div class="flex items-start gap-3">

                {{-- Avatar --}}
                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 text-sm font-700 flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="font-700 text-gray-800 text-sm">{{ $user->name }}</span>
                        <span class="badge text-[10px]
                            {{ $user->isMasterAdmin() ? 'badge-master' : '' }}
                            {{ $user->isPpic() ? 'badge-ppic' : '' }}
                            {{ $user->isKoor() ? 'badge-koor' : '' }}
                        ">{{ $user->role_label }}</span>
                        <span class="text-[10px] font-600 px-2 py-0.5 rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-400 mb-2">{{ $user->email }}</p>

                    {{-- Tim --}}
                    @if($user->teams->count() > 0)
                    <div class="flex flex-wrap gap-1">
                        @foreach($user->teams as $team)
                        <span class="text-[10px] px-2 py-0.5 rounded-md font-600"
                              style="background: {{ $team->warna }}20; color: {{ $team->warna }}; border: 1px solid {{ $team->warna }}40;">
                            {{ $team->name }}
                        </span>
                        @endforeach
                    </div>
                    @else
                    <span class="text-xs text-gray-300 italic">Belum ada tim</span>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex gap-1.5 flex-shrink-0">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-xs">Edit</a>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                          onsubmit="return confirm('Hapus akun {{ $user->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="card p-12 text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <p class="text-gray-500 text-sm">Tidak ada akun ditemukan</p>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary mt-3 inline-flex">Buat Akun</a>
        </div>
        @endforelse
    </div>

    <div>{{ $users->withQueryString()->links() }}</div>
</div>
@endsection