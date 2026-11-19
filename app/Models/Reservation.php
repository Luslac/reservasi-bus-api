<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'trip_id', 'status', 'total_harga', 'payment_status'];

    protected $casts = ['created_at' => 'datetime'];

    // Menambah kolom hold_left (sisa detik masa tahan), dihitung oleh MySQL
    public function scopeWithHold(Builder $query): Builder
    {
        return $query->select('reservations.*')->selectRaw(
            'GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(reservations.created_at, INTERVAL ? MINUTE))) as hold_left',
            [config('booking.hold_minutes')]
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(ReservationSeat::class);
    }
}
