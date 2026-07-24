-- =====================================================================
--  AutoVault - Database Schema
-- ---------------------------------------------------------------------
--  Vehicle marketplace: browse vehicles, accounts, favourites,
--  test-drive requests, admin management, themes and monitoring.
--
--  Target : Oracle MySQL 8
--  Engine : InnoDB
--  Charset: utf8mb4 (utf8mb4_unicode_ci)
--  Access : PHP PDO with prepared statements
--
--  NOTE: This file defines the DATABASE STRUCTURE ONLY.
--        It contains NO sample data and NO seed rows.
--        Authentication logic, seed data and the website itself are
--        added in later milestones.
-- =====================================================================

-- Create the database and switch to it.
CREATE DATABASE IF NOT EXISTS autovault
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE autovault;

-- Drop tables first so the script can be re-run cleanly during setup.
-- Order matters because of foreign keys (drop children before parents).
DROP TABLE IF EXISTS test_drive_requests;
DROP TABLE IF EXISTS favourites;
DROP TABLE IF EXISTS vehicle_options;
DROP TABLE IF EXISTS vehicle_images;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS system_services;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS themes;
DROP TABLE IF EXISTS users;


-- ---------------------------------------------------------------------
-- users
-- Stores accounts for both normal users and administrators.
-- Passwords are stored ONLY as a hash created by PHP password_hash()
-- (no passwords are created in this schema). is_active lets an admin
-- disable/enable an account.
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name     VARCHAR(100)      NOT NULL,
    email         VARCHAR(150)      NOT NULL,
    password_hash VARCHAR(255)      NOT NULL,
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',
    is_active     TINYINT(1)        NOT NULL DEFAULT 1,
    created_at    TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                    ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- themes
-- The three website themes. "slug" is used as the CSS file name and
-- <body> class. Exactly one theme should have is_active = 1; the admin
-- can switch the active theme dynamically.
-- ---------------------------------------------------------------------
CREATE TABLE themes (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(50)  NOT NULL,
    slug        VARCHAR(50)  NOT NULL,
    description VARCHAR(255)      NULL,
    is_active   TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_themes_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- settings
-- Simple key/value store for site-wide settings such as the site name,
-- contact email and the currently active theme slug.
-- ---------------------------------------------------------------------
CREATE TABLE settings (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key   VARCHAR(100) NOT NULL,
    setting_value TEXT             NULL,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                               ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- vehicles
-- The core catalogue. Adding a new vehicle is a single INSERT here
-- (plus rows in vehicle_images / vehicle_options), so the catalogue is
-- easy to update without editing many files.
-- ---------------------------------------------------------------------
CREATE TABLE vehicles (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    make         VARCHAR(50)  NOT NULL,
    model        VARCHAR(50)  NOT NULL,
    year         SMALLINT UNSIGNED NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    mileage      INT UNSIGNED      NULL,
    fuel_type    ENUM('Petrol','Diesel','Electric','Hybrid','Other')
                              NOT NULL DEFAULT 'Petrol',
    transmission ENUM('Automatic','Manual') NOT NULL DEFAULT 'Automatic',
    body_type    VARCHAR(50)       NULL,
    color        VARCHAR(30)       NULL,
    doors        TINYINT UNSIGNED  NULL,
    seats        TINYINT UNSIGNED  NULL,
    vin          VARCHAR(50)       NULL,
    description  TEXT              NULL,
    status       ENUM('available','reserved','sold') NOT NULL DEFAULT 'available',
    is_featured  TINYINT(1)   NOT NULL DEFAULT 0,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_vehicles_vin (vin),
    KEY idx_vehicles_make (make),
    KEY idx_vehicles_price (price),
    KEY idx_vehicles_status (status),
    KEY idx_vehicles_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- vehicle_images
-- One vehicle can have many images. is_primary marks the main photo
-- shown in the catalogue list. Deleting a vehicle deletes its images.
-- ---------------------------------------------------------------------
CREATE TABLE vehicle_images (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    vehicle_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text   VARCHAR(150)      NULL,
    is_primary TINYINT(1)   NOT NULL DEFAULT 0,
    sort_order INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_images_vehicle (vehicle_id),
    CONSTRAINT fk_images_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- vehicle_options
-- Selectable options for a vehicle (e.g. exterior colour, trim,
-- wheels). The assignment requires each vehicle to have at least two
-- selectable options. option_group lets options be shown as groups,
-- and price_adjustment can raise/lower the base price.
-- ---------------------------------------------------------------------
CREATE TABLE vehicle_options (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    vehicle_id       INT UNSIGNED NOT NULL,
    option_group     VARCHAR(50)  NOT NULL,
    option_name      VARCHAR(100) NOT NULL,
    price_adjustment DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_default       TINYINT(1)   NOT NULL DEFAULT 0,
    sort_order       INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_options_vehicle (vehicle_id),
    CONSTRAINT fk_options_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- favourites
-- Links a user to a vehicle they saved. The UNIQUE key prevents the
-- same vehicle being saved twice by the same user.
-- ---------------------------------------------------------------------
CREATE TABLE favourites (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    vehicle_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_fav_user_vehicle (user_id, vehicle_id),
    KEY idx_fav_vehicle (vehicle_id),
    CONSTRAINT fk_fav_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_fav_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- test_drive_requests
-- A request to test drive a vehicle. Logged-in users are linked via
-- user_id; guests can still request (user_id NULL). If the user or
-- vehicle is later deleted, the request is kept for history and the
-- link is set to NULL.
-- ---------------------------------------------------------------------
CREATE TABLE test_drive_requests (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    vehicle_id     INT UNSIGNED     NULL,
    user_id        INT UNSIGNED     NULL,
    full_name      VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL,
    phone          VARCHAR(30)      NULL,
    preferred_date DATE             NULL,
    preferred_time TIME             NULL,
    message        TEXT             NULL,
    status         ENUM('pending','confirmed','completed','cancelled')
                                NOT NULL DEFAULT 'pending',
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_td_vehicle (vehicle_id),
    KEY idx_td_user (user_id),
    KEY idx_td_status (status),
    CONSTRAINT fk_td_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_td_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- contact_messages
-- Messages sent through the public contact form (second dynamic form).
-- Admins can read them and mark them as read.
-- ---------------------------------------------------------------------
CREATE TABLE contact_messages (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    subject    VARCHAR(150)     NULL,
    message    TEXT         NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contact_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- system_services
-- Backs the "system monitoring" page. Each row is a feature/service
-- whose status (online/offline/degraded) is shown to the admin.
-- ---------------------------------------------------------------------
CREATE TABLE system_services (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    service_key     VARCHAR(50)  NOT NULL,
    name            VARCHAR(100) NOT NULL,
    description     VARCHAR(255)     NULL,
    status          ENUM('online','offline','degraded') NOT NULL DEFAULT 'online',
    last_checked_at TIMESTAMP        NULL,
    sort_order      INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_service_key (service_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- End of schema. No sample data is inserted by this file.
