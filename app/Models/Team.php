<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'jalur', 'warna', 'keterangan', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_teams');
    }

    public function orders()
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function ppicUsers()
    {
        return $this->users()->where('role', 'ppic');
    }

    public function koorUsers()
    {
        return $this->users()->where('role', 'koor');
    }

    // ── Accessors ─────────────────────────────────────────────

    public function getJalurLabelAttribute(): string
    {
        return match($this->jalur) {
            'digital' => 'Digital',
            'offset'  => 'Offset',
            default   => '-',
        };
    }

    public function getJalurColorAttribute(): string
    {
        return match($this->jalur) {
            'digital' => 'blue',
            'offset'  => 'orange',
            default   => 'gray',
        };
    }
}
