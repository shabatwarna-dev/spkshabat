<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductionProcess extends Model
{
    protected $fillable = [
        'production_order_id', 'nama_proses', 'urutan',
        'estimasi_selesai', 'jumlah_barang', 'montage', 'ukuran', 'warna',
        'estimasi_hasil', 'satuan', 'catatan_marketing',
        'hasil_jadi', 'jumlah_reject', 'tanggal_selesai_aktual',
        'catatan_produksi', 'foto_hasil', 'status',
        'is_edited', 'edit_count', 'first_input_at', 'input_by',
    ];

    protected $casts = [
        'estimasi_selesai'      => 'datetime', // datetime bukan date
        'tanggal_selesai_aktual'=> 'datetime',
        'first_input_at'        => 'datetime',
        'is_edited'             => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function order()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    public function editLogs()
    {
        return $this->hasMany(ProcessEditLog::class)->orderByDesc('created_at');
    }

    // ── Accessors ─────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'proses'  => 'On Process',
            'selesai' => 'Selesai',
            'telat'   => 'Terlambat',
            default   => ucfirst($this->status),
        };
    }

    /**
     * Cek apakah proses ini terlambat berdasarkan estimasi datetime.
     */
    public function isTelat(): bool
    {
        if (!$this->estimasi_selesai) return false;
        if (!$this->tanggal_selesai_aktual) {
            return now()->gt($this->estimasi_selesai);
        }
        return $this->tanggal_selesai_aktual->gt($this->estimasi_selesai);
    }

    public function sudahDiInput(): bool
    {
        return !is_null($this->hasil_jadi);
    }

    // ── Static ────────────────────────────────────────────────

    public static function defaultProcesses(): array
    {
        return [
            'Design', 'Plat', 'Pisau Punch', 'Potong Bahan',
            'Cetak', 'Laminasi', 'Punch', 'Dedel', 'Sortir', 'Packing',
        ];
    }
}
