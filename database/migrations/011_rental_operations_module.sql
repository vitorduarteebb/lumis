-- Módulo operacional de Locações (entregas/coletas) — logística
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS rental_operations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  store_id BIGINT UNSIGNED NULL,
  document_number VARCHAR(40) NOT NULL,
  client_id BIGINT UNSIGNED NOT NULL,
  rental_date DATE NOT NULL,
  expected_delivery_date DATE NULL,
  expected_pickup_date DATE NULL,
  cep VARCHAR(16) NULL,
  street VARCHAR(255) NULL,
  address_number VARCHAR(32) NULL,
  complement VARCHAR(120) NULL,
  district VARCHAR(120) NULL,
  city VARCHAR(120) NULL,
  state VARCHAR(8) NULL,
  reference VARCHAR(500) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  contact_name VARCHAR(255) NULL,
  phone_primary VARCHAR(40) NULL,
  phone_secondary VARCHAR(40) NULL,
  notes_internal TEXT NULL,
  notes_driver TEXT NULL,
  operation_type VARCHAR(24) NOT NULL DEFAULT 'both' COMMENT 'delivery,pickup,both',
  status VARCHAR(32) NOT NULL DEFAULT 'pending',
  delivery_user_id BIGINT UNSIGNED NULL,
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_rental_doc_company (company_id, document_number),
  KEY idx_ro_company_status (company_id, status),
  KEY idx_ro_delivery (company_id, delivery_user_id),
  KEY idx_ro_client (client_id),
  KEY idx_ro_dates (company_id, rental_date),
  CONSTRAINT fk_ro_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_ro_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE RESTRICT,
  CONSTRAINT fk_ro_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE SET NULL,
  CONSTRAINT fk_ro_delivery_user FOREIGN KEY (delivery_user_id) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT fk_ro_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT fk_ro_updated_by FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rental_operation_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  rental_operation_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NULL,
  product_name VARCHAR(255) NOT NULL,
  qty DECIMAL(14,4) NOT NULL DEFAULT 1.0000,
  notes VARCHAR(500) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_roi_op (rental_operation_id),
  CONSTRAINT fk_roi_op FOREIGN KEY (rental_operation_id) REFERENCES rental_operations (id) ON DELETE CASCADE,
  CONSTRAINT fk_roi_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rental_operation_status_history (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  rental_operation_id BIGINT UNSIGNED NOT NULL,
  from_status VARCHAR(32) NULL,
  to_status VARCHAR(32) NOT NULL,
  note VARCHAR(1000) NULL,
  user_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_rosh_op (rental_operation_id),
  CONSTRAINT fk_rosh_op FOREIGN KEY (rental_operation_id) REFERENCES rental_operations (id) ON DELETE CASCADE,
  CONSTRAINT fk_rosh_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'is_delivery_driver');
SET @sql := IF(@c = 0, 'ALTER TABLE users ADD COLUMN is_delivery_driver TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER status', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

INSERT IGNORE INTO roles (name, slug, description, created_at, updated_at)
VALUES ('Entregador', 'entregador', 'Acesso ao painel de entregas de locações', NOW(), NOW());
