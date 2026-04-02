@extends('layouts.app')
@section('title', isset($user) ? 'Edit Akun' : 'Buat Akun Baru')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="card p-5">
        <div class="section-header">
            <div class="section-accent bg-blue-500"></div>
            <h3 class="font-700 text-gray-800">{{ isset($user) ? 'Edit Akun' : 'Akun Baru' }}</h3>
        </div>

        <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
            @csrf
            @if(isset($user)) @method('PUT') @endif

            <div class="space-y-4">
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}"
                           class="form-input" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                           class="form-input" required>
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">
                        Password {{ isset($user) ? '(kosongkan jika tidak diganti)' : '' }}
                        @if(!isset($user))<span class="text-red-500">*</span>@endif
                    </label>
                    <input type="password" name="password" class="form-input"
                           placeholder="{{ isset($user) ? 'Isi untuk mengganti password...' : 'Min. 8 karakter' }}"
                           {{ !isset($user) ? 'required' : '' }}>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div x-data="{ role: '{{ old('role', $user->role ?? 'koor') }}' }">
                    <label class="form-label">Role <span class="text-red-500">*</span></label>
                    <select name="role" x-model="role" class="form-input" required>
                        <option value="ppic">PPIC (buat & kelola SPK)</option>
                        <option value="koor">Koordinator (input semua proses tim)</option>
                        <option value="operator">Operator (input proses tertentu saja)</option>
                        <option value="master_admin">Master Admin (akses penuh)</option>
                    </select>

                    {{-- Nama proses khusus operator --}}
                    <div x-show="role === 'operator'" x-cloak class="mt-3">
                        <label class="form-label">Nama Proses yang Dihandle <span class="text-red-500">*</span></label>
                        <select name="nama_proses" class="form-input" :required="role === 'operator'">
                            <option value="">Pilih proses...</option>
                            @foreach(\App\Models\ProductionProcess::defaultProcesses() as $p)
                            <option value="{{ $p }}" {{ old('nama_proses', $user->nama_proses ?? '') === $p ? 'selected' : '' }}>
                                {{ $p }}
                            </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">
                            Operator ini hanya bisa input hasil untuk proses dengan nama tersebut.
                        </p>
                    </div>

                    {{-- Pilih tim --}}
                    <div x-show="role !== 'master_admin'" x-cloak class="mt-4">
                        <label class="form-label">Assign ke Tim</label>
                        <p class="text-xs text-gray-400 mb-2">User bisa masuk ke beberapa tim sekaligus.</p>
                        @php $selectedIds = old('team_ids', $userTeamIds ?? []); @endphp
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            @forelse($teams as $team)
                            <label class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0">
                                <input type="checkbox" name="team_ids[]" value="{{ $team->id }}"
                                       {{ in_array($team->id, $selectedIds) ? 'checked' : '' }}
                                       class="w-4 h-4 accent-blue-600 flex-shrink-0">
                                <div class="w-5 h-5 rounded flex items-center justify-center text-white text-[10px] font-700 flex-shrink-0"
                                     style="background: {{ $team->warna }}">
                                    {{ strtoupper(substr($team->name, 0, 2)) }}
                                </div>
                                <div class="flex-1">
                                    <span class="text-sm text-gray-700">{{ $team->name }}</span>
                                    <span class="badge badge-{{ $team->jalur }} text-[10px] ml-1.5">{{ $team->jalur_label }}</span>
                                </div>
                            </label>
                            @empty
                            <div class="px-3 py-4 text-center text-sm text-gray-400">
                                Belum ada tim. <a href="{{ route('admin.teams.create') }}" class="text-blue-600">Buat tim dulu.</a>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                @if(isset($user))
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 accent-blue-600">
                    <label for="is_active" class="text-sm text-gray-700">Akun aktif</label>
                </div>
                @endif
            </div>

            <div class="flex gap-3 justify-end mt-5 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($user) ? 'Simpan Perubahan' : 'Buat Akun' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
