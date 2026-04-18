-- Lumis ERP — tabelas operacionais (vendas com itens, estoque, orçamentos, contratos, NF admin, compras)
SET NAMES utf8mb4;

SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_tickets' AND COLUMN_NAME = 'body');
SET @sql := IF(@exist = 0, 'ALTER TABLE support_tickets ADD COLUMN body TEXT NULL AFTER subject', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS sales_document_lines (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sales_document_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NULL,
  service_id BIGINT UNSIGNED NULL,
  description VARCHAR(500) NULL,
  qty DECIMAL(14,4) NOT NULL DEFAULT 1.0000,
  unit_price DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  line_total DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (id),
  KEY idx_sdl_doc (sales_document_id),
  CONSTRAINT fk_sdl_doc FOREIGN KEY (sales_document_id) REFERENCES sales_documents (id) ON DELETE CASCADE,
  CONSTRAINT fk_sdl_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL,
  CONSTRAINT fk_sdl_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_movements (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  movement_type VARCHAR(24) NOT NULL COMMENT 'in,out,adjust,transfer_out,transfer_in',
  qty DECIMAL(14,4) NOT NULL,
  reference VARCHAR(120) NULL,
  notes VARCHAR(500) NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_sm_company (company_id),
  KEY idx_sm_product (product_id),
  CONSTRAINT fk_sm_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_sm_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
  CONSTRAINT fk_sm_user FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  client_id BIGINT UNSIGNED NULL,
  quote_kind VARCHAR(20) NOT NULL DEFAULT 'product',
  status VARCHAR(32) NOT NULL DEFAULT 'draft',
  total_amount DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  valid_until DATE NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_qu_company (company_id),
  CONSTRAINT fk_qu_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_qu_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quote_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  quote_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NULL,
  service_id BIGINT UNSIGNED NULL,
  description VARCHAR(500) NULL,
  qty DECIMAL(14,4) NOT NULL DEFAULT 1.0000,
  unit_price DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  line_total DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (id),
  KEY idx_qi_quote (quote_id),
  CONSTRAINT fk_qi_quote FOREIGN KEY (quote_id) REFERENCES quotes (id) ON DELETE CASCADE,
  CONSTRAINT fk_qi_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL,
  CONSTRAINT fk_qi_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contract_registry (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  client_id BIGINT UNSIGNED NULL,
  contract_type VARCHAR(24) NOT NULL,
  title VARCHAR(255) NOT NULL,
  amount DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  start_date DATE NULL,
  end_date DATE NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'active',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_cr_company (company_id),
  CONSTRAINT fk_cr_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_cr_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fiscal_note_registry (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  note_kind VARCHAR(32) NOT NULL,
  number VARCHAR(50) NULL,
  series VARCHAR(20) NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'recorded',
  issued_at DATETIME NULL,
  total_amount DECIMAL(14,4) NULL,
  xml_path VARCHAR(500) NULL,
  pdf_path VARCHAR(500) NULL,
  notes VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_fn_company (company_id),
  CONSTRAINT fk_fn_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bank_slip_records (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  payer_name VARCHAR(255) NULL,
  amount DECIMAL(14,4) NOT NULL,
  due_date DATE NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'pending',
  our_number VARCHAR(80) NULL,
  notes VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_bs_company (company_id),
  CONSTRAINT fk_bs_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_orders (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  supplier_id BIGINT UNSIGNED NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'open',
  total_amount DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  expected_at DATE NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_po_company (company_id),
  CONSTRAINT fk_po_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_po_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_order_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  purchase_order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  qty DECIMAL(14,4) NOT NULL,
  unit_price DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  line_total DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (id),
  KEY idx_poi_po (purchase_order_id),
  CONSTRAINT fk_poi_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_poi_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_returns (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  return_kind VARCHAR(24) NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  qty DECIMAL(14,4) NOT NULL,
  reason VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_sr_company (company_id),
  CONSTRAINT fk_sr_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_sr_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
