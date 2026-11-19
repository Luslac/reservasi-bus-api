# Struktur Folder Backend dan API Spec — Sistem Reservasi Bus

Base URL : `/api/v1`

Header untuk semua request yang membawa body : `Accept: application/json`, `Content-Type: application/json`

Header untuk endpoint yang butuh login : `Authorization: Bearer <token>`

---

# Struktur Folder Backend (Laravel)

```text
reservasi-bus-api/
├── app/
│   ├── Enums/
│   │   ├── ActiveStatus.php
│   │   ├── PaymentStatus.php
│   │   ├── ReservationStatus.php
│   │   └── UserRole.php
│   ├── Exceptions/
│   │   └── ApiException.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── Admin/
│   │   │       │   ├── BusController.php
│   │   │       │   ├── DashboardController.php
│   │   │       │   ├── ReservationController.php
│   │   │       │   ├── RouteController.php
│   │   │       │   └── TripController.php
│   │   │       ├── AuthController.php
│   │   │       ├── LookupController.php
│   │   │       ├── ReservationController.php
│   │   │       └── TripController.php
│   │   ├── Middleware/
│   │   │   └── EnsureAdmin.php
│   │   ├── Requests/
│   │   │   ├── Admin/
│   │   │   │   ├── StoreBusRequest.php
│   │   │   │   ├── StoreRouteRequest.php
│   │   │   │   ├── StoreTripRequest.php
│   │   │   │   ├── UpdateBusRequest.php
│   │   │   │   ├── UpdateRouteRequest.php
│   │   │   │   └── UpdateTripRequest.php
│   │   │   ├── LoginRequest.php
│   │   │   ├── RegisterRequest.php
│   │   │   ├── SearchTripRequest.php
│   │   │   └── StoreReservationRequest.php
│   │   └── Resources/
│   │       ├── BusResource.php
│   │       ├── ReservationResource.php
│   │       ├── RouteResource.php
│   │       ├── SeatResource.php
│   │       ├── TripResource.php
│   │       └── UserResource.php
│   ├── Models/
│   │   ├── Bus.php
│   │   ├── BusRoute.php
│   │   ├── Reservation.php
│   │   ├── ReservationSeat.php
│   │   ├── Seat.php
│   │   ├── Trip.php
│   │   └── User.php
│   └── Services/
│       ├── BusService.php
│       ├── DashboardService.php
│       └── ReservationService.php
├── bootstrap/
│   └── app.php
├── config/
│   ├── booking.php
│   ├── cors.php
│   └── database.php
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_users_table.php
│   │   ├── xxxx_create_buses_table.php
│   │   ├── xxxx_create_seats_table.php
│   │   ├── xxxx_create_routes_table.php
│   │   ├── xxxx_create_trips_table.php
│   │   ├── xxxx_create_reservations_table.php
│   │   ├── xxxx_create_reservation_seats_table.php
│   │   └── xxxx_create_personal_access_tokens_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── DemoSeeder.php
├── docs/
│   └── api-contract.md
├── routes/
│   └── api.php
├── tests/
│   └── Feature/
│       ├── AdminTest.php
│       ├── AuthTest.php
│       ├── BookingTest.php
│       └── SearchTripTest.php
├── .env
└── composer.json
```

---

# Format Bersama

## Format Error

Semua error memakai bentuk ini. Frontend menentukan perilaku dari `code`, bukan dari `message`.

```json
{
  "message" : "Kursi sudah dipesan orang lain.",
  "code"    : "SEAT_TAKEN"
}
```

Error validasi (HTTP 422) menambah field `errors`:

```json
{
  "message" : "Data tidak valid.",
  "code"    : "VALIDATION_ERROR",
  "errors"  : {
    "email" : ["Email sudah terdaftar."]
  }
}
```

Error umum yang bisa muncul di endpoint yang butuh login:

```json
{
  "message" : "Silakan login terlebih dahulu.",
  "code"    : "UNAUTHENTICATED"
}
```

```json
{
  "message" : "Akses ditolak.",
  "code"    : "FORBIDDEN"
}
```

`UNAUTHENTICATED` (401) muncul di semua endpoint login. `FORBIDDEN` (403) muncul di semua endpoint `/admin/*` bila dipanggil customer. Keduanya tidak diulang di tiap endpoint di bawah.

## Format Paginasi

Semua endpoint daftar menerima `?page=1&per_page=10` (default 10, maksimal 50) dan mengembalikan:

