@extends('layouts.app')
@section('title', isset($team) ? 'Edit Tim' : 'Buat Tim Baru')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="card p-5">
        <div class="section-header">
            <div class="section-accent bg-blue-500"></div>
            <h3 class="font-700 text-gray-800">{{ isset($team) ? 'Edit Tim' : 'Tim Baru' }}</h3>
        </div>

        <form action="{{ isset($team) ? route('admin.teams.update', $team) : route('admin.teams.store') }}" method="POST">
            @csrf
            @if(isset($team)) @method('PUT') @endif

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="form-label">Nama Tim <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $team->name ?? '') }}"
                               class="form-input" placeholder="Tim Digital / Tim Offset / Tim Finishing" required>
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Jalur <span class="text-red-500">*</span></label>
                        <select name="jalur" class="form-input" required>
                            <option value="digital" {{ old('jalur', $team->jalur ?? '') === 'digital' ? 'selected' : '' }}>Digital</option>
                            <option value="offset"  {{ old('jalur', $team->jalur ?? '') === 'offset'  ? 'selected' : '' }}>Offset</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Warna Tim</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="warna" value="{{ old('warna', $team->warna ?? '#3b82f6') }}"
                                   class="h-9 w-16 rounded-lg border border-gray-300 cursor-pointer p-1">
                            <span class="text-xs text-gray-400">Warna identitas tim</span>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" value="{{ old('keterangan', $team->keterangan ?? '') }}"
                               class="form-input" placeholder="Deskripsi singkat tim...">
                    </div>
                    @if(isset($team))
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $team->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 accent-blue-600">
                        <label for="is_active" class="text-sm text-gray-700">Tim aktif</label>
                    </div>
                    @endif
                </div>

                {{-- Pilih anggota --}}
                <div>
                    <label class="form-label">Anggota Tim</label>
                    <p class="text-xs text-gray-400 mb-3">Pilih PPIC dan Koor yang masuk tim ini. Satu user bisa masuk ke beberapa tim.</p>
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        @php
                            $ppicUsers = $users->where('role', 'ppic');
                            $koorUsers = $users->where('role', 'koor');
                            $selectedIds = old('user_ids', $teamUserIds ?? []);
                        @endphp

                        @if($ppicUsers->count() > 0)
                        <div class="px-3 py-2 bg-blue-50 border-b border-gray-200">
                            <p class="text-[10px] font-700 text-blue-700 uppercase tracking-wider">PPIC</p>
                        </div>
                        @foreach($ppicUsers as $u)
                        <label class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0">
                            <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                                   {{ in_array($u->id, $selectedIds) ? 'checked' : '' }}
                                   class="w-4 h-4 accent-blue-600 flex-shrink-0">
                            <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-700 flex items-center justify-center flex-shrink-0">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <span class="text-sm text-gray-700 flex-1">{{ $u->name }}</span>
                            <span class="text-xs text-gray-400">{{ $u->email }}</span>
                        </label>
                        @endforeach
                        @endif

                        @if($koorUsers->count() > 0)
                        <div class="px-3 py-2 bg-green-50 border-b border-gray-200 {{ $ppicUsers->count() > 0 ? 'border-t border-gray-200' : '' }}">
                            <p class="text-[10px] font-700 text-green-700 uppercase tracking-wider">Koordinator</p>
                        </div>
                        @foreach($koorUsers as $u)
                        <label class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0">
                            <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                                   {{ in_array($u->id, $selectedIds) ? 'checked' : '' }}
                                   class="w-4 h-4 accent-green-600 flex-shrink-0">
                            <div class="w-6 h-6 rounded-full bg-green-100 text-green-700 text-xs font-700 flex items-center justify-center flex-shrink-0">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <span class="text-sm text-gray-700 flex-1">{{ $u->name }}</span>
                            <span class="text-xs text-gray-400">{{ $u->email }}</span>
                        </label>
                        @endforeach
                        @endif

                        @if($users->count() === 0)
                        <div class="px-3 py-6 text-center text-sm text-gray-400">Belum ada user PPIC atau Koor. Buat akun dulu.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex gap-3 justify-end mt-5 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.teams.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($team) ? 'Simpan Perubahan' : 'Buat Tim' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
