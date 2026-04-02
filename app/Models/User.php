<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'nama_proses', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Relations ─────────────────────────────────────────────

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'user_teams');
    }

    public function orders()
    {
        return $this->hasMany(ProductionOrder::class, 'created_by');
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class);
    }

    // ── Role helpers ──────────────────────────────────────────

    public function isMasterAdmin(): bool { return $this->role === 'master_admin'; }
    public function isPpic(): bool        { return $this->role === 'ppic'; }
    public function isKoor(): bool        { return $this->role === 'koor'; }
    public function isOperator(): bool    { return $this->role === 'operator'; }

    // Legacy helpers
    public function isMarketing(): bool  { return $this->isPpic() || $this->isMasterAdmin(); }
    public function isProduksi(): bool   { return $this->isKoor() || $this->isOperator(); }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'master_admin' => 'Master Admin',
            'ppic'         => 'PPIC',
            'koor'         => 'Koordinator',
            'operator'     => 'Operator' . ($this->nama_proses ? ' ' . $this->nama_proses : ''),
            default        => ucfirst($this->role),
        };
    }

    // ── Team helpers ──────────────────────────────────────────

    public function teamIds(): array
    {
        return $this->teams->pluck('id')->toArray();
    }

    public function canAccessTeam(int $teamId): bool
    {
        if ($this->isMasterAdmin()) return true;
        return in_array($teamId, $this->teamIds());
    }

    /**
     * Operator hanya bisa input proses yang namanya sama dengan nama_proses mereka.
     */
    public function canInputProcess(ProductionProcess $process): bool
    {
        if ($this->isKoor()) return true; // Koor bisa semua proses di timnya
        if ($this->isOperator()) {
            return strtolower(trim($this->nama_proses)) === strtolower(trim($process->nama_proses));
        }
        return false;
    }
}
