-- Base para emissão fiscal real (NF-e / NFC-e / NFS-e) + cadastros fiscais — idempotente
SET NAMES utf8mb4;

-- ===================== company_fiscal_settings =====================
CREATE TABLE IF NOT EXISTS company_fiscal_settings (
  company_id BIGINT UNSIGNED NOT NULL,
  store_id BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = padrão da empresa (todas as lojas)',
  tp_amb TINYINT UNSIGNED NOT NULL DEFAULT 2 COMMENT '1=produção 2=homologação',
  crt TINYINT UNSIGNED NULL COMMENT 'Código regime tributário (0-4)',
  issuer_ibge_city_code INT UNSIGNED NULL,
  default_series_nfe VARCHAR(3) NULL,
  default_series_nfce VARCHAR(3) NULL,
  nfce_csc_id VARCHAR(10) NULL,
  nfce_csc_encrypted TEXT NULL COMMENT 'CSC cifrado (APP_KEY)',
  active_digital_certificate_id BIGINT UNSIGNED NULL,
  nfse_integration_mode VARCHAR(24) NOT NULL DEFAULT 'driver' COMMENT 'driver|national|disabled',
  nfse_endpoint VARCHAR(512) NULL,
  nfse_city_ibge INT UNSIGNED NULL,
  nfse_extra_json JSON NULL,
  reform_ready TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Base CBS/IBS leiaute futuro',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (company_id, store_id),
  KEY idx_cfs_cert (active_digital_certificate_id),
  CONSTRAINT fk_cfs_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @cfk := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'company_fiscal_settings' AND CONSTRAINT_NAME = 'fk_cfs_digital_cert');
SET @sql := IF(@cfk = 0 AND EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'digital_certificates'),
  'ALTER TABLE company_fiscal_settings ADD CONSTRAINT fk_cfs_digital_cert FOREIGN KEY (active_digital_certificate_id) REFERENCES digital_certificates (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ===================== fiscal_series =====================
