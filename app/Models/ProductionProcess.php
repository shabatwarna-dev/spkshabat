<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductionProcess extends Model
{
    protected $fillable = [
        'production_order_id', 'nama_proses', 'urutan',
        'estimasi_selesai', 'jumlah_barang', 'montage',
        'ukuran', 'warna', 'estimasi_hasil', 'satuan',
        'catatan_marketing', 'hasil_jadi', 'jumlah_reject',
        'tanggal_selesai_aktual', 'catatan_produksi', 'foto_hasil', 'status',
        'is_edited', 'edit_count', 'first_input_at', 'input_by',
    ];

    protected $casts = [
        'estimasi_selesai'       => 'date',
        'tanggal_selesai_aktual' => 'date',
        'first_input_at'         => 'datetime',
        'jumlah_barang'          => 'decimal:2',
        'estimasi_hasil'         => 'decimal:2',
        'hasil_jadi'             => 'decimal:2',
        'jumlah_reject'          => 'decimal:2',
        'is_edited'              => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function editLogs()
    {
        return $this->hasMany(ProcessEditLog::class, 'production_process_id')->latest();
    }

    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    public function isTelat(): bool
    {
        if (in_array($this->status, ['selesai', 'telat']) && $this->tanggal_selesai_aktual && $this->estimasi_selesai) {
            return $this->tanggal_selesai_aktual->gt($this->estimasi_selesai);
        }
        if (!in_array($this->status, ['selesai', 'telat']) && $this->estimasi_selesai) {
            return Carbon::today()->gt($this->estimasi_selesai);
        }
        return false;
    }

    public function sudahDiInput(): bool
    {
        return !is_null($this->hasil_jadi) || !is_null($this->tanggal_selesai_aktual);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-gray-100 text-gray-600',
            'proses'  => 'bg-blue-100 text-blue-700',
            'selesai' => 'bg-green-100 text-green-700',
            'telat'   => 'bg-red-100 text-red-700',
            default   => 'bg-gray-100 text-gray-600',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'proses'  => 'Proses',
            'selesai' => 'Selesai',
            'telat'   => 'TELAT',
            default   => '-',
        };
    }

    public static function defaultProcesses(): array
    {
        return [
            'Design', 'Plat', 'Pisau Punch', 'Potong Bahan',
            'Cetak', 'Laminasi', 'Punch', 'Dedel', 'Sortir', 'Packing'
        ];
    }
}
