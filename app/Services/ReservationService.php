<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Reservation;
use App\Models\ReservationSeat;
use App\Models\Seat;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    /** Batalkan reservasi pending yang melewati masa tahan dan bebaskan kursinya. */
    public function expireStale(): void
    {
        DB::transaction(function () {
            $ids = Reservation::where('status', 'pending')
                ->whereRaw('created_at < (NOW() - INTERVAL ? MINUTE)', [config('booking.hold_minutes')])
                ->lockForUpdate()
                ->pluck('id');

            if ($ids->isEmpty()) {
                return;
            }

            ReservationSeat::whereIn('reservation_id', $ids)->delete();
            Reservation::whereIn('id', $ids)->update(['status' => 'cancelled']);
        });
    }

    /** Detail satu reservasi (userId null = admin, boleh lihat semua). */
    public function find(int $id, ?int $userId = null): Reservation
    {
        $this->expireStale();

        return $this->load($id, $userId);
    }

    /** Daftar reservasi + filter. userId null = admin. */
    public function paginate(array $f, ?int $userId, int $perPage)
    {
        $this->expireStale();

        return Reservation::withHold()
            ->with(['trip.route', 'trip.bus', 'seats.seat'])
            ->when($userId === null, fn ($q) => $q->with('user'))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($f['payment_status'] ?? null, fn ($q, $v) => $q->where('payment_status', $v))
            ->when($f['trip_id'] ?? null, fn ($q, $v) => $q->where('trip_id', $v))
            ->when($f['tanggal'] ?? null, fn ($q, $v) => $q->whereHas('trip', fn ($t) => $t->where('tanggal_berangkat', $v)))
            ->when($f['q'] ?? null, fn ($q, $v) => $q->whereHas('user', fn ($u) => $u
                ->where('name', 'like', "%{$v}%")
                ->orWhere('email', 'like', "%{$v}%")))
            ->orderByDesc('reservations.id')
            ->paginate($perPage);
    }

    /**
     * @param  array<int, array{seat_id:int, nama_penumpang:string}>  $passengers
     */
    public function book(User $user, int $tripId, array $passengers): Reservation
    {
        $this->expireStale();

        $reservationId = DB::transaction(function () use ($user, $tripId, $passengers) {
            // Kunci baris trip: pemesanan pada trip yang sama diproses berurutan
            $trip = Trip::lockForUpdate()->find($tripId);
            if (! $trip) {
                throw $this->notFound();
            }
            $trip->load(['bus', 'route']);

            if ($trip->bus->status !== 'Active' || $trip->route->status !== 'Active') {
                throw new ApiException('Trip tidak tersedia.', 422, 'TRIP_NOT_AVAILABLE');
            }
            if (Carbon::parse($trip->tanggal_berangkat . ' ' . $trip->jam_berangkat)->isPast()) {
                throw new ApiException('Trip sudah berangkat.', 422, 'TRIP_DEPARTED');
            }

            $pending = Reservation::where('user_id', $user->id)->where('status', 'pending')->count();
            if ($pending >= config('booking.max_pending')) {
                throw new ApiException('Terlalu banyak reservasi yang belum dibayar.', 422, 'TOO_MANY_PENDING');
            }

            // Semua kursi harus milik bus trip ini
            $seatIds = array_column($passengers, 'seat_id');
            $seats = Seat::whereIn('id', $seatIds)->get()->keyBy('id');
            foreach ($seatIds as $seatId) {
                if (! isset($seats[$seatId]) || (int) $seats[$seatId]->bus_id !== (int) $trip->bus_id) {
                    throw new ApiException('Kursi bukan milik bus trip ini.', 422, 'SEAT_NOT_IN_BUS');
                }
            }

            $taken = ReservationSeat::where('trip_id', $trip->id)->whereIn('seat_id', $seatIds)->pluck('seat_id');
            if ($taken->isNotEmpty()) {
                throw new ApiException('Kursi sudah dipesan orang lain.', 409, 'SEAT_TAKEN', [
                    'seat_numbers' => $seats->only($taken->all())->pluck('seat_number')->values()->all(),
                ]);
            }

            $reservation = Reservation::create([
                'user_id'        => $user->id,
                'trip_id'        => $trip->id,
                'status'         => 'pending',
                'payment_status' => 'unpaid',
                'total_harga'    => number_format($trip->harga * count($passengers), 2, '.', ''), // snapshot harga
            ]);

            try {
                foreach ($passengers as $p) {
                    ReservationSeat::create([
                        'reservation_id' => $reservation->id,
                        'trip_id'        => $trip->id,
                        'seat_id'        => $p['seat_id'],
                        'nama_penumpang' => $p['nama_penumpang'],
                    ]);
                }
            } catch (QueryException $e) {
                // Jaring pengaman: UNIQUE (trip_id, seat_id) di database (MySQL error 1062)
                if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                    throw new ApiException('Kursi sudah dipesan orang lain.', 409, 'SEAT_TAKEN');
                }
                throw $e;
            }

            return $reservation->id;
        });

        return $this->load($reservationId, $user->id);
    }

    public function pay(User $user, int $id): Reservation
    {
        $r = $this->load($id, $user->id);

        // Dicek di luar transaksi supaya pembatalan otomatis tidak ikut di-rollback
        if ($r->status === 'pending' && (int) $r->hold_left <= 0) {
            $this->expireStale();
            throw new ApiException('Waktu pembayaran habis.', 409, 'RESERVATION_EXPIRED');
        }

        DB::transaction(function () use ($id, $user) {
            $row = Reservation::where('id', $id)->where('user_id', $user->id)->lockForUpdate()->first();
            if (! $row || $row->status !== 'pending') {
                throw new ApiException('Reservasi tidak dalam status pending.', 409, 'INVALID_STATE');
            }
            $row->update(['status' => 'confirmed', 'payment_status' => 'paid']); // simulasi, tanpa gateway
        });

        return $this->load($id, $user->id);
    }

    /** Admin: $user = null dan $byAdmin = true. */
    public function cancel(?User $user, int $id, bool $byAdmin = false): Reservation
    {
        $userId = $byAdmin ? null : $user->id;
        $this->load($id, $userId); // memastikan ada dan milik user (404 jika tidak)

        DB::transaction(function () use ($id, $userId, $byAdmin) {
            $row = Reservation::with('trip')->where('id', $id)
                ->when($userId, fn ($q) => $q->where('user_id', $userId))
                ->lockForUpdate()->first();

            if ($row->status === 'cancelled') {
                throw new ApiException('Reservasi sudah dibatalkan.', 409, 'INVALID_STATE');
            }
            $departed = Carbon::parse($row->trip->tanggal_berangkat . ' ' . $row->trip->jam_berangkat)->isPast();
            if ($departed && ! $byAdmin) {
                throw new ApiException('Trip sudah berangkat.', 422, 'TRIP_DEPARTED');
            }

            // Sudah dibayar -> refund 100% (simulasi)
            $refund = $row->payment_status === 'paid';

            // WAJIB: hapus baris kursi supaya UNIQUE (trip_id, seat_id) tidak menahan kursi
            ReservationSeat::where('reservation_id', $row->id)->delete();

            $row->update([
                'status'         => 'cancelled',
                'payment_status' => $refund ? 'refunded' : 'unpaid',
            ]);
        });

        return $this->load($id, $userId);
    }

    private function load(int $id, ?int $userId): Reservation
    {
        $reservation = Reservation::withHold()
            ->with(['trip.route', 'trip.bus', 'seats.seat'])
            ->when($userId === null, fn ($q) => $q->with('user'))
            ->where('reservations.id', $id)
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->first();

        return $reservation ?? throw $this->notFound();
    }

    private function notFound(): ApiException
    {
        return new ApiException('Data tidak ditemukan.', 404, 'NOT_FOUND');
    }
}