```json
{
  "data" : [],
  "meta" : {
    "current_page" : 1,
    "last_page"    : 3,
    "per_page"     : 10,
    "total"        : 27
  }
}
```

## Objek User

```json
{
  "id"    : 5,
  "name"  : "ren",
  "email" : "ren@example.com",
  "role"  : "customer"
}
```

## Objek Trip (hasil pencarian)

```json
{
  "id"                : 12,
  "tanggal_berangkat" : "2026-11-20",
  "jam_berangkat"     : "08:30",
  "harga"             : "150000.00",
  "kursi_tersedia"    : 28,
  "route" : {
    "id"     : 3,
    "asal"   : "Jakarta",
    "tujuan" : "Yogyakarta"
  },
  "bus" : {
    "id"        : 2,
    "tipe_bus"  : "Executive",
    "kapasitas" : 32
  }
}
```

## Objek Reservation

```json
{
  "id"                : 57,
  "status"            : "pending",
  "payment_status"    : "unpaid",
  "total_harga"       : "300000.00",
  "jumlah_kursi"      : 2,
  "created_at"        : "2026-11-05T10:15:00+07:00",
  "expires_at"        : "2026-11-05T10:30:00+07:00",
  "hold_seconds_left" : 900,
  "trip" : {
    "id"                : 12,
    "tanggal_berangkat" : "2026-11-20",
    "jam_berangkat"     : "08:30",
    "harga"             : "150000.00",
    "route" : { "id" : 3, "asal" : "Jakarta", "tujuan" : "Yogyakarta" },
    "bus"   : { "id" : 2, "tipe_bus" : "Executive", "kapasitas" : 32 }
  },
  "passengers" : [
    { "seat_id" : 101, "seat_number" : "1A", "nama_penumpang" : "ren" },
    { "seat_id" : 103, "seat_number" : "1C", "nama_penumpang" : "Sari" }
  ]
}
```

Catatan field : `expires_at` dan `hold_seconds_left` bernilai `null` bila status bukan `pending`. Pada reservasi `cancelled`, `passengers` bernilai `[]` dan `jumlah_kursi` bernilai `0`. Objek Reservation versi admin menambah field `user` (Objek User).

## Nilai Enum

| Field | Nilai |
|---|---|
| `role` | `customer`, `admin` |
| `buses.status`, `routes.status` | `Active`, `Inactive` |
| `reservations.status` | `pending`, `confirmed`, `cancelled` |
| `reservations.payment_status` | `unpaid`, `paid`, `refunded` |
| `seats[].status` (peta kursi) | `available`, `taken` |

Uang selalu berupa string desimal (`"150000.00"`). Tanggal `YYYY-MM-DD`. Jam `HH:MM`. Timestamp ISO 8601 dengan offset.

---

# Auth API

## Register User API

Endpoint : POST /api/v1/auth/register

Request Body :

```json
{
  "name"                  : "ren",
  "email"                 : "ren@example.com",
  "password"              : "rahasia123",
  "password_confirmation" : "rahasia123"
}
```

Aturan : `name` wajib maks 100. `email` wajib, format email, maks 150, unik. `password` wajib 8-72 karakter, sama dengan `password_confirmation`. Role selalu `customer`.

Response Body Success (201) :

```json
{
  "data" : {
    "token" : "1|AbCdEf123456",
    "user"  : {
      "id"    : 5,
      "name"  : "ren",
      "email" : "ren@example.com",
      "role"  : "customer"
    }
  }
}
```

Response Body Error (422) :

```json
{
  "message" : "Data tidak valid.",
  "code"    : "VALIDATION_ERROR",
  "errors"  : {
    "email" : ["Email sudah terdaftar."]
  }
}
```

## Login User API

Endpoint : POST /api/v1/auth/login

Request Body :

```json
{
  "email"    : "ren@example.com",
  "password" : "rahasia123"
}
```

Response Body Success (200) :

```json
{
  "data" : {
    "token" : "2|XyZ987654321",
    "user"  : {
      "id"    : 5,
      "name"  : "Ren",
      "email" : "ren@example.com",
      "role"  : "customer"
    }
  }
}
```

Response Body Error (401) :

```json
{
  "message" : "Email atau password salah.",
  "code"    : "INVALID_CREDENTIALS"
}
```

Catatan : `INVALID_CREDENTIALS` bukan tanda token kedaluwarsa, jadi frontend jangan logout paksa. Bila terlalu banyak percobaan, server mengembalikan 429.

## Logout User API

Endpoint : POST /api/v1/auth/logout

