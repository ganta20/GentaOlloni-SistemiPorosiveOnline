-- ============================================================
-- Sistemi për Menaxhimin e Porosive Online
-- Skema e databazës (MySQL / MariaDB), Raporti Teknik 2
-- ============================================================

DROP DATABASE IF EXISTS porosi_online;
CREATE DATABASE porosi_online
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE porosi_online;

-- ------------------------------------------------------------
-- users: klientët dhe administratorët
-- ------------------------------------------------------------
CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- addresses: një përdorues ka shumë adresa (1-N)
-- ------------------------------------------------------------
CREATE TABLE addresses (
  id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id  INT UNSIGNED NOT NULL,
  street   VARCHAR(150) NOT NULL,
  city     VARCHAR(80)  NOT NULL,
  phone    VARCHAR(30)  NOT NULL,
  PRIMARY KEY (id),
  KEY idx_addresses_user (user_id),
  CONSTRAINT fk_addresses_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- categories: një kategori ka shumë produkte (1-N)
-- ------------------------------------------------------------
CREATE TABLE categories (
  id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name  VARCHAR(80) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- products
-- ------------------------------------------------------------
CREATE TABLE products (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id  INT UNSIGNED NOT NULL,
  name         VARCHAR(150) NOT NULL,
  description  TEXT NULL,
  price        DECIMAL(10,2) NOT NULL,
  stock        INT UNSIGNED NOT NULL DEFAULT 0,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_products_category (category_id),
  CONSTRAINT chk_products_price CHECK (price >= 0),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- cart_items: shporta e përdoruesit
-- Një produkt shfaqet vetëm një herë në shportën e një përdoruesi
-- ------------------------------------------------------------
CREATE TABLE cart_items (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     INT UNSIGNED NOT NULL,
  product_id  INT UNSIGNED NOT NULL,
  quantity    INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cart_user_product (user_id, product_id),
  CONSTRAINT chk_cart_quantity CHECK (quantity > 0),
  CONSTRAINT fk_cart_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_cart_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- orders
-- ------------------------------------------------------------
CREATE TABLE orders (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     INT UNSIGNED NOT NULL,
  address_id  INT UNSIGNED NOT NULL,
  status      ENUM('pending','confirmed','shipped','delivered','cancelled')
              NOT NULL DEFAULT 'pending',
  total       DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_orders_user (user_id),
  KEY idx_orders_address (address_id),
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE RESTRICT,
  CONSTRAINT fk_orders_address
    FOREIGN KEY (address_id) REFERENCES addresses(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- order_items: lidh porosinë me produktet (zbërthen N-M)
-- unit_price ruan çmimin në momentin e porosisë
-- ------------------------------------------------------------
CREATE TABLE order_items (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id    INT UNSIGNED NOT NULL,
  product_id  INT UNSIGNED NOT NULL,
  quantity    INT UNSIGNED NOT NULL,
  unit_price  DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_order_items_order (order_id),
  KEY idx_order_items_product (product_id),
  CONSTRAINT chk_order_items_quantity CHECK (quantity > 0),
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- payments: një porosi ka një pagesë (1-1)
-- ------------------------------------------------------------
CREATE TABLE payments (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id  INT UNSIGNED NOT NULL,
  method    ENUM('cash_on_delivery') NOT NULL DEFAULT 'cash_on_delivery',
  status    ENUM('pending','paid') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (id),
  UNIQUE KEY uq_payments_order (order_id),
  CONSTRAINT fk_payments_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TË DHËNA TESTUESE
-- Fjalëkalimet: admin@test.com -> Admin123!
--               klient@test.com -> Klient123!
-- ============================================================

INSERT INTO users (full_name, email, password_hash, role) VALUES
('Administratori', 'admin@test.com',
 '$2y$10$bbxU.HgXcRijUQp5EPeBiOkco5.SSvLxWI6ovmcV1u5aaXiGk1Gt6', 'admin'),
('Arben Krasniqi', 'klient@test.com',
 '$2y$10$FbAP9T8YObw1PIN/2gxk/u9Fl9nlBeqA51N0JrrinNgBXE8GDlHBm', 'customer');

INSERT INTO addresses (user_id, street, city, phone) VALUES
(2, 'Rruga e Shkodrës 12', 'Prizren', '044111222');

INSERT INTO categories (name) VALUES
('Elektronikë'), ('Veshje'), ('Shtëpi');

INSERT INTO products (category_id, name, description, price, stock) VALUES
(1, 'Kufje me tel',        'Kufje stereo me mikrofon.',       12.50, 40),
(1, 'Powerbank 10000 mAh', 'Karikues portativ me dy dalje.',  24.90, 25),
(2, 'Bluzë pambuku',       'Bluzë unisex, madhësi M.',        15.00, 60),
(2, 'Xhaketë dimri',       'Xhaketë e ngrohtë me kapuç.',     59.00, 15),
(3, 'Llambë tavoline',     'Llambë LED me ndriçim të rregullueshëm.', 18.75, 30),
(3, 'Set filxhanësh',      'Set me 6 filxhanë qeramike.',     21.00, 20);

INSERT INTO cart_items (user_id, product_id, quantity) VALUES
(2, 5, 1);

-- Një porosi shembull: 2 kufje + 1 bluzë = 2*12.50 + 15.00 = 40.00
INSERT INTO orders (user_id, address_id, status, total) VALUES
(2, 1, 'pending', 40.00);

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 2, 12.50),
(1, 3, 1, 15.00);

INSERT INTO payments (order_id, method, status) VALUES
(1, 'cash_on_delivery', 'pending');