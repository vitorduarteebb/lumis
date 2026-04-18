-- Vendas + estoque operacional (aplique uma vez em produção)
SET NAMES utf8mb4;

-- ========== sales_documents: cabeçalho completo ==========
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND COLUMN_NAME = 'document_kind');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_documents ADD COLUMN document_kind VARCHAR(32) NOT NULL DEFAULT ''product'' COMMENT ''product,service,balcao'' AFTER client_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND COLUMN_NAME = 'store_id');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_documents ADD COLUMN store_id BIGINT UNSIGNED NULL AFTER document_kind', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND COLUMN_NAME = 'seller_user_id');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_documents ADD COLUMN seller_user_id BIGINT UNSIGNED NULL AFTER store_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND COLUMN_NAME = 'notes');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_documents ADD COLUMN notes TEXT NULL AFTER seller_user_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND COLUMN_NAME = 'subtotal_amount');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_documents ADD COLUMN subtotal_amount DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER notes', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND COLUMN_NAME = 'discount_total');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_documents ADD COLUMN discount_total DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER subtotal_amount', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND COLUMN_NAME = 'payment_method_entry_id');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_documents ADD COLUMN payment_method_entry_id BIGINT UNSIGNED NULL AFTER discount_total', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND COLUMN_NAME = 'payment_terms_entry_id');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_documents ADD COLUMN payment_terms_entry_id BIGINT UNSIGNED NULL AFTER payment_method_entry_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND COLUMN_NAME = 'sale_channel_entry_id');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_documents ADD COLUMN sale_channel_entry_id BIGINT UNSIGNED NULL AFTER payment_terms_entry_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND COLUMN_NAME = 'accounts_receivable_id');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_documents ADD COLUMN accounts_receivable_id BIGINT UNSIGNED NULL AFTER sale_channel_entry_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND COLUMN_NAME = 'created_by');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_documents ADD COLUMN created_by BIGINT UNSIGNED NULL AFTER updated_at', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND COLUMN_NAME = 'updated_by');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_documents ADD COLUMN updated_by BIGINT UNSIGNED NULL AFTER created_by', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND INDEX_NAME = 'idx_sd_store');
SET @sql := IF(@idx = 0, 'ALTER TABLE sales_documents ADD KEY idx_sd_store (store_id)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND CONSTRAINT_NAME = 'fk_sd_store');
SET @sql := IF(@idx = 0, 'ALTER TABLE sales_documents ADD CONSTRAINT fk_sd_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND CONSTRAINT_NAME = 'fk_sd_seller');
SET @sql := IF(@idx = 0, 'ALTER TABLE sales_documents ADD CONSTRAINT fk_sd_seller FOREIGN KEY (seller_user_id) REFERENCES users (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_documents' AND CONSTRAINT_NAME = 'fk_sd_ar');
SET @sql := IF(@idx = 0, 'ALTER TABLE sales_documents ADD CONSTRAINT fk_sd_ar FOREIGN KEY (accounts_receivable_id) REFERENCES accounts_receivable (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ========== sales_document_lines: desconto linha ==========
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_document_lines' AND COLUMN_NAME = 'line_discount');
SET @sql := IF(@c = 0, 'ALTER TABLE sales_document_lines ADD COLUMN line_discount DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER unit_price', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ========== product_store_stock (saldo por loja) ==========
CREATE TABLE IF NOT EXISTS product_store_stock (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  store_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  qty DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pss_csp (company_id, store_id, product_id),
  KEY idx_pss_product (product_id),
  CONSTRAINT fk_pss_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_pss_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE,
  CONSTRAINT fk_pss_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== stock_movements: metadados ==========
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_movements' AND COLUMN_NAME = 'store_id');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_movements ADD COLUMN store_id BIGINT UNSIGNED NULL AFTER product_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_movements' AND COLUMN_NAME = 'balance_before');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_movements ADD COLUMN balance_before DECIMAL(14,4) NULL AFTER qty', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_movements' AND COLUMN_NAME = 'balance_after');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_movements ADD COLUMN balance_after DECIMAL(14,4) NULL AFTER balance_before', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_movements' AND COLUMN_NAME = 'ref_table');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_movements ADD COLUMN ref_table VARCHAR(48) NULL AFTER reference', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_movements' AND COLUMN_NAME = 'ref_id');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_movements ADD COLUMN ref_id BIGINT UNSIGNED NULL AFTER ref_table', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_movements' AND INDEX_NAME = 'idx_sm_store');
SET @sql := IF(@idx = 0, 'ALTER TABLE stock_movements ADD KEY idx_sm_store (store_id)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_movements' AND CONSTRAINT_NAME = 'fk_sm_store');
SET @sql := IF(@idx = 0, 'ALTER TABLE stock_movements ADD CONSTRAINT fk_sm_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ========== Ajustes de estoque ==========
CREATE TABLE IF NOT EXISTS stock_adjustments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  store_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  direction VARCHAR(8) NOT NULL COMMENT 'in,out',
  qty DECIMAL(14,4) NOT NULL,
  reason_text VARCHAR(500) NULL,
  notes TEXT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_sadj_company (company_id),
  KEY idx_sadj_product (product_id),
  CONSTRAINT fk_sadj_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_sadj_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE,
  CONSTRAINT fk_sadj_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
  CONSTRAINT fk_sadj_user FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== Transferências ==========
CREATE TABLE IF NOT EXISTS stock_transfers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  from_store_id BIGINT UNSIGNED NOT NULL,
  to_store_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'pending' COMMENT 'pending,done,cancelled',
  notes TEXT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_str_company (company_id),
  CONSTRAINT fk_str_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_str_from FOREIGN KEY (from_store_id) REFERENCES stores (id) ON DELETE CASCADE,
  CONSTRAINT fk_str_to FOREIGN KEY (to_store_id) REFERENCES stores (id) ON DELETE CASCADE,
  CONSTRAINT fk_str_user FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_transfer_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  transfer_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  qty DECIMAL(14,4) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_sti_transfer (transfer_id),
  CONSTRAINT fk_sti_transfer FOREIGN KEY (transfer_id) REFERENCES stock_transfers (id) ON DELETE CASCADE,
  CONSTRAINT fk_sti_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== Cotações de fornecedor (compra) ==========
CREATE TABLE IF NOT EXISTS supplier_quotes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  supplier_id BIGINT UNSIGNED NOT NULL,
  quote_number VARCHAR(50) NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'open' COMMENT 'open,approved,cancelled',
  quoted_at DATE NOT NULL,
  notes TEXT NULL,
  total_amount DECIMAL(14,4) NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_squ_company (company_id),
  CONSTRAINT fk_squ_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_squ_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers (id) ON DELETE CASCADE,
  CONSTRAINT fk_squ_user FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supplier_quote_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  supplier_quote_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  qty DECIMAL(14,4) NOT NULL DEFAULT 1,
  unit_cost DECIMAL(14,4) NOT NULL DEFAULT 0,
  line_total DECIMAL(14,4) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_sqi_quote (supplier_quote_id),
  CONSTRAINT fk_sqi_quote FOREIGN KEY (supplier_quote_id) REFERENCES supplier_quotes (id) ON DELETE CASCADE,
  CONSTRAINT fk_sqi_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== Compras: estender purchase_orders ==========
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders' AND COLUMN_NAME = 'document_number');
SET @sql := IF(@c = 0, 'ALTER TABLE purchase_orders ADD COLUMN document_number VARCHAR(50) NULL AFTER supplier_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders' AND COLUMN_NAME = 'notes');
SET @sql := IF(@c = 0, 'ALTER TABLE purchase_orders ADD COLUMN notes TEXT NULL AFTER total_amount', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders' AND COLUMN_NAME = 'issued_at');
SET @sql := IF(@c = 0, 'ALTER TABLE purchase_orders ADD COLUMN issued_at DATETIME NULL AFTER notes', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders' AND COLUMN_NAME = 'store_id');
SET @sql := IF(@c = 0, 'ALTER TABLE purchase_orders ADD COLUMN store_id BIGINT UNSIGNED NULL AFTER issued_at', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders' AND COLUMN_NAME = 'supplier_quote_id');
SET @sql := IF(@c = 0, 'ALTER TABLE purchase_orders ADD COLUMN supplier_quote_id BIGINT UNSIGNED NULL AFTER store_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders' AND COLUMN_NAME = 'created_by');
SET @sql := IF(@c = 0, 'ALTER TABLE purchase_orders ADD COLUMN created_by BIGINT UNSIGNED NULL AFTER deleted_at', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders' AND CONSTRAINT_NAME = 'fk_po_store');
SET @sql := IF(@idx = 0, 'ALTER TABLE purchase_orders ADD CONSTRAINT fk_po_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders' AND CONSTRAINT_NAME = 'fk_po_squ');
SET @sql := IF(@idx = 0, 'ALTER TABLE purchase_orders ADD CONSTRAINT fk_po_squ FOREIGN KEY (supplier_quote_id) REFERENCES supplier_quotes (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders' AND CONSTRAINT_NAME = 'fk_po_creator');
SET @sql := IF(@idx = 0, 'ALTER TABLE purchase_orders ADD CONSTRAINT fk_po_creator FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- purchase_order_items: desconto linha
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_order_items' AND COLUMN_NAME = 'line_discount');
SET @sql := IF(@c = 0, 'ALTER TABLE purchase_order_items ADD COLUMN line_discount DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER unit_price', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ========== Trocas / devoluções (estende stock_returns) ==========
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND COLUMN_NAME = 'store_id');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_returns ADD COLUMN store_id BIGINT UNSIGNED NULL AFTER company_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND COLUMN_NAME = 'client_id');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_returns ADD COLUMN client_id BIGINT UNSIGNED NULL AFTER return_kind', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND COLUMN_NAME = 'supplier_id');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_returns ADD COLUMN supplier_id BIGINT UNSIGNED NULL AFTER client_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND COLUMN_NAME = 'sales_document_id');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_returns ADD COLUMN sales_document_id BIGINT UNSIGNED NULL AFTER supplier_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND COLUMN_NAME = 'purchase_order_id');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_returns ADD COLUMN purchase_order_id BIGINT UNSIGNED NULL AFTER sales_document_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND COLUMN_NAME = 'status');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_returns ADD COLUMN status VARCHAR(24) NOT NULL DEFAULT ''recorded'' AFTER qty', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND COLUMN_NAME = 'notes');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_returns ADD COLUMN notes TEXT NULL AFTER reason', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND COLUMN_NAME = 'created_by');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_returns ADD COLUMN created_by BIGINT UNSIGNED NULL AFTER created_at', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND COLUMN_NAME = 'updated_at');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_returns ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_by', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND COLUMN_NAME = 'deleted_at');
SET @sql := IF(@c = 0, 'ALTER TABLE stock_returns ADD COLUMN deleted_at DATETIME NULL AFTER updated_at', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND CONSTRAINT_NAME = 'fk_sr_store');
SET @sql := IF(@idx = 0, 'ALTER TABLE stock_returns ADD CONSTRAINT fk_sr_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND CONSTRAINT_NAME = 'fk_sr_client');
SET @sql := IF(@idx = 0, 'ALTER TABLE stock_returns ADD CONSTRAINT fk_sr_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND CONSTRAINT_NAME = 'fk_sr_supplier');
SET @sql := IF(@idx = 0, 'ALTER TABLE stock_returns ADD CONSTRAINT fk_sr_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND CONSTRAINT_NAME = 'fk_sr_sd');
SET @sql := IF(@idx = 0, 'ALTER TABLE stock_returns ADD CONSTRAINT fk_sr_sd FOREIGN KEY (sales_document_id) REFERENCES sales_documents (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND CONSTRAINT_NAME = 'fk_sr_po');
SET @sql := IF(@idx = 0, 'ALTER TABLE stock_returns ADD CONSTRAINT fk_sr_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND CONSTRAINT_NAME = 'fk_sr_creator');
SET @sql := IF(@idx = 0, 'ALTER TABLE stock_returns ADD CONSTRAINT fk_sr_creator FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_returns' AND INDEX_NAME = 'idx_sr_deleted');
SET @sql := IF(@idx = 0, 'ALTER TABLE stock_returns ADD KEY idx_sr_deleted (deleted_at)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- accounts_payable link na compra
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders' AND COLUMN_NAME = 'accounts_payable_id');
SET @sql := IF(@c = 0, 'ALTER TABLE purchase_orders ADD COLUMN accounts_payable_id BIGINT UNSIGNED NULL AFTER supplier_quote_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'purchase_orders' AND CONSTRAINT_NAME = 'fk_po_ap');
SET @sql := IF(@idx = 0, 'ALTER TABLE purchase_orders ADD CONSTRAINT fk_po_ap FOREIGN KEY (accounts_payable_id) REFERENCES accounts_payable (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Primeira loja ativa por empresa recebe o estoque legado (products.stock_qty)
INSERT IGNORE INTO product_store_stock (company_id, store_id, product_id, qty)
SELECT p.company_id, st.min_id, p.id, p.stock_qty
FROM products p
INNER JOIN (
  SELECT company_id, MIN(id) AS min_id FROM stores WHERE status = 1 GROUP BY company_id
) st ON st.company_id = p.company_id
WHERE p.deleted_at IS NULL;