Headers : Authorization: Bearer <token>

Request Body : (kosong)

Response Body Success (204) : (tanpa body)

Response Body Error (401) :

```json
{
  "message" : "Silakan login terlebih dahulu.",
  "code"    : "UNAUTHENTICATED"
}
```

## Get Current User API

Endpoint : GET /api/v1/auth/me

Headers : Authorization: Bearer <token>

Response Body Success (200) :

```json
{
  "data" : {
    "id"    : 5,
    "name"  : "ren",
    "email" : "ren@example.com",
    "role"  : "customer"
  }
}
```

Response Body Error (401) :

```json
{
  "message" : "Silakan login terlebih dahulu.",
  "code"    : "UNAUTHENTICATED"
}
```

---

# Data Publik API

## Get Locations API

Endpoint : GET /api/v1/locations

Response Body Success (200) :

```json
{
  "data" : {
    "asal"   : ["Jakarta", "Bandung"],
    "tujuan" : ["Yogyakarta", "Surabaya"]
  }
}
```

Response Body Error : tidak ada error spesifik.

## Get Bus Types API

Endpoint : GET /api/v1/bus-types

Response Body Success (200) :

```json
{
  "data" : [
    {
      "tipe_bus"   : "Executive",
      "jumlah_bus" : 3,
      "kapasitas"  : 32
    }
  ]
}
```

Response Body Error : tidak ada error spesifik.

## Get Public Routes API

Endpoint : GET /api/v1/routes

Response Body Success (200) :

```json
{
  "data" : [
    {
      "id"          : 3,
      "asal"        : "Jakarta",
      "tujuan"      : "Yogyakarta",
      "harga_mulai" : "150000.00"
    }
  ]
}
```

Catatan : hanya rute `Active`. `harga_mulai` adalah harga terendah dari trip mendatang, `null` bila belum ada trip.

Response Body Error : tidak ada error spesifik.

---

# Trip API (Customer)

## Search Trips API

Endpoint : GET /api/v1/trips

Headers : Authorization: Bearer <token>

Query Params :

| Param | Wajib | Aturan |
|---|---|---|
| `asal` | ya | string, sesuai `/locations` |
| `tujuan` | ya | string |
| `tanggal` | ya | `YYYY-MM-DD`, tidak sebelum hari ini |
| `tipe_bus` | tidak | sesuai `/bus-types` |
| `jumlah_kursi` | tidak | integer 1-10, hanya trip dengan `kursi_tersedia` >= nilai ini |
| `page`, `per_page` | tidak | paginasi |

Contoh : `GET /api/v1/trips?asal=Jakarta&tujuan=Yogyakarta&tanggal=2026-11-20`

Response Body Success (200) :

```json
{
  "data" : [
    {
      "id"                : 12,
      "tanggal_berangkat" : "2026-11-20",
      "jam_berangkat"     : "08:30",
      "harga"             : "150000.00",
      "kursi_tersedia"    : 28,
      "route" : { "id" : 3, "asal" : "Jakarta", "tujuan" : "Yogyakarta" },
      "bus"   : { "id" : 2, "tipe_bus" : "Executive", "kapasitas" : 32 }
    }
  ],
  "meta" : { "current_page" : 1, "last_page" : 1, "per_page" : 10, "total" : 1 }
}
```

Response Body Error (422) :

```json
{
  "message" : "Data tidak valid.",
  "code"    : "VALIDATION_ERROR",
  "errors"  : {
    "tanggal" : ["Tanggal tidak boleh sebelum hari ini."]
  }
}
```

## Get Trip Detail API

Endpoint : GET /api/v1/trips/{id}

Headers : Authorization: Bearer <token>

Response Body Success (200) :

```json
{
  "data" : {
    "id"                : 12,
    "tanggal_berangkat" : "2026-11-20",
    "jam_berangkat"     : "08:30",
    "harga"             : "150000.00",
    "kursi_tersedia"    : 30,
    "route" : { "id" : 3, "asal" : "Jakarta", "tujuan" : "Yogyakarta" },
    "bus"   : { "id" : 2, "tipe_bus" : "Executive", "kapasitas" : 32 },
    "seats" : [
      { "id" : 101, "seat_number" : "1A", "status" : "available" },
      { "id" : 102, "seat_number" : "1B", "status" : "taken" }
    ]
  }
}
```

Catatan : `seats` urut baris lalu huruf. Format `seat_number` `<baris><A-D>`, tata letak 2-2 (A, B, lorong, C, D). `taken` mencakup kursi yang sedang ditahan reservasi pending siapa pun.

