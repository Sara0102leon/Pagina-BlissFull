-- migracion_config_tienda.sql
-- Configuración por tienda (multi-tenant). Aditiva e idempotente.
--
-- Antes: `configuration.name` era UNIQUE global -> todas las tiendas compartían
-- una sola fila por clave (la base, cfg_id=1).
-- Ahora: UNIQUE(name, cfg_id) -> cada tienda puede tener su propia fila por clave,
-- heredando de la base (cfg_id=1) cuando no la define.
--
-- Aplicar:  mysql -u root tacomenu < migracion_config_tienda.sql

-- 1) Quitar el UNIQUE global sobre `name` (solo si existe)
SET @has_name_uni := (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'configuration'
    AND INDEX_NAME = 'name' AND NON_UNIQUE = 0);
SET @sql := IF(@has_name_uni > 0, 'ALTER TABLE configuration DROP INDEX name', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) UNIQUE compuesto (name, cfg_id) (solo si no existe)
SET @has_comp := (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'configuration'
    AND INDEX_NAME = 'name_cfg');
SET @sql := IF(@has_comp = 0, 'ALTER TABLE configuration ADD UNIQUE KEY name_cfg (name, cfg_id)', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3) Normalizar cfg_id
ALTER TABLE configuration MODIFY cfg_id int(11) NOT NULL DEFAULT 1;

-- 4) Claves base de la "carta" imprimible (editables por tienda)
INSERT INTO configuration (name, label, kind, val, cfg_id) VALUES
  ('carta_titulo',             'Carta - Titulo',                1, '',  1),
  ('carta_subtitulo',          'Carta - Subtitulo',             1, '',  1),
  ('carta_mostrar_logo',       'Carta - Mostrar logo (1/0)',    2, '1', 1),
  ('carta_mostrar_precios',    'Carta - Mostrar precios (1/0)', 2, '1', 1),
  ('carta_mostrar_descripcion','Carta - Mostrar descripcion (1/0)', 2, '1', 1),
  ('carta_mostrar_imagenes',   'Carta - Mostrar imagenes (1/0)',2, '0', 1),
  ('carta_columnas',           'Carta - Columnas (1/2)',        2, '2', 1),
  ('carta_nota',               'Carta - Nota al pie',           1, '',  1)
ON DUPLICATE KEY UPDATE label = VALUES(label), kind = VALUES(kind);
