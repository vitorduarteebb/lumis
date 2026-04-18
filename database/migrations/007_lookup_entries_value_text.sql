-- Texto longo para modelos de observação em lookup_entries (opções auxiliares)
SET NAMES utf8mb4;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lookup_entries' AND COLUMN_NAME = 'value_text');
SET @sql := IF(@c = 0, 'ALTER TABLE lookup_entries ADD COLUMN value_text TEXT NULL AFTER slug', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
