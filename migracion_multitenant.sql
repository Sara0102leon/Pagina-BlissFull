-- =====================================================================
-- A0: arquitectura multi-tenant (fase de datos)
-- Agrega tienda_id a las tablas de negocio para escopar por cliente.
-- Aditiva e idempotente; NADA se borra.
-- =====================================================================

ALTER TABLE category     ADD COLUMN IF NOT EXISTS tienda_id int NULL;
ALTER TABLE product      ADD COLUMN IF NOT EXISTS tienda_id int NULL;
ALTER TABLE sede         ADD COLUMN IF NOT EXISTS tienda_id int NULL;
ALTER TABLE buy          ADD COLUMN IF NOT EXISTS tienda_id int NULL;
ALTER TABLE delivery_zone ADD COLUMN IF NOT EXISTS tienda_id int NULL;

-- Índices para el filtro por tienda (guardado con información_schema para ser idempotente)
SET @tbl := 'category'; SET @idx := 'idx_category_tienda'; SET @col := 'tienda_id';
SET @n := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=@tbl AND index_name=@idx);
SET @s := IF(@n=0, CONCAT('ALTER TABLE `', @tbl, '` ADD KEY `', @idx, '` (`', @col, '`)'), 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @tbl := 'product'; SET @idx := 'idx_product_tienda'; SET @col := 'tienda_id';
SET @n := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=@tbl AND index_name=@idx);
SET @s := IF(@n=0, CONCAT('ALTER TABLE `', @tbl, '` ADD KEY `', @idx, '` (`', @col, '`)'), 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @tbl := 'sede'; SET @idx := 'idx_sede_tienda'; SET @col := 'tienda_id';
SET @n := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=@tbl AND index_name=@idx);
SET @s := IF(@n=0, CONCAT('ALTER TABLE `', @tbl, '` ADD KEY `', @idx, '` (`', @col, '`)'), 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @tbl := 'buy'; SET @idx := 'idx_buy_tienda'; SET @col := 'tienda_id';
SET @n := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=@tbl AND index_name=@idx);
SET @s := IF(@n=0, CONCAT('ALTER TABLE `', @tbl, '` ADD KEY `', @idx, '` (`', @col, '`)'), 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @tbl := 'delivery_zone'; SET @idx := 'idx_dz_tienda'; SET @col := 'tienda_id';
SET @n := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=@tbl AND index_name=@idx);
SET @s := IF(@n=0, CONCAT('ALTER TABLE `', @tbl, '` ADD KEY `', @idx, '` (`', @col, '`)'), 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- Dominios adicionales cuyos subdominios se resuelven como tiendas (separados por espacio)
INSERT IGNORE INTO configuration (name,label,kind,val) VALUES
('saas_alt_domains','Dominios altos de tiendas (separados por espacio)',1,'prueba.alianzablissful.com');