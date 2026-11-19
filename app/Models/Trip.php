<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    public $timestamps = false;

    // tanggal_berangkat sengaja TIDAK di-cast ke date: tetap string 'Y-m-d'
    protected $fillable = ['bus_id', 'route_id', 'tanggal_berangkat', 'jam_berangkat', 'harga'];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(BusRoute::class, 'route_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function reservationSeats(): HasMany
    {
        return $this->hasMany(ReservationSeat::class);
    }
}