Response Body Error (404) :

```json
{
  "message" : "Data tidak ditemukan.",
  "code"    : "NOT_FOUND"
}
```

---

# Reservation API (Customer)

## Create Reservation API

Endpoint : POST /api/v1/reservations

Headers : Authorization: Bearer <token>

Request Body :

```json
{
  "trip_id"    : 12,
  "passengers" : [
    { "seat_id" : 101, "nama_penumpang" : "ren" },
    { "seat_id" : 103, "nama_penumpang" : "Sari" }
  ]
}
```

Aturan : `trip_id` wajib dan ada. `passengers` array 1-10 item. `seat_id` wajib, ada, tidak boleh ganda. `nama_penumpang` wajib maks 100.

Response Body Success (201) : Objek Reservation dengan `status` `pending`, `payment_status` `unpaid`, `total_harga` = harga trip x jumlah kursi, ditahan 15 menit.

```json
{
  "data" : {
    "id"                : 57,
    "status"            : "pending",
    "payment_status"    : "unpaid",
    "total_harga"       : "300000.00",
    "jumlah_kursi"      : 2,
    "created_at"        : "2026-11-05T10:15:00+07:00",
    "expires_at"        : "2026-11-05T10:30:00+07:00",
    "hold_seconds_left" : 900,
    "trip" : {
      "id"                : 12,
      "tanggal_berangkat" : "2026-11-20",
      "jam_berangkat"     : "08:30",
      "harga"             : "150000.00",
      "route" : { "id" : 3, "asal" : "Jakarta", "tujuan" : "Yogyakarta" },
      "bus"   : { "id" : 2, "tipe_bus" : "Executive", "kapasitas" : 32 }
    },
    "passengers" : [
      { "seat_id" : 101, "seat_number" : "1A", "nama_penumpang" : "ren" },
      { "seat_id" : 103, "seat_number" : "1C", "nama_penumpang" : "Sari" }
    ]
  }
}
```

Response Body Error :

409, kursi sudah dipesan :

```json
{
  "message"      : "Kursi sudah dipesan orang lain.",
  "code"         : "SEAT_TAKEN",
  "seat_numbers" : ["1A"]
}
```

422, kursi bukan milik bus trip ini :

```json
{
  "message" : "Kursi bukan milik bus trip ini.",
  "code"    : "SEAT_NOT_IN_BUS"
}
```

422, trip tidak tersedia (bus atau rute Inactive) :

```json
{
  "message" : "Trip tidak tersedia.",
  "code"    : "TRIP_NOT_AVAILABLE"
}
```

422, trip sudah berangkat :

```json
{
  "message" : "Trip sudah berangkat.",
  "code"    : "TRIP_DEPARTED"
}
```

422, reservasi pending sudah mencapai batas (3) :

```json
{
  "message" : "Terlalu banyak reservasi yang belum dibayar.",
  "code"    : "TOO_MANY_PENDING"
}
```

422, input tidak valid : format Error Validasi.

## Get My Reservations API

Endpoint : GET /api/v1/reservations

Headers : Authorization: Bearer <token>

Query Params : `status` (opsional: `pending`, `confirmed`, `cancelled`), `page`, `per_page`

Response Body Success (200) : daftar Objek Reservation, terbaru dulu.

```json
{
  "data" : [
    {
      "id"                : 57,
      "status"            : "confirmed",
      "payment_status"    : "paid",
      "total_harga"       : "300000.00",
      "jumlah_kursi"      : 2,
      "created_at"        : "2026-11-05T10:15:00+07:00",
      "expires_at"        : null,
      "hold_seconds_left" : null,
      "trip" : {
        "id"                : 12,
        "tanggal_berangkat" : "2026-11-20",
        "jam_berangkat"     : "08:30",
        "harga"             : "150000.00",
        "route" : { "id" : 3, "asal" : "Jakarta", "tujuan" : "Yogyakarta" },
        "bus"   : { "id" : 2, "tipe_bus" : "Executive", "kapasitas" : 32 }
      },
      "passengers" : [
        { "seat_id" : 101, "seat_number" : "1A", "nama_penumpang" : "ren" },
        { "seat_id" : 103, "seat_number" : "1C", "nama_penumpang" : "Sari" }
      ]
    }
  ],
  "meta" : { "current_page" : 1, "last_page" : 1, "per_page" : 10, "total" : 1 }
}
```

