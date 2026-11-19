<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Dipakai customer (pencarian/detail) dan admin; data admin ditambah kursi_terisi + plat_nomor
class TripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $admin = $request->is('api/v1/admin/*');
        $terisi = (int) ($this->kursi_terisi ?? 0);

        $bus = [
            'id'        => $this->bus->id,
            'tipe_bus'  => $this->bus->tipe_bus,
            'kapasitas' => (int) $this->bus->kapasitas,
        ];
        if ($admin) {
            $bus['plat_nomor'] = $this->bus->plat_nomor;
        }

        $data = [
            'id'                => $this->id,
            'tanggal_berangkat' => $this->tanggal_berangkat,
            'jam_berangkat'     => substr($this->jam_berangkat, 0, 5),
            'harga'             => number_format((float) $this->harga, 2, '.', ''),
            'kursi_tersedia'    => (int) $this->bus->kapasitas - $terisi,
        ];
        if ($admin) {
            $data['kursi_terisi'] = $terisi;
        }

        return $data + [
            'route' => ['id' => $this->route->id, 'asal' => $this->route->asal, 'tujuan' => $this->route->tujuan],
            'bus'   => $bus,
        ];
    }
}
