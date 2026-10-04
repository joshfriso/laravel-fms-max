<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** @var array<int, string> Kolom yang boleh diisi. */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /** @var array<int, string> Kolom yang disembunyikan. */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @return array<string, string> Tipe data otomatis. */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdministrator(): bool
    {
        return $this->role === 'administrator';
    }
}