CREATE TABLE IF NOT EXISTS fiscal_series (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  store_id BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = todas/padrão',
  fiscal_model SMALLINT UNSIGNED NOT NULL COMMENT '55 NFe 65 NFCe 1 NFSe placeholder',
  tp_amb TINYINT UNSIGNED NOT NULL DEFAULT 2,
  series VARCHAR(4) NOT NULL DEFAULT '1',
  last_number INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_fs_company_store_model_amb_series (company_id, store_id, fiscal_model, tp_amb, series),
  KEY idx_fs_company (company_id),
  CONSTRAINT fk_fs_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== fiscal_transmission_logs =====================
CREATE TABLE IF NOT EXISTS fiscal_transmission_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  fiscal_document_id BIGINT UNSIGNED NULL,
  phase VARCHAR(32) NOT NULL COMMENT 'validate,sign,transmit,consult,event,cancel',
  endpoint VARCHAR(512) NULL,
  http_status SMALLINT NULL,
  request_payload MEDIUMTEXT NULL,
  response_payload MEDIUMTEXT NULL,
  error_message TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ftl_doc (fiscal_document_id),
  KEY idx_ftl_company_created (company_id, created_at),
  CONSTRAINT fk_ftl_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_ftl_fiscal_doc FOREIGN KEY (fiscal_document_id) REFERENCES fiscal_documents (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== fiscal_document_events =====================
CREATE TABLE IF NOT EXISTS fiscal_document_events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  fiscal_document_id BIGINT UNSIGNED NOT NULL,
  event_type VARCHAR(32) NOT NULL COMMENT 'authorization,cancellation,ccr,inutilization,nfse_issue...',
  protocol VARCHAR(80) NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'registered',
  payload_path VARCHAR(512) NULL,
  response_path VARCHAR(512) NULL,
  metadata_json JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_fde_doc (fiscal_document_id),
  CONSTRAINT fk_fde_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE,
  CONSTRAINT fk_fde_fiscal_doc FOREIGN KEY (fiscal_document_id) REFERENCES fiscal_documents (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== fiscal_documents — colunas emissão =====================
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_documents' AND COLUMN_NAME = 'fiscal_model');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_documents ADD COLUMN fiscal_model SMALLINT UNSIGNED NULL COMMENT ''55/65/NFSe'' AFTER document_kind', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_documents' AND COLUMN_NAME = 'tp_amb');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_documents ADD COLUMN tp_amb TINYINT UNSIGNED NULL AFTER fiscal_model', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_documents' AND COLUMN_NAME = 'authorization_protocol');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_documents ADD COLUMN authorization_protocol VARCHAR(60) NULL AFTER access_key', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_documents' AND COLUMN_NAME = 'sefaz_receipt_number');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_documents ADD COLUMN sefaz_receipt_number VARCHAR(60) NULL AFTER authorization_protocol', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_documents' AND COLUMN_NAME = 'sefaz_status_code');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_documents ADD COLUMN sefaz_status_code VARCHAR(8) NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_documents' AND COLUMN_NAME = 'sefaz_reason');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_documents ADD COLUMN sefaz_reason TEXT NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_documents' AND COLUMN_NAME = 'xml_signed_path');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_documents ADD COLUMN xml_signed_path VARCHAR(512) NULL AFTER pdf_path', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_documents' AND COLUMN_NAME = 'xml_authorized_path');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_documents ADD COLUMN xml_authorized_path VARCHAR(512) NULL AFTER xml_signed_path', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_documents' AND COLUMN_NAME = 'reform_tax_json');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_documents ADD COLUMN reform_tax_json JSON NULL COMMENT ''CBS/IBS placeholders''', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_documents' AND COLUMN_NAME = 'cancel_protocol');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_documents ADD COLUMN cancel_protocol VARCHAR(60) NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_documents' AND COLUMN_NAME = 'cancelled_at');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_documents ADD COLUMN cancelled_at DATETIME NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Ampliar status para novos fluxos
SET @c := (SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_documents' AND COLUMN_NAME = 'status');
SET @sql := IF(@c IS NOT NULL AND @c < 32, 'ALTER TABLE fiscal_documents MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT ''draft''', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ===================== fiscal_document_lines — itens fiscais =====================
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_document_lines' AND COLUMN_NAME = 'ncm');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_document_lines ADD COLUMN ncm VARCHAR(10) NULL AFTER description', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_document_lines' AND COLUMN_NAME = 'cfop');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_document_lines ADD COLUMN cfop VARCHAR(5) NULL AFTER ncm', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_document_lines' AND COLUMN_NAME = 'origin');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_document_lines ADD COLUMN origin TINYINT UNSIGNED NULL COMMENT ''0-8'' AFTER cfop', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_document_lines' AND COLUMN_NAME = 'cst_icms');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_document_lines ADD COLUMN cst_icms VARCHAR(4) NULL AFTER origin', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_document_lines' AND COLUMN_NAME = 'csosn');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_document_lines ADD COLUMN csosn VARCHAR(4) NULL AFTER cst_icms', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_document_lines' AND COLUMN_NAME = 'cest');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_document_lines ADD COLUMN cest VARCHAR(10) NULL AFTER csosn', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_document_lines' AND COLUMN_NAME = 'ean');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_document_lines ADD COLUMN ean VARCHAR(16) NULL AFTER cest', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fiscal_document_lines' AND COLUMN_NAME = 'tax_payload_json');
SET @sql := IF(@c = 0, 'ALTER TABLE fiscal_document_lines ADD COLUMN tax_payload_json JSON NULL AFTER line_total', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ===================== clients =====================
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clients' AND COLUMN_NAME = 'ibge_city_code');
SET @sql := IF(@c = 0, 'ALTER TABLE clients ADD COLUMN ibge_city_code INT UNSIGNED NULL AFTER state', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clients' AND COLUMN_NAME = 'ind_ie_dest');
SET @sql := IF(@c = 0, 'ALTER TABLE clients ADD COLUMN ind_ie_dest TINYINT UNSIGNED NULL COMMENT ''NF-e indIEDest'' AFTER ibge_city_code', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ===================== products =====================
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'ncm');
SET @sql := IF(@c = 0, 'ALTER TABLE products ADD COLUMN ncm VARCHAR(10) NULL AFTER barcode', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'cfop_default');
SET @sql := IF(@c = 0, 'ALTER TABLE products ADD COLUMN cfop_default VARCHAR(5) NULL AFTER ncm', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'origin');
SET @sql := IF(@c = 0, 'ALTER TABLE products ADD COLUMN origin TINYINT UNSIGNED NULL COMMENT ''0-8'' AFTER cfop_default', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'cst_icms');
SET @sql := IF(@c = 0, 'ALTER TABLE products ADD COLUMN cst_icms VARCHAR(4) NULL AFTER origin', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'csosn');
SET @sql := IF(@c = 0, 'ALTER TABLE products ADD COLUMN csosn VARCHAR(4) NULL AFTER cst_icms', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'cest');
SET @sql := IF(@c = 0, 'ALTER TABLE products ADD COLUMN cest VARCHAR(10) NULL AFTER csosn', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ===================== services =====================
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'municipal_service_code');
SET @sql := IF(@c = 0, 'ALTER TABLE services ADD COLUMN municipal_service_code VARCHAR(20) NULL AFTER category', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'lc116_item');
SET @sql := IF(@c = 0, 'ALTER TABLE services ADD COLUMN lc116_item VARCHAR(10) NULL AFTER municipal_service_code', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'fiscal_notes');
SET @sql := IF(@c = 0, 'ALTER TABLE services ADD COLUMN fiscal_notes VARCHAR(500) NULL AFTER lc116_item', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
