<div class="card hover:shadow-md transition-all duration-150 {{ $order->isCorporate() ? 'border-l-4 border-l-amber-400' : '' }}">
    <a href="{{ route('orders.show', $order) }}" class="block p-3.5">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-1">
                <div class="w-2.5 h-2.5 rounded-full
                    {{ $order->status === 'produksi' ? 'bg-blue-500' : '' }}
                    {{ $order->status === 'selesai'  ? 'bg-green-500' : '' }}
                    {{ $order->status === 'kirim'    ? 'bg-violet-500' : '' }}
                    {{ $order->status === 'batal'    ? 'bg-gray-400' : '' }}
                    {{ $order->status === 'draft'    ? 'bg-yellow-500' : '' }}
                "></div>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                    <span class="font-mono font-700 text-xs text-gray-900">{{ $order->nomor_spk }}</span>
                    <span class="badge badge-{{ $order->status }} text-[10px]">{{ $order->status_label }}</span>
                    @if($order->isCorporate())
                    <span class="badge text-[10px]" style="background:#fef3c7;color:#92400e;border:1px solid #fcd34d;">CORP</span>
                    @endif
                    @if($order->hasLateProcesses())
                    <span class="badge badge-telat text-[10px]">TELAT</span>
                    @endif
                </div>
                <div class="text-xs font-600 text-gray-700 truncate">{{ $order->nama_barang }}</div>
                <div class="text-[11px] text-gray-400 mt-0.5">{{ $order->nama_customer }}</div>
                <div class="mt-2 flex items-center gap-2">
                    <div class="progress-track flex-1">
                        <div class="progress-fill {{ $order->hasLateProcesses() ? 'danger' : '' }}"
                             style="width:{{ $order->progress_percent }}%"></div>
                    </div>
                    <span class="text-[11px] font-600 text-gray-400 flex-shrink-0">{{ $order->progress_percent }}%</span>
                </div>
            </div>

            <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>

    {{-- Process chips --}}
    @if($order->processes->count() > 0)
    <div class="px-3.5 pb-3 flex flex-wrap gap-1 border-t border-gray-50">
        @foreach($order->processes as $proc)
        <span class="text-[10px] px-2 py-0.5 rounded font-500 border
            {{ $proc->status === 'pending' ? 'chip-pending' : '' }}
            {{ $proc->status === 'proses'  ? 'chip-proses' : '' }}
            {{ $proc->status === 'selesai' ? 'chip-selesai' : '' }}
            {{ $proc->status === 'telat'   ? 'chip-telat' : '' }}
        ">{{ $proc->nama_proses }}</span>
        @endforeach
    </div>
    @endif
</div>
