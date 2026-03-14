<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];
    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function isMarketing(): bool
    {
        return $this->role === 'marketing';
    }

    public function isProduksi(): bool
    {
        return $this->role === 'produksi';
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'created_by');
    }
}
