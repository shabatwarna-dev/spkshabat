<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk &mdash; SPK Shabat</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .form-input {
            width: 100%; padding: 10px 13px;
            border: 1.5px solid #d1d5db; border-radius: 8px;
            font-size: 14px; font-family: 'Inter', sans-serif;
            outline: none; transition: border-color .15s, box-shadow .15s;
            color: #111827;
        }
        .form-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    </style>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4" style="background: linear-gradient(135deg, #1a1f2e 0%, #252b3b 50%, #1a1f2e 100%);">

    <div class="w-full max-w-sm">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl shadow-blue-900/40">
                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-white text-2xl font-bold tracking-tight">SPK Shabat</h1>
            <p class="text-slate-400 text-sm mt-1">Sistem Manajemen Produksi Percetakan</p>
        </div>

        {{-- Login Card --}}
        <div class="bg-white rounded-2xl shadow-2xl shadow-black/30 overflow-hidden">
            <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-700"></div>
            <div class="p-7">
                <h2 class="text-gray-900 font-bold text-lg mb-1">Masuk ke Sistem</h2>
                <p class="text-gray-500 text-sm mb-6">Gunakan akun yang telah diberikan</p>

                @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-5">
                    <p class="text-red-700 text-sm font-500">{{ $errors->first() }}</p>
                </div>
                @endif

                <form action="/login" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-600 text-gray-500 uppercase tracking-wider mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="form-input" placeholder="email@perusahaan.com" required autofocus>
                    </div>
                    <div>
                        <label class="block text-xs font-600 text-gray-500 uppercase tracking-wider mb-1.5">Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Kata sandi" required>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="remember" name="remember"
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 accent-blue-600">
                        <label for="remember" class="text-sm text-gray-600">Ingat saya</label>
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm mt-2">
                        Masuk
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-slate-500 text-xs mt-6">&copy; {{ date('Y') }} SPK Shabat &mdash; Sistem Internal</p>
    </div>
</body>
</html>
