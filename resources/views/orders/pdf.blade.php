<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK {{ $order->nomor_spk }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; margin: 24px 28px; }
        .header { border-bottom: 2px solid #0ea5e9; padding-bottom: 12px; margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: 800; color: #0ea5e9; }
        .subtitle { font-size: 11px; color: #64748b; }
        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
        .info-item label { display: block; font-size: 8px; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; font-weight: 700; margin-bottom: 2px; }
        .info-item span { font-size: 10px; font-weight: 600; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f172a; color: white; text-align: left; padding: 6px 8px; font-size: 8px; text-transform: uppercase; letter-spacing: .05em; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; font-size: 9px; vertical-align: top; }
        tr:nth-child(even) td { background: #f8fafc; }
        .status { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 8px; font-weight: 700; }
        .status-selesai { background: #d1fae5; color: #065f46; }
        .status-telat { background: #fee2e2; color: #991b1b; }
        .status-proses { background: #dbeafe; color: #1e40af; }
        .status-pending { background: #f1f5f9; color: #64748b; }
        .footer { margin-top: 20px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 8px; color: #94a3b8; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <div class="header">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <div class="title">SURAT PERINTAH KERJA</div>
                <div class="subtitle">SPK Shabat – Sistem Manajemen Produksi Percetakan</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:14px; font-weight:800; font-family:monospace;">{{ $order->nomor_spk }}</div>
                <div class="status status-{{ $order->status }}" style="margin-top:4px;">{{ strtoupper($order->status_label) }}</div>
            </div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <label>Customer</label>
            <span>{{ $order->nama_customer }}</span>
        </div>
        <div class="info-item">
            <label>Nama Barang</label>
            <span>{{ $order->nama_barang }}</span>
        </div>
        <div class="info-item">
            <label>Tanggal Pesan</label>
            <span>{{ $order->tanggal_pesan->format('d M Y') }}</span>
        </div>
        @if($order->tanggal_produksi)
        <div class="info-item">
            <label>Mulai Produksi</label>
            <span>{{ $order->tanggal_produksi->format('d M Y') }}</span>
        </div>
        @endif
        @if($order->tanggal_selesai_estimasi)
        <div class="info-item">
            <label>Est. Selesai</label>
            <span>{{ $order->tanggal_selesai_estimasi->format('d M Y') }}</span>
        </div>
        @endif
        @if($order->tanggal_kirim)
        <div class="info-item">
            <label>Tanggal Kirim</label>
            <span>{{ $order->tanggal_kirim->format('d M Y') }}</span>
        </div>
        @endif
        <div class="info-item">
            <label>Dibuat oleh</label>
            <span>{{ $order->creator->name }}</span>
        </div>
        <div class="info-item">
            <label>Tanggal Cetak</label>
            <span>{{ now()->format('d M Y H:i') }}</span>
        </div>
    </div>

    @if($order->keterangan)
    <div style="background:#f8fafc; border-left:3px solid #0ea5e9; padding:8px 12px; margin-bottom:16px; border-radius:0 4px 4px 0;">
        <strong style="font-size:8px; color:#64748b; text-transform:uppercase;">Keterangan:</strong>
        <div style="margin-top:3px;">{{ $order->keterangan }}</div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Proses</th>
                <th>Est. Selesai</th>
                <th>Jml Barang</th>
                <th>Montage</th>
                <th>Ukuran</th>
                <th>Warna</th>
                <th>Est. Hasil</th>
                <th>Satuan</th>
                <th>Hasil Jadi</th>
                <th>Reject</th>
                <th>Tgl Selesai</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->processes as $proc)
            <tr>
                <td>{{ $proc->urutan }}</td>
                <td><strong>{{ $proc->nama_proses }}</strong>
                    @if($proc->catatan_marketing)<br><span style="color:#94a3b8;font-size:8px;">{{ $proc->catatan_marketing }}</span>@endif
                </td>
                <td>{{ $proc->estimasi_selesai?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $proc->jumlah_barang ? number_format($proc->jumlah_barang,0,',','.') : '-' }}</td>
                <td>{{ $proc->montage ?? '-' }}</td>
                <td>{{ $proc->ukuran ?? '-' }}</td>
                <td>{{ $proc->warna ?? '-' }}</td>
                <td>{{ $proc->estimasi_hasil ? number_format($proc->estimasi_hasil,0,',','.') : '-' }}</td>
                <td>{{ $proc->satuan ?? '-' }}</td>
                <td><strong>{{ $proc->hasil_jadi ? number_format($proc->hasil_jadi,0,',','.') : '-' }}</strong></td>
                <td>{{ $proc->jumlah_reject ? number_format($proc->jumlah_reject,0,',','.') : '-' }}</td>
                <td>{{ $proc->tanggal_selesai_aktual?->format('d/m/Y') ?? '-' }}</td>
                <td><span class="status status-{{ $proc->status }}">{{ strtoupper($proc->status_label) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:30px; display:grid; grid-template-columns: repeat(3, 1fr); gap:20px;">
        <div style="text-align:center;">
            <div style="border-top:1px solid #cbd5e1; margin-top:40px; padding-top:6px; font-size:9px; color:#64748b;">Admin Marketing</div>
            <div style="font-size:9px; font-weight:600;">{{ $order->creator->name }}</div>
        </div>
        <div style="text-align:center;">
            <div style="border-top:1px solid #cbd5e1; margin-top:40px; padding-top:6px; font-size:9px; color:#64748b;">Admin Produksi</div>
        </div>
        <div style="text-align:center;">
            <div style="border-top:1px solid #cbd5e1; margin-top:40px; padding-top:6px; font-size:9px; color:#64748b;">Kepala Produksi</div>
        </div>
    </div>

    <div class="footer">
        <span>Dokumen ini digenerate oleh sistem SPK Shabat pada {{ now()->format('d M Y H:i:s') }}</span>
        <span>{{ $order->nomor_spk }}</span>
    </div>
</body>
</html>