Response Body Error (422) : format Error Validasi (misalnya nilai `status` tidak dikenal).

## Get Reservation Detail API

Endpoint : GET /api/v1/reservations/{id}

Headers : Authorization: Bearer <token>

Response Body Success (200) : `{ "data" : <Objek Reservation> }`, bentuk sama dengan satu item di atas.

Response Body Error (404) : reservasi tidak ada atau milik user lain.

```json
{
  "message" : "Data tidak ditemukan.",
  "code"    : "NOT_FOUND"
}
```

## Pay Reservation API

Endpoint : POST /api/v1/reservations/{id}/pay

Headers : Authorization: Bearer <token>

Request Body : (kosong). Pembayaran hanya simulasi, tidak ada payment gateway.

Response Body Success (200) : Objek Reservation dengan `status` `confirmed`, `payment_status` `paid`, `expires_at` `null`, `hold_seconds_left` `null`.

```json
{
  "data" : {
    "id"                : 57,
    "status"            : "confirmed",
    "payment_status"    : "paid",
    "total_harga"       : "300000.00",
    "jumlah_kursi"      : 2,
    "created_at"        : "2026-11-05T10:15:00+07:00",
    "expires_at"        : null,
    "hold_seconds_left" : null,
    "trip" : {
      "id"                : 12,
      "tanggal_berangkat" : "2026-11-20",
      "jam_berangkat"     : "08:30",
      "harga"             : "150000.00",
      "route" : { "id" : 3, "asal" : "Jakarta", "tujuan" : "Yogyakarta" },
      "bus"   : { "id" : 2, "tipe_bus" : "Executive", "kapasitas" : 32 }
    },
    "passengers" : [
      { "seat_id" : 101, "seat_number" : "1A", "nama_penumpang" : "ren" },
      { "seat_id" : 103, "seat_number" : "1C", "nama_penumpang" : "Sari" }
    ]
  }
}
```

Response Body Error :

409, status bukan pending :

```json
{
  "message" : "Reservasi tidak dalam status pending.",
  "code"    : "INVALID_STATE"
}
```

409, masa hold habis :

```json
{
  "message" : "Waktu pembayaran habis.",
  "code"    : "RESERVATION_EXPIRED"
}
```

404, reservasi tidak ada atau milik user lain : `NOT_FOUND`.

## Cancel Reservation API

Endpoint : POST /api/v1/reservations/{id}/cancel

Headers : Authorization: Bearer <token>

Request Body : (kosong)

Aturan hasil :

| Kondisi sebelum | Hasil |
|---|---|
| `pending` / `unpaid` | `cancelled` / `unpaid` |
| `confirmed` / `paid` | `cancelled` / `refunded` (simulasi, 100%) |

Response Body Success (200) : Objek Reservation dengan status baru, `passengers` `[]`, `jumlah_kursi` `0`.

```json
{
  "data" : {
    "id"                : 57,
    "status"            : "cancelled",
    "payment_status"    : "refunded",
    "total_harga"       : "300000.00",
    "jumlah_kursi"      : 0,
    "created_at"        : "2026-11-05T10:15:00+07:00",
    "expires_at"        : null,
    "hold_seconds_left" : null,
    "trip" : {
      "id"                : 12,
      "tanggal_berangkat" : "2026-11-20",
      "jam_berangkat"     : "08:30",
      "harga"             : "150000.00",
      "route" : { "id" : 3, "asal" : "Jakarta", "tujuan" : "Yogyakarta" },
      "bus"   : { "id" : 2, "tipe_bus" : "Executive", "kapasitas" : 32 }
    },
    "passengers" : []
  }
}
```

Response Body Error :

409, sudah dibatalkan :

```json
{
  "message" : "Reservasi sudah dibatalkan.",
  "code"    : "INVALID_STATE"
}
```

422, trip sudah berangkat :

```json
{
  "message" : "Trip sudah berangkat.",
  "code"    : "TRIP_DEPARTED"
}
```

404, reservasi tidak ada atau milik user lain : `NOT_FOUND`.

---

# Admin API

Semua endpoint di bagian ini : Headers `Authorization: Bearer <token>` dengan role `admin`. Customer menerima 403 `FORBIDDEN`.

## Dashboard API

Endpoint : GET /api/v1/admin/dashboard

Response Body Success (200) :

