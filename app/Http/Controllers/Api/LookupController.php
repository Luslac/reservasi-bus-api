<?php

namespace App\Http\Controllers\Api;

use App\Models\Bus;
use App\Models\BusRoute;

// Data publik untuk form pencarian dan halaman info
class LookupController extends ApiController
{
    public function locations()
    {
        $aktif = BusRoute::where('status', 'Active');

        return response()->json(['data' => [
            'asal'   => (clone $aktif)->distinct()->orderBy('asal')->pluck('asal'),
            'tujuan' => (clone $aktif)->distinct()->orderBy('tujuan')->pluck('tujuan'),
        ]]);
    }

    public function busTypes()
    {
        $data = Bus::where('status', 'Active')
            ->selectRaw('tipe_bus, count(*) as jumlah_bus, max(kapasitas) as kapasitas')
            ->groupBy('tipe_bus')->orderBy('tipe_bus')->get()
            ->map(fn ($b) => [
                'tipe_bus'   => $b->tipe_bus,
                'jumlah_bus' => (int) $b->jumlah_bus,
                'kapasitas'  => (int) $b->kapasitas,
            ]);

        return response()->json(['data' => $data]);
    }

    public function routes()
    {
        $now = now()->format('Y-m-d H:i:s');

        $data = BusRoute::where('status', 'Active')
            ->withMin(['trips as harga_mulai' => fn ($q) => $q
                ->whereRaw('TIMESTAMP(trips.tanggal_berangkat, trips.jam_berangkat) > ?', [$now])], 'harga')
            ->orderBy('asal')->get()
            ->map(fn ($r) => [
                'id'          => $r->id,
                'asal'        => $r->asal,
                'tujuan'      => $r->tujuan,
                'harga_mulai' => $r->harga_mulai !== null ? number_format((float) $r->harga_mulai, 2, '.', '') : null,
            ]);

        return response()->json(['data' => $data]);
    }
}
