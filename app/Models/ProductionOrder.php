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
        'keterangan', 'status', 'tipe', 'team_id', 'created_by',
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

    public function getTipeLabelAttribute(): string
    {
        return match($this->tipe) {
            'corporate' => 'Corporate',
            default     => 'General',
        };
    }

    public function isCorporate(): bool
    {
        return $this->tipe === 'corporate';
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

    public function scopeForUser($query, User $user)
    {
        if ($user->isMasterAdmin()) return $query;

        // Operator hanya lihat SPK yang ada proses dengan nama yang dia handle
        if ($user->isOperator() && $user->nama_proses) {
            return $query->whereIn('team_id', $user->teamIds())
                         ->whereHas('processes', function ($q) use ($user) {
                             $q->where('nama_proses', $user->nama_proses);
                         });
        }

        $teamIds = $user->teamIds();
        if (empty($teamIds)) return $query->whereRaw('1 = 0');

        return $query->whereIn('team_id', $teamIds);
    }

    // Corporate selalu di atas dalam ordering
    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw("FIELD(tipe, 'corporate', 'general')")
                     ->orderByDesc('created_at');
    }

    // ── Static ────────────────────────────────────────────────

    public static function generateNomorSPK(Team $team, string $tipe = 'general'): string
    {
        $year   = now()->format('Y');
        $month  = now()->format('m');
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $team->name), 0, 3));
        $tipePrefix = $tipe === 'corporate' ? 'C' : 'G';
        $last   = static::whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->where('team_id', $team->id)
                        ->where('tipe', $tipe)
                        ->count() + 1;

        return 'SPK-' . $prefix . $tipePrefix . '-' . $year . $month . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}