```json
{
  "data" : {
    "total_reservasi"   : 120,
    "reservasi_pending" : 4,
    "pendapatan"        : "18450000.00",
    "trip_mendatang"    : 22,
    "per_status" : {
      "pending"   : 4,
      "confirmed" : 96,
      "cancelled" : 20
    },
    "pendapatan_per_rute" : [
      {
        "route_id"   : 3,
        "asal"       : "Jakarta",
        "tujuan"     : "Yogyakarta",
        "pendapatan" : "9000000.00"
      }
    ]
  }
}
```

Catatan : `pendapatan` = jumlah `total_harga` dengan `payment_status` `paid`. Dikelompokkan per `routes.id`.

Response Body Error : hanya `UNAUTHENTICATED` dan `FORBIDDEN`.

## Bus API

### List Buses

Endpoint : GET /api/v1/admin/buses

Query Params : `page`, `per_page`

Response Body Success (200) :

```json
{
  "data" : [
    {
      "id"         : 2,
      "plat_nomor" : "B 1234 XY",
      "kapasitas"  : 32,
      "tipe_bus"   : "Executive",
      "status"     : "Active",
      "created_at" : "2026-11-01T09:00:00+07:00"
    }
  ],
  "meta" : { "current_page" : 1, "last_page" : 1, "per_page" : 10, "total" : 1 }
}
```

### Create Bus

Endpoint : POST /api/v1/admin/buses

Request Body :

```json
{
  "plat_nomor"   : "B 1234 XY",
  "tipe_bus"     : "Executive",
  "jumlah_baris" : 8,
  "status"       : "Active"
}
```

Aturan : `plat_nomor` wajib maks 20 unik. `tipe_bus` wajib maks 50. `jumlah_baris` wajib integer 1-20; sistem membuat kursi `1A` sampai `{n}D`, sehingga `kapasitas` = `jumlah_baris` x 4. `status` opsional, default `Active`.

Response Body Success (201) :

```json
{
  "data" : {
    "id"         : 2,
    "plat_nomor" : "B 1234 XY",
    "kapasitas"  : 32,
    "tipe_bus"   : "Executive",
    "status"     : "Active",
    "created_at" : "2026-11-01T09:00:00+07:00"
  }
}
```

Response Body Error (422) :

```json
{
  "message" : "Data tidak valid.",
  "code"    : "VALIDATION_ERROR",
  "errors"  : {
    "plat_nomor" : ["Plat nomor sudah terdaftar."]
  }
}
```

### Get Bus Detail

Endpoint : GET /api/v1/admin/buses/{id}

Response Body Success (200) :

```json
{
  "data" : {
    "id"         : 2,
    "plat_nomor" : "B 1234 XY",
    "kapasitas"  : 32,
    "tipe_bus"   : "Executive",
    "status"     : "Active",
    "created_at" : "2026-11-01T09:00:00+07:00",
    "seats" : [
      { "id" : 101, "seat_number" : "1A" },
      { "id" : 102, "seat_number" : "1B" }
    ]
  }
}
```

Response Body Error (404) : `NOT_FOUND`.

### Update Bus

Endpoint : PUT /api/v1/admin/buses/{id}

Request Body :

```json
{
  "plat_nomor" : "B 1234 XY",
  "tipe_bus"   : "Executive",
  "status"     : "Inactive"
}
```

Catatan : `kapasitas` dan kursi tidak dapat diubah setelah bus dibuat.

Response Body Success (200) : `{ "data" : <objek bus> }` seperti Create Bus.

Response Body Error : 422 `VALIDATION_ERROR`, 404 `NOT_FOUND`.

### Delete Bus

Endpoint : DELETE /api/v1/admin/buses/{id}

Response Body Success (204) : (tanpa body). Kursi bus ikut terhapus.

Response Body Error (409) : bus masih dipakai trip.

```json
{
  "message" : "Bus masih dipakai oleh trip.",
  "code"    : "HAS_DEPENDENCIES"
}
```

## Route API

### List Routes

Endpoint : GET /api/v1/admin/routes

Query Params : `page`, `per_page`

Response Body Success (200) :

```json
{
  "data" : [
    {
      "id"         : 3,
      "asal"       : "Jakarta",
      "tujuan"     : "Yogyakarta",
      "status"     : "Active",
      "created_at" : "2026-11-01T09:00:00+07:00"
    }
  ],
  "meta" : { "current_page" : 1, "last_page" : 1, "per_page" : 10, "total" : 1 }
}
```

### Create Route

Endpoint : POST /api/v1/admin/routes

Request Body :

```json
{
  "asal"   : "Jakarta",
  "tujuan" : "Yogyakarta",
  "status" : "Active"
}
```

