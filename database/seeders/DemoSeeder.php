<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\BusRoute;
use App\Models\Trip;
use App\Models\User;
use App\Services\BusService;
use Illuminate\Database\Seeder;

// Data demo: akun (password: "password"), 4 bus, 4 rute, trip 14 hari ke depan
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['email' => 'admin@example.com'], ['name' => 'Admin', 'password' => 'password', 'role' => 'admin']);
        User::firstOrCreate(['email' => 'customer@example.com'], ['name' => 'Customer', 'password' => 'password', 'role' => 'customer']);

        $busService = app(BusService::class);
        $buses = [];
        foreach ([['B 1111 AA', 'Ekonomi', 8], ['B 2222 BB', 'Bisnis', 8], ['B 3333 CC', 'Executive', 8], ['B 4444 DD', 'VIP', 6]] as [$plat, $tipe, $baris]) {
            $buses[] = Bus::where('plat_nomor', $plat)->first()
                ?? $busService->create(['plat_nomor' => $plat, 'tipe_bus' => $tipe, 'jumlah_baris' => $baris]);
        }

        $routes = [];
        foreach ([['Jakarta', 'Bandung', 120000, '08:00'], ['Jakarta', 'Yogyakarta', 250000, '10:30'],
                  ['Bandung', 'Surabaya', 300000, '13:00'], ['Jakarta', 'Surabaya', 350000, '20:00']] as [$asal, $tujuan, $harga, $jam]) {
            $routes[] = [BusRoute::firstOrCreate(['asal' => $asal, 'tujuan' => $tujuan], ['status' => 'Active']), $harga, $jam];
        }

        for ($hari = 1; $hari <= 14; $hari++) {
            $tanggal = now()->addDays($hari)->format('Y-m-d');
            foreach ($routes as $i => [$route, $harga, $jam]) {
                // satu bus per rute per hari (sesuai aturan satu trip per bus per tanggal)
                Trip::firstOrCreate(
                    ['bus_id' => $buses[$i]->id, 'tanggal_berangkat' => $tanggal],
                    ['route_id' => $route->id, 'jam_berangkat' => $jam, 'harga' => $harga]
                );
            }
        }
    }
}
