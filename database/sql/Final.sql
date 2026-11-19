CREATE DATABASE sistem_reservasi_bus;

USE sistem_reservasi_bus;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE buses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plat_nomor VARCHAR(20) NOT NULL UNIQUE,
    kapasitas INT UNSIGNED NOT NULL,
    tipe_bus VARCHAR(50) NOT NULL, -- contoh: VIP, Executive, Suite Class
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE seats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bus_id BIGINT UNSIGNED NOT NULL,
    seat_number VARCHAR(10) NOT NULL, -- contoh: '1A', '12C'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_bus_seat (bus_id, seat_number), -- BUS A = 1A, BUS B = 1A, BUS A = 1A
    CONSTRAINT fk_seats_bus FOREIGN KEY (bus_id) REFERENCES buses(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);
 

CREATE TABLE routes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asal VARCHAR(100) NOT NULL,
    tujuan VARCHAR(100) NOT NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE trips (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bus_id BIGINT UNSIGNED NOT NULL,
    route_id BIGINT UNSIGNED NOT NULL,
    tanggal_berangkat DATE NOT NULL,
    jam_berangkat TIME NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_trips_bus FOREIGN KEY (bus_id) REFERENCES buses(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_trips_route FOREIGN KEY (route_id) REFERENCES routes(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
); -- Trips Bali - Madura 19/2/2025 (Active), Bali-Madura 



CREATE TABLE reservations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    trip_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending', -- X
    total_harga DECIMAL(10,2) NOT NULL, -- Jadi (10,2)
    payment_status ENUM('unpaid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservations_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_reservations_trip FOREIGN KEY (trip_id) REFERENCES trips(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE reservation_seats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reservation_id BIGINT UNSIGNED NOT NULL,
    seat_id BIGINT UNSIGNED NOT NULL,
    trip_id BIGINT UNSIGNED NOT NULL,
    nama_penumpang VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_trip_seat (trip_id, seat_id),
    CONSTRAINT fk_resseats_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_resseats_seat FOREIGN KEY (seat_id) REFERENCES seats(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_resseats_trip FOREIGN KEY (trip_id) REFERENCES trips(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);