Aturan : `asal` wajib maks 100. `tujuan` wajib maks 100, tidak boleh sama dengan `asal`. Pasangan `asal` + `tujuan` unik (dicek aplikasi). `status` opsional, default `Active`.

Response Body Success (201) :

```json
{
  "data" : {
    "id"         : 3,
    "asal"       : "Jakarta",
    "tujuan"     : "Yogyakarta",
    "status"     : "Active",
    "created_at" : "2026-11-01T09:00:00+07:00"
  }
}
```

Response Body Error (422) :

```json
{
  "message" : "Data tidak valid.",
  "code"    : "VALIDATION_ERROR",
  "errors"  : {
    "tujuan" : ["Rute ini sudah ada."]
  }
}
```

### Get Route Detail

Endpoint : GET /api/v1/admin/routes/{id}

Response Body Success (200) : `{ "data" : <objek rute> }` seperti Create Route.

Response Body Error (404) : `NOT_FOUND`.

### Update Route

Endpoint : PUT /api/v1/admin/routes/{id}

Request Body :

```json
{
  "asal"   : "Jakarta",
  "tujuan" : "Yogyakarta",
  "status" : "Inactive"
}
```

Response Body Success (200) : `{ "data" : <objek rute> }`.

Response Body Error : 422 `VALIDATION_ERROR`, 404 `NOT_FOUND`.

### Delete Route

Endpoint : DELETE /api/v1/admin/routes/{id}

Response Body Success (204) : (tanpa body)

Response Body Error (409) :

```json
{
  "message" : "Rute masih dipakai oleh trip.",
  "code"    : "HAS_DEPENDENCIES"
}
```

## Trip API (Admin)

### List Trips

Endpoint : GET /api/v1/admin/trips

Query Params : `route_id`, `bus_id`, `tanggal_dari`, `tanggal_sampai`, `page`, `per_page`

Response Body Success (200) :

```json
{
  "data" : [
    {
      "id"                : 12,
      "tanggal_berangkat" : "2026-11-20",
      "jam_berangkat"     : "08:30",
      "harga"             : "150000.00",
      "kursi_tersedia"    : 28,
      "kursi_terisi"      : 4,
      "route" : { "id" : 3, "asal" : "Jakarta", "tujuan" : "Yogyakarta" },
      "bus"   : { "id" : 2, "tipe_bus" : "Executive", "kapasitas" : 32, "plat_nomor" : "B 1234 XY" }
    }
  ],
  "meta" : { "current_page" : 1, "last_page" : 1, "per_page" : 10, "total" : 1 }
}
```

### Create Trip

Endpoint : POST /api/v1/admin/trips

Request Body :

```json
{
  "bus_id"            : 2,
  "route_id"          : 3,
  "tanggal_berangkat" : "2026-11-20",
  "jam_berangkat"     : "08:30",
  "harga"             : "150000"
}
```

Aturan : `bus_id` ada dan `Active`. `route_id` ada dan `Active`. `tanggal_berangkat` `YYYY-MM-DD`, tidak sebelum hari ini. `jam_berangkat` `HH:MM`. `harga` angka > 0, maks 9999999.

Response Body Success (201) : `{ "data" : <objek trip admin> }` seperti List Trips.

Response Body Error :

422, bus sudah punya trip di tanggal itu :

```json
{
  "message" : "Bus sudah memiliki trip pada tanggal tersebut.",
  "code"    : "SCHEDULE_CONFLICT"
}
```

422, input tidak valid atau bus/rute Inactive : `VALIDATION_ERROR`.

### Get Trip Detail

Endpoint : GET /api/v1/admin/trips/{id}

Response Body Success (200) : `{ "data" : <objek trip admin> }`.

Response Body Error (404) : `NOT_FOUND`.

### Update Trip

Endpoint : PUT /api/v1/admin/trips/{id}

Request Body : field sama seperti Create Trip.

Catatan : `harga` boleh diubah kapan saja (reservasi lama tidak terpengaruh). `bus_id`, `route_id`, tanggal, dan jam hanya boleh diubah bila belum ada reservasi aktif.

Response Body Success (200) : `{ "data" : <objek trip admin> }`.

Response Body Error (409) :

```json
{
  "message" : "Trip sudah memiliki reservasi.",
  "code"    : "TRIP_HAS_RESERVATIONS"
}
```

Error lain : 422 `VALIDATION_ERROR`, 422 `SCHEDULE_CONFLICT`, 404 `NOT_FOUND`.

