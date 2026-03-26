<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'nomor_spk', 'tanggal_pesan', 'tanggal_produksi',
        'tanggal_selesai_estimasi', 'tanggal_selesai_aktual',
        'tanggal_kirim', 'nama_customer', 'nama_barang',
        'keterangan', 'status', 'team_id', 'created_by',
    ];

    protected $casts = [
        'tanggal_pesan'            => 'date',
        'tanggal_produksi'         => 'date',
        'tanggal_selesai_estimasi' => 'date',
        'tanggal_selesai_aktual'   => 'date',
        'tanggal_kirim'            => 'date',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function processes()
    {
        return $this->hasMany(ProductionProcess::class)->orderBy('urutan');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // ── Accessors ─────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'Draft',
            'produksi' => 'Produksi',
            'selesai'  => 'Selesai',
            'kirim'    => 'Dikirim',
            'batal'    => 'Dibatalkan',
            default    => ucfirst($this->status),
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

    // ── Scopes ────────────────────────────────────────────────

    /**
     * Filter SPK berdasarkan tim user.
     * Master admin tidak difilter — lihat semua.
     * PPIC & Koor hanya lihat SPK dari tim mereka.
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->isMasterAdmin()) {
            return $query;
        }

        $teamIds = $user->teamIds();

        if (empty($teamIds)) {
            // User tidak punya tim — tidak bisa lihat apapun
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('team_id', $teamIds);
    }

    // ── Static ────────────────────────────────────────────────

    public static function generateNomorSPK(Team $team): string
    {
        $year   = now()->format('Y');
        $month  = now()->format('m');
        // Ambil 3 huruf pertama nama tim, uppercase
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $team->name), 0, 3));
        $last   = static::whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->where('team_id', $team->id)
                        ->count() + 1;

        return 'SPK-' . $prefix . '-' . $year . $month . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}