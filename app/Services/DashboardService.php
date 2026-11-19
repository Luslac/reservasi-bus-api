<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function summary(): array
    {
        $per = Reservation::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');
        $perStatus = [
            'pending'   => (int) ($per['pending'] ?? 0),
            'confirmed' => (int) ($per['confirmed'] ?? 0),
            'cancelled' => (int) ($per['cancelled'] ?? 0),
        ];

        // Pendapatan hanya dari reservasi yang berstatus paid (refunded tidak dihitung)
        $perRute = DB::table('reservations')
            ->join('trips', 'trips.id', '=', 'reservations.trip_id')
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->where('reservations.payment_status', 'paid')
            ->groupBy('routes.id', 'routes.asal', 'routes.tujuan') // per id rute, bukan per nama kota
            ->select('routes.id as route_id', 'routes.asal', 'routes.tujuan', DB::raw('SUM(reservations.total_harga) as pendapatan'))
            ->get()
            ->map(fn ($r) => [
                'route_id'   => (int) $r->route_id,
                'asal'       => $r->asal,
                'tujuan'     => $r->tujuan,
                'pendapatan' => number_format((float) $r->pendapatan, 2, '.', ''),
            ])->values()->all();

        return [
            'total_reservasi'     => array_sum($perStatus),
            'reservasi_pending'   => $perStatus['pending'],
            'pendapatan'          => number_format((float) Reservation::where('payment_status', 'paid')->sum('total_harga'), 2, '.', ''),
            'trip_mendatang'      => Trip::whereRaw('TIMESTAMP(tanggal_berangkat, jam_berangkat) > ?', [now()->format('Y-m-d H:i:s')])->count(),
            'per_status'          => $perStatus,
            'pendapatan_per_rute' => $perRute,
        ];
    }
}
