<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Bus;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;

class BusService
{
    /** Buat bus + kursi otomatis (tata letak 2-2: A, B | C, D). */
    public function create(array $d): Bus
    {
        return DB::transaction(function () use ($d) {
            $bus = Bus::create([
                'plat_nomor' => $d['plat_nomor'],
                'tipe_bus'   => $d['tipe_bus'],
                'kapasitas'  => $d['jumlah_baris'] * 4,
                'status'     => $d['status'] ?? 'Active',
            ]);

            $rows = [];
            for ($baris = 1; $baris <= $d['jumlah_baris']; $baris++) {
                foreach (['A', 'B', 'C', 'D'] as $huruf) {
                    $rows[] = ['bus_id' => $bus->id, 'seat_number' => $baris . $huruf];
                }
            }
            Seat::insert($rows);

            return $bus->refresh();
        });
    }

    public function delete(Bus $bus): void
    {
        if ($bus->trips()->exists()) {
            throw new ApiException('Bus masih dipakai oleh trip.', 409, 'HAS_DEPENDENCIES');
        }
        $bus->delete(); // kursi ikut terhapus (ON DELETE CASCADE)
    }
}