### Delete Trip

Endpoint : DELETE /api/v1/admin/trips/{id}

Response Body Success (204) : (tanpa body)

Response Body Error (409) : trip punya reservasi (termasuk yang dibatalkan).

```json
{
  "message" : "Trip sudah memiliki reservasi.",
  "code"    : "TRIP_HAS_RESERVATIONS"
}
```

## Reservation API (Admin)

### List All Reservations

Endpoint : GET /api/v1/admin/reservations

Query Params : `status`, `payment_status`, `trip_id`, `tanggal` (tanggal berangkat), `q` (nama atau email user, cocok sebagian), `page`, `per_page`

Response Body Success (200) : daftar Objek Reservation dengan tambahan `user`.

```json
{
  "data" : [
    {
      "id"                : 57,
      "status"            : "confirmed",
      "payment_status"    : "paid",
      "total_harga"       : "300000.00",
      "jumlah_kursi"      : 2,
      "created_at"        : "2026-11-05T10:15:00+07:00",
      "expires_at"        : null,
      "hold_seconds_left" : null,
      "user" : { "id" : 5, "name" : "ren", "email" : "ren@example.com", "role" : "customer" },
      "trip" : {
        "id"                : 12,
        "tanggal_berangkat" : "2026-11-20",
        "jam_berangkat"     : "08:30",
        "harga"             : "150000.00",
        "route" : { "id" : 3, "asal" : "Jakarta", "tujuan" : "Yogyakarta" },
        "bus"   : { "id" : 2, "tipe_bus" : "Executive", "kapasitas" : 32 }
      },
      "passengers" : [
        { "seat_id" : 101, "seat_number" : "1A", "nama_penumpang" : "ren" },
        { "seat_id" : 103, "seat_number" : "1C", "nama_penumpang" : "Sari" }
      ]
    }
  ],
  "meta" : { "current_page" : 1, "last_page" : 1, "per_page" : 10, "total" : 1 }
}
```

Response Body Error (422) : `VALIDATION_ERROR` untuk filter tidak valid.

### Get Reservation Detail

Endpoint : GET /api/v1/admin/reservations/{id}

Response Body Success (200) : `{ "data" : <Objek Reservation + user> }`.

Response Body Error (404) : `NOT_FOUND`.

### Cancel Reservation (Admin)

Endpoint : POST /api/v1/admin/reservations/{id}/cancel

Request Body : (kosong)

Aturan : sama dengan Cancel Reservation customer (pending jadi `unpaid`, confirmed jadi `refunded` 100% simulasi), tetapi admin boleh membatalkan trip yang sudah berangkat.

Response Body Success (200) : `{ "data" : <Objek Reservation + user> }` dengan status `cancelled`, `passengers` `[]`.

Response Body Error :

409, sudah dibatalkan :

```json
{
  "message" : "Reservasi sudah dibatalkan.",
  "code"    : "INVALID_STATE"
}
```

404 : `NOT_FOUND`.

---

# Katalog Kode Error

| HTTP | `code` | Arti |
|---|---|---|
| 401 | `UNAUTHENTICATED` | Token tidak ada atau tidak valid |
| 401 | `INVALID_CREDENTIALS` | Email atau password salah |
| 403 | `FORBIDDEN` | Bukan admin |
| 404 | `NOT_FOUND` | Data tidak ada atau bukan milik user |
| 409 | `SEAT_TAKEN` | Kursi sudah dipesan (ada `seat_numbers`) |
| 409 | `INVALID_STATE` | Aksi tidak cocok dengan status reservasi |
| 409 | `RESERVATION_EXPIRED` | Membayar setelah masa hold habis |
| 409 | `HAS_DEPENDENCIES` | Bus atau rute masih dipakai trip |
| 409 | `TRIP_HAS_RESERVATIONS` | Trip punya reservasi |
| 422 | `VALIDATION_ERROR` | Input tidak valid (ada `errors`) |
| 422 | `SEAT_NOT_IN_BUS` | Kursi bukan milik bus trip |
| 422 | `TRIP_NOT_AVAILABLE` | Bus atau rute trip Inactive |
| 422 | `TRIP_DEPARTED` | Trip sudah berangkat |
| 422 | `TOO_MANY_PENDING` | Reservasi pending melebihi batas |
| 422 | `SCHEDULE_CONFLICT` | Bus sudah punya trip di tanggal itu |
| 429 | (bawaan) | Terlalu banyak percobaan login |
