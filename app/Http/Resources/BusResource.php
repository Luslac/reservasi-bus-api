<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'plat_nomor' => $this->plat_nomor,
            'kapasitas'  => (int) $this->kapasitas,
            'tipe_bus'   => $this->tipe_bus,
            'status'     => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'seats'      => $this->whenLoaded('seats', fn () => $this->seats
                ->sortBy(fn ($s) => sprintf('%03d%s', (int) $s->seat_number, substr($s->seat_number, -1)))
                ->map(fn ($s) => ['id' => $s->id, 'seat_number' => $s->seat_number])->values()),
        ];
    }
}
