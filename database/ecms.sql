-- =====================================================================
--  ECMS — Electricity Consumption Monitoring System
--  Database schema (Section 5 of the SRS)
--
--  Import this file in phpMyAdmin (or: mysql -u root < ecms.sql)
--  It creates the `ecms` database and its four tables.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS ecms
    CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ecms;

-- ---------------------------------------------------------------------
-- Table 1: users
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT             NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100)    NOT NULL,
    email       VARCHAR(150)    NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL,                 -- PHP password_hash()
    bill_limit  DECIMAL(10,2)   NOT NULL DEFAULT 0,       -- monthly budget (PKR)
    role        ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table 2: appliances
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS appliances (
    id            INT           NOT NULL AUTO_INCREMENT,
    user_id       INT           NOT NULL,
    name          VARCHAR(100)  NOT NULL,                 -- e.g. AC, Fan
    wattage       DECIMAL(8,2)  NOT NULL,                 -- power rating in Watts
    hours_per_day DECIMAL(4,2)  NOT NULL,                 -- average daily usage
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table 3: monthly_bills
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS monthly_bills (
    id          INT           NOT NULL AUTO_INCREMENT,
    user_id     INT           NOT NULL,
    month       TINYINT       NOT NULL,                   -- 1 - 12
    year        YEAR          NOT NULL,
    total_units DECIMAL(10,2) NOT NULL,                   -- total kWh that month
    total_bill  DECIMAL(10,2) NOT NULL,                   -- estimated bill (PKR)
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_month (user_id, month, year)     -- one record per month
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table 4: alerts
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS alerts (
    id         INT        NOT NULL AUTO_INCREMENT,
    user_id    INT        NOT NULL,
    message    TEXT       NOT NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,             -- 0 = unread, 1 = read
    created_at TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table 5: tariff_slabs  (editable WAPDA unit prices — NFR-05)
--   slab_to = NULL means "and above" (the top, unbounded slab).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tariff_slabs (
    id        INT          NOT NULL AUTO_INCREMENT,
    slab_from INT          NOT NULL,                    -- range start (units)
    slab_to   INT          NULL,                        -- range end; NULL = unbounded
    rate      DECIMAL(8,2) NOT NULL,                    -- price per unit (PKR)
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Seed default rates only if the table is empty.
INSERT INTO tariff_slabs (slab_from, slab_to, rate)
SELECT * FROM (
    SELECT 1   AS slab_from, 100  AS slab_to, 13.48 AS rate UNION ALL
    SELECT 101, 200,  18.95 UNION ALL
    SELECT 201, 300,  22.14 UNION ALL
    SELECT 301, 400,  25.53 UNION ALL
    SELECT 401, 500,  27.74 UNION ALL
    SELECT 501, 600,  29.16 UNION ALL
    SELECT 601, 700,  30.30 UNION ALL
    SELECT 701, NULL, 35.24
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM tariff_slabs);

-- ---------------------------------------------------------------------
-- Default admin account:
--   A password hash cannot be reliably hard-coded in SQL, so the admin
--   user is seeded by running  setup.php  once in the browser
--   (http://localhost/ecms/setup.php). That script uses PHP's
--   password_hash() to create the account safely.
-- ---------------------------------------------------------------------
