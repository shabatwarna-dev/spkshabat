<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor_spk', 'tanggal_pesan', 'tanggal_produksi',
        'tanggal_selesai_estimasi', 'tanggal_selesai_aktual',
        'tanggal_kirim', 'nama_customer', 'nama_barang',
        'keterangan', 'status', 'created_by',
    ];

    protected $casts = [
        'tanggal_pesan' => 'date',
        'tanggal_produksi' => 'date',
        'tanggal_selesai_estimasi' => 'date',
        'tanggal_selesai_aktual' => 'date',
        'tanggal_kirim' => 'date',
    ];

    public function processes()
    {
        return $this->hasMany(ProductionProcess::class)->orderBy('urutan');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'badge-warning',
            'produksi' => 'badge-info',
            'selesai'  => 'badge-success',
            'kirim'    => 'badge-primary',
            'batal'    => 'badge-danger',
            default    => 'badge-secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'Draft',
            'produksi' => 'Produksi',
            'selesai'  => 'Selesai',
            'kirim'    => 'Dikirim',
            'batal'    => 'Dibatalkan',
            default    => 'Unknown',
        };
    }

    public function getProgressPercentAttribute(): int
    {
        $total = $this->processes->count();
        if ($total === 0) return 0;
        $done = $this->processes->where('status', 'selesai')->count();
        return (int) round(($done / $total) * 100);
    }

    public function hasLateProcesses(): bool
    {
        return $this->processes->contains('status', 'telat');
    }

    // Auto-generate SPK number
    public static function generateNomorSPK(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $last = static::whereYear('created_at', $year)
                       ->whereMonth('created_at', $month)
                       ->count() + 1;
        return 'SPK-' . $year . $month . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}
