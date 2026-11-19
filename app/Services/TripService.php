<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Reservation;
use App\Models\ReservationSeat;
use App\Models\Seat;
use App\Models\Trip;

class TripService
{
    public function __construct(private ReservationService $reservations)
    {
    }

    // kursi_terisi = jumlah baris reservation_seats pada trip tersebut
    private function base()
    {
        return Trip::with(['route', 'bus'])->withCount('reservationSeats as kursi_terisi');
    }

    public function find(int $id): Trip
    {
        return $this->base()->find($id)
            ?? throw new ApiException('Data tidak ditemukan.', 404, 'NOT_FOUND');
    }

    /** Pencarian customer: hanya trip aktif yang belum berangkat. */
    public function search(array $f, int $perPage)
    {
        $this->reservations->expireStale();

        return $this->base()
            ->whereHas('route', fn ($q) => $q->where('status', 'Active')
                ->where('asal', $f['asal'])->where('tujuan', $f['tujuan']))
            ->whereHas('bus', function ($q) use ($f) {
                $q->where('status', 'Active');
                if (! empty($f['tipe_bus'])) {
                    $q->where('tipe_bus', $f['tipe_bus']);
                }
            })
            ->where('tanggal_berangkat', $f['tanggal'])
            ->whereRaw('TIMESTAMP(trips.tanggal_berangkat, trips.jam_berangkat) > ?', [now()->format('Y-m-d H:i:s')])
            ->when(! empty($f['jumlah_kursi']), fn ($q) => $q->whereRaw(
                '(select kapasitas from buses where buses.id = trips.bus_id)
                 - (select count(*) from reservation_seats where reservation_seats.trip_id = trips.id) >= ?',
                [$f['jumlah_kursi']]
            ))
            ->orderBy('jam_berangkat')
            ->paginate($perPage);
    }

    /** Detail trip + peta kursi (available / taken). */
    public function detail(int $id): array
    {
        $this->reservations->expireStale();
        $trip = $this->find($id);

        $taken = ReservationSeat::where('trip_id', $trip->id)->pluck('seat_id')->flip();
        $seats = Seat::where('bus_id', $trip->bus_id)->get()
            ->sortBy(fn ($s) => sprintf('%03d%s', (int) $s->seat_number, substr($s->seat_number, -1)))
            ->map(fn ($s) => [
                'id'          => $s->id,
                'seat_number' => $s->seat_number,
                'status'      => $taken->has($s->id) ? 'taken' : 'available',
            ])->values()->all();

        return ['trip' => $trip, 'seats' => $seats];
    }

    public function adminList(array $f, int $perPage)
    {
        return $this->base()
            ->when($f['route_id'] ?? null, fn ($q, $v) => $q->where('route_id', $v))
            ->when($f['bus_id'] ?? null, fn ($q, $v) => $q->where('bus_id', $v))
            ->when($f['tanggal_dari'] ?? null, fn ($q, $v) => $q->where('tanggal_berangkat', '>=', $v))
            ->when($f['tanggal_sampai'] ?? null, fn ($q, $v) => $q->where('tanggal_berangkat', '<=', $v))
            ->orderByDesc('tanggal_berangkat')->orderBy('jam_berangkat')
            ->paginate($perPage);
    }

    public function create(array $d): Trip
    {
        $this->assertNoConflict((int) $d['bus_id'], $d['tanggal_berangkat']);
        $trip = Trip::create($d);

        return $this->find($trip->id);
    }

    public function update(Trip $trip, array $d): Trip
    {
        // bus, rute, tanggal, jam terkunci bila sudah ada reservasi aktif
        $changed = (int) $trip->bus_id !== (int) $d['bus_id']
            || (int) $trip->route_id !== (int) $d['route_id']
            || $trip->tanggal_berangkat !== $d['tanggal_berangkat']
            || substr($trip->jam_berangkat, 0, 5) !== $d['jam_berangkat'];

        if ($changed) {
            $hasActive = Reservation::where('trip_id', $trip->id)->where('status', '!=', 'cancelled')->exists();
            if ($hasActive) {
                throw new ApiException('Trip sudah memiliki reservasi.', 409, 'TRIP_HAS_RESERVATIONS');
            }
            $this->assertNoConflict((int) $d['bus_id'], $d['tanggal_berangkat'], $trip->id);
        }

        $trip->update($d); // harga boleh diubah kapan saja (reservasi lama sudah snapshot)

        return $this->find($trip->id);
    }

    public function delete(Trip $trip): void
    {
        if (Reservation::where('trip_id', $trip->id)->exists()) {
            throw new ApiException('Trip sudah memiliki reservasi.', 409, 'TRIP_HAS_RESERVATIONS');
        }
        $trip->delete();
    }

    // Aturan sederhana: satu bus hanya boleh punya satu trip per tanggal
    private function assertNoConflict(int $busId, string $tanggal, ?int $ignoreId = null): void
    {
        $exists = Trip::where('bus_id', $busId)
            ->where('tanggal_berangkat', $tanggal)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw new ApiException('Bus sudah memiliki trip pada tanggal tersebut.', 422, 'SCHEDULE_CONFLICT');
        }
    }
}
