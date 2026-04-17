-- Nome exibido no sistema (separado da razão social)
-- Requer MySQL 8.0.29+ para IF NOT EXISTS em ADD COLUMN; em versões antigas, execute o ALTER manualmente uma vez.
SET NAMES utf8mb4;

ALTER TABLE company_profiles ADD COLUMN IF NOT EXISTS app_title VARCHAR(255) NULL AFTER company_id;
