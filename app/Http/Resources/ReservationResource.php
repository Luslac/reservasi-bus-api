<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pending = $this->status === 'pending';
        $left = $pending ? (int) $this->hold_left : null; // hold_left dari scope withHold()
        $trip = $this->trip;

        return [
            'id'                => $this->id,
            'status'            => $this->status,
            'payment_status'    => $this->payment_status,
            'total_harga'       => number_format((float) $this->total_harga, 2, '.', ''),
            'jumlah_kursi'      => $this->seats->count(),
            'created_at'        => $this->created_at->toIso8601String(),
            'expires_at'        => $pending ? now()->addSeconds($left)->toIso8601String() : null,
            'hold_seconds_left' => $left,
            // hanya ada di response admin (relasi user dimuat)
            'user'              => $this->whenLoaded('user', fn () => (new UserResource($this->user))->resolve()),
            'trip'              => [
                'id'                => $trip->id,
                'tanggal_berangkat' => $trip->tanggal_berangkat,
                'jam_berangkat'     => substr($trip->jam_berangkat, 0, 5),
                'harga'             => number_format((float) $trip->harga, 2, '.', ''),
                'route'             => ['id' => $trip->route->id, 'asal' => $trip->route->asal, 'tujuan' => $trip->route->tujuan],
                'bus'               => ['id' => $trip->bus->id, 'tipe_bus' => $trip->bus->tipe_bus, 'kapasitas' => (int) $trip->bus->kapasitas],
            ],
            'passengers'        => $this->seats->map(fn ($s) => [
                'seat_id'        => $s->seat_id,
                'seat_number'    => $s->seat->seat_number,
                'nama_penumpang' => $s->nama_penumpang,
            ])->values(),
        ];
    }
}
