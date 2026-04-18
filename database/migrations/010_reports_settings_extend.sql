-- Relatórios (metadados) + extensões de configurações — aplicar uma vez
SET NAMES utf8mb4;

-- company_profiles: fantasia, site, moeda, paginação, favicon
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company_profiles' AND COLUMN_NAME = 'trade_name');
SET @sql := IF(@c = 0, 'ALTER TABLE company_profiles ADD COLUMN trade_name VARCHAR(255) NULL AFTER legal_name', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company_profiles' AND COLUMN_NAME = 'website');
SET @sql := IF(@c = 0, 'ALTER TABLE company_profiles ADD COLUMN website VARCHAR(500) NULL AFTER email', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company_profiles' AND COLUMN_NAME = 'default_currency');
SET @sql := IF(@c = 0, 'ALTER TABLE company_profiles ADD COLUMN default_currency VARCHAR(10) NOT NULL DEFAULT ''BRL'' AFTER locale', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company_profiles' AND COLUMN_NAME = 'default_page_size');
SET @sql := IF(@c = 0, 'ALTER TABLE company_profiles ADD COLUMN default_page_size INT UNSIGNED NOT NULL DEFAULT 15 AFTER default_currency', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company_profiles' AND COLUMN_NAME = 'display_name');
SET @sql := IF(@c = 0, 'ALTER TABLE company_profiles ADD COLUMN display_name VARCHAR(255) NULL AFTER app_title', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company_profiles' AND COLUMN_NAME = 'favicon_path');
SET @sql := IF(@c = 0, 'ALTER TABLE company_profiles ADD COLUMN favicon_path VARCHAR(500) NULL AFTER logo_path', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- digital_certificates: tipo, observações, senha cifrada (opcional)
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'digital_certificates' AND COLUMN_NAME = 'cert_type');
SET @sql := IF(@c = 0, 'ALTER TABLE digital_certificates ADD COLUMN cert_type VARCHAR(40) NULL DEFAULT ''A1'' AFTER label', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'digital_certificates' AND COLUMN_NAME = 'notes');
SET @sql := IF(@c = 0, 'ALTER TABLE digital_certificates ADD COLUMN notes TEXT NULL AFTER status', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'digital_certificates' AND COLUMN_NAME = 'password_encrypted');
SET @sql := IF(@c = 0, 'ALTER TABLE digital_certificates ADD COLUMN password_encrypted VARCHAR(768) NULL AFTER file_path', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- notificações por e-mail: template vinculado
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'email_notification_settings' AND COLUMN_NAME = 'template_id');
SET @sql := IF(@c = 0, 'ALTER TABLE email_notification_settings ADD COLUMN template_id BIGINT UNSIGNED NULL AFTER event_key', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @fk := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'email_notification_settings' AND CONSTRAINT_NAME = 'fk_ens_template');
SET @sql := IF(@fk = 0,
  'ALTER TABLE email_notification_settings ADD CONSTRAINT fk_ens_template FOREIGN KEY (template_id) REFERENCES email_templates (id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
