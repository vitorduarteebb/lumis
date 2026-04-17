-- Nome exibido no sistema (separado da razão social)
-- Compatível com MySQL 5.7+ e MariaDB (sem ADD COLUMN IF NOT EXISTS)
SET NAMES utf8mb4;

SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company_profiles' AND COLUMN_NAME = 'app_title');
SET @sql := IF(@exist = 0, 'ALTER TABLE company_profiles ADD COLUMN app_title VARCHAR(255) NULL AFTER company_id', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
