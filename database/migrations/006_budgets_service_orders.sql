-- Orçamentos estendidos + itens de O.S. (aplique uma vez; evite rerodar migrate.php completo em produção)
SET NAMES utf8mb4;

-- quotes: número, emissão, descontos, conversão em venda
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quotes' AND COLUMN_NAME = 'quote_number');
SET @sql := IF(@c = 0, 'ALTER TABLE quotes ADD COLUMN quote_number VARCHAR(50) NULL AFTER company_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quotes' AND COLUMN_NAME = 'issued_at');
SET @sql := IF(@c = 0, 'ALTER TABLE quotes ADD COLUMN issued_at DATE NULL AFTER quote_number', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quotes' AND COLUMN_NAME = 'subtotal_amount');
SET @sql := IF(@c = 0, 'ALTER TABLE quotes ADD COLUMN subtotal_amount DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER status', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quotes' AND COLUMN_NAME = 'discount_total');
SET @sql := IF(@c = 0, 'ALTER TABLE quotes ADD COLUMN discount_total DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER subtotal_amount', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quotes' AND COLUMN_NAME = 'conversion_sales_document_id');
SET @sql := IF(@c = 0, 'ALTER TABLE quotes ADD COLUMN conversion_sales_document_id BIGINT UNSIGNED NULL AFTER notes', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quotes' AND COLUMN_NAME = 'created_by');
SET @sql := IF(@c = 0, 'ALTER TABLE quotes ADD COLUMN created_by BIGINT UNSIGNED NULL AFTER conversion_sales_document_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quotes' AND INDEX_NAME = 'idx_qu_conversion');
SET @sql := IF(@idx = 0, 'ALTER TABLE quotes ADD KEY idx_qu_conversion (conversion_sales_document_id)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- FK conversion (pode falhar se tabela sales_documents não existir — ignorar nesse caso)
SET @fk := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'quotes' AND CONSTRAINT_NAME = 'fk_qu_sales_doc');
SET @sql := IF(@fk = 0,
  'ALTER TABLE quotes ADD CONSTRAINT fk_qu_sales_doc FOREIGN KEY (conversion_sales_document_id) REFERENCES sales_documents (id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @fk := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'quotes' AND CONSTRAINT_NAME = 'fk_qu_created_by');
SET @sql := IF(@fk = 0,
  'ALTER TABLE quotes ADD CONSTRAINT fk_qu_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- quote_items: desconto por linha
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quote_items' AND COLUMN_NAME = 'line_discount');
SET @sql := IF(@c = 0, 'ALTER TABLE quote_items ADD COLUMN line_discount DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER unit_price', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- service_orders: técnico, vínculo orçamento, datas, notas, tipo
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_orders' AND COLUMN_NAME = 'assigned_user_id');
SET @sql := IF(@c = 0, 'ALTER TABLE service_orders ADD COLUMN assigned_user_id BIGINT UNSIGNED NULL AFTER client_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_orders' AND COLUMN_NAME = 'quote_id');
SET @sql := IF(@c = 0, 'ALTER TABLE service_orders ADD COLUMN quote_id BIGINT UNSIGNED NULL AFTER assigned_user_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_orders' AND COLUMN_NAME = 'expected_at');
SET @sql := IF(@c = 0, 'ALTER TABLE service_orders ADD COLUMN expected_at DATETIME NULL AFTER opened_at', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_orders' AND COLUMN_NAME = 'completed_at');
SET @sql := IF(@c = 0, 'ALTER TABLE service_orders ADD COLUMN completed_at DATETIME NULL AFTER expected_at', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_orders' AND COLUMN_NAME = 'internal_notes');
SET @sql := IF(@c = 0, 'ALTER TABLE service_orders ADD COLUMN internal_notes TEXT NULL AFTER description', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_orders' AND COLUMN_NAME = 'customer_notes');
SET @sql := IF(@c = 0, 'ALTER TABLE service_orders ADD COLUMN customer_notes TEXT NULL AFTER internal_notes', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_orders' AND COLUMN_NAME = 'os_type');
SET @sql := IF(@c = 0, 'ALTER TABLE service_orders ADD COLUMN os_type VARCHAR(40) NULL AFTER priority', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @fk := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'service_orders' AND CONSTRAINT_NAME = 'fk_so_assigned');
SET @sql := IF(@fk = 0,
  'ALTER TABLE service_orders ADD CONSTRAINT fk_so_assigned FOREIGN KEY (assigned_user_id) REFERENCES users (id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @fk := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'service_orders' AND CONSTRAINT_NAME = 'fk_so_quote');
SET @sql := IF(@fk = 0,
  'ALTER TABLE service_orders ADD CONSTRAINT fk_so_quote FOREIGN KEY (quote_id) REFERENCES quotes (id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

CREATE TABLE IF NOT EXISTS service_order_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  service_order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NULL,
  service_id BIGINT UNSIGNED NULL,
  description VARCHAR(500) NULL,
  qty DECIMAL(14,4) NOT NULL DEFAULT 1.0000,
  unit_price DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  line_discount DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  line_total DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (id),
  KEY idx_soi_so (service_order_id),
  CONSTRAINT fk_soi_so FOREIGN KEY (service_order_id) REFERENCES service_orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_soi_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL,
  CONSTRAINT fk_soi_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
