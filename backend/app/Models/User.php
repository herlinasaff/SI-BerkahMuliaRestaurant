<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'username',
        'email',
        'password',
        'api_token',
        'phone',
        'address',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token', // disembunyikan agar hash token tidak bocor di JSON
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // Role Helper Methods
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'admin';
    }

    public function isCashier(): bool
    {
        return $this->role && $this->role->name === 'kasir';
    }

    public function isOwner(): bool
    {
        return $this->role && $this->role->name === 'pemilik';
    }
}
