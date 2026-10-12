-- =====================================================================
-- Fase 4: funciones de menú (aditiva e idempotente)
-- BD objetivo: tacomenu (MariaDB 10.4)
--   Depende de: migracion_config_tienda.sql (indice UNIQUE(name,cfg_id))
--               migracion_multitenant.sql (product.tienda_id)
--
-- Contenido:
--   1) Etiquetas/badges por producto (product.badge, product.badge_color)
--   2) Oferta por franja horaria (product.offer_from, product.offer_to)
--   3) Interruptores de funciones interactivas (configuration: menu_show_*)
--
-- "Agotado" NO añade columna: reutiliza product.in_existence (0 = agotado).
-- Los interruptores nacen en "0" (apagados) y cfg_id=1 es la plantilla base;
-- activarlos no cambia el menú hasta marcarlos en Apariencia.
-- =====================================================================

ALTER TABLE product ADD COLUMN IF NOT EXISTS badge VARCHAR(40) NULL;
ALTER TABLE product ADD COLUMN IF NOT EXISTS badge_color VARCHAR(20) NULL;
ALTER TABLE product ADD COLUMN IF NOT EXISTS offer_from TIME NULL;
ALTER TABLE product ADD COLUMN IF NOT EXISTS offer_to TIME NULL;

INSERT INTO configuration (name, label, kind, val, cfg_id)
VALUES ('menu_show_favs', 'Favoritos en el menú', 2, '0', 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), kind = VALUES(kind);

INSERT INTO configuration (name, label, kind, val, cfg_id)
VALUES ('menu_show_share', 'Compartir producto', 2, '0', 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), kind = VALUES(kind);

INSERT INTO configuration (name, label, kind, val, cfg_id)
VALUES ('menu_show_listmode', 'Vista de lista compacta', 2, '0', 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), kind = VALUES(kind);
