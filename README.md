# Reservasi Bus API (Laravel)

## Setup
1. Import `database/sql/Final.sql` ke MySQL.
2. `composer install`
3. Salin `.env.example` menjadi `.env`, sesuaikan DB_USERNAME dan DB_PASSWORD
4. `php artisan key:generate`
5. `php artisan migrate`   (hanya membuat tabel token login)
6. `php artisan db:seed`   (akun dan data demo)
7. `php artisan serve`

API: http://localhost:8000/api/v1
Dokumentasi endpoint: docs/api-contract.md

Akun demo (password: password): admin@example.com, customer@example.com

do not jalanin `migrate:fresh` (menghapus tabel dari Final.sql).