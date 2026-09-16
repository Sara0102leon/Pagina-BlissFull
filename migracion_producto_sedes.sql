-- Migración: producto disponible en varias sedes
-- Ejecutar:  mysql -uroot tacomenu < migracion_producto_sedes.sql

-- Tabla pivote product <-> sede
CREATE TABLE IF NOT EXISTS product_sede (
  product_id INT NOT NULL,
  sede_id INT NOT NULL,
  PRIMARY KEY (product_id, sede_id),
  KEY idx_ps_sede (sede_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Conversión de los productos de sede única actuales a la nueva lógica:
--   sede_id NULL = todas las sedes
--   sede_id 0    = solo en las sedes de product_sede
--   sede_id > 0  = solo en esa sede (legacy, se conserva)
-- (no se migran datos: los productos con sede_id>0 se copian al pivote en la
--  próxima edición; los que ya están en varias sedes se agregan manualmente)