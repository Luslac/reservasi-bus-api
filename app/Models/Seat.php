<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    public $timestamps = false;

    protected $fillable = ['bus_id', 'seat_number'];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function reservationSeats(): HasMany
    {
        return $this->hasMany(ReservationSeat::class);
    }
}
