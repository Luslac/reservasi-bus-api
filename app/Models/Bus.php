<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    public $timestamps = false;

    protected $fillable = ['plat_nomor', 'kapasitas', 'tipe_bus', 'status'];

    protected $casts = ['created_at' => 'datetime'];

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
