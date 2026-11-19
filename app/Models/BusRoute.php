<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Tabel `routes`. Dinamai BusRoute agar tidak bentrok dengan facade Route.
class BusRoute extends Model
{
    protected $table = 'routes';

    public $timestamps = false;

    protected $fillable = ['asal', 'tujuan', 'status'];

    protected $casts = ['created_at' => 'datetime'];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'route_id');
    }
}
