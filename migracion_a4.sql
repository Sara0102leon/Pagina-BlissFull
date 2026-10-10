-- =====================================================================
-- A4: orden manual del menú (drag & drop en el admin)
-- Agrega `product.position` para ordenar productos dentro de su categoría.
-- Aditiva e idempotente; NADA se borra.
--
-- Notas:
--  * NO se inicializan posiciones: todas quedan en 0 y el orden resultante
--    es `position asc, created_at desc`, idéntico al comportamiento previo.
--    Al reordenar en el panel se asignan posiciones explícitas (0..n-1),
--    de modo que re-ejecutar esta migración nunca pisa un orden guardado.
--  * El orden de CATEGORÍAS ya existe (`category.position`).
-- =====================================================================

ALTER TABLE product ADD COLUMN IF NOT EXISTS position int NOT NULL DEFAULT 0;

-- Índice para el orden por tienda+categoría+posición (idempotente)
SET @tbl := 'product'; SET @idx := 'idx_product_order'; SET @n := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=@tbl AND index_name=@idx);
SET @s := IF(@n=0, 'ALTER TABLE `product` ADD KEY `idx_product_order` (`tienda_id`,`category_id`,`position`)', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
