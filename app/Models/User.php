<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    // created_at / updated_at diisi oleh MySQL (lihat Final.sql)
    public $timestamps = false;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password'];

    protected $casts = ['password' => 'hashed'];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
