-- ============================================================================
-- migracion_planes_ofertas.sql — Planes editables + OFERTAS con vigencia
-- + visitas por tienda (métricas del super-admin).  Octubre 2026.
--
-- Aditiva e idempotente. Se puede re-ejecutar sin perder datos:
--   * columnas -> ADD COLUMN IF NOT EXISTS
--   * tabla    -> CREATE TABLE IF NOT EXISTS
--   * semilla  -> UPDATE ... WHERE (sólo si nadie configuró la oferta)
--
-- Aplicar:  mariadb -u root tacomenu < migracion_planes_ofertas.sql
-- ============================================================================

-- 1) Copia del plan + oferta con vigencia (todo opcional/NULL) -----------
ALTER TABLE plan ADD COLUMN IF NOT EXISTS description  VARCHAR(160) NULL;
ALTER TABLE plan ADD COLUMN IF NOT EXISTS offer_price   DECIMAL(10,2) NULL;
ALTER TABLE plan ADD COLUMN IF NOT EXISTS offer_from    DATE NULL;
ALTER TABLE plan ADD COLUMN IF NOT EXISTS offer_to      DATE NULL;
ALTER TABLE plan ADD COLUMN IF NOT EXISTS offer_label   VARCHAR(80)  NULL;

-- 2) Visitas diarias al menú de cada tienda (tiendas más visitadas) ------
CREATE TABLE IF NOT EXISTS tienda_visita (
  id       INT NOT NULL AUTO_INCREMENT,
  tienda_id INT NOT NULL,
  fecha    DATE NOT NULL,
  visitas  INT  NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_visita (tienda_id, fecha),
  KEY idx_visita_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3) Descripción por defecto (la de la landing; sólo si está vacía) ------
UPDATE plan SET description='Para empezar a vender por WhatsApp.'  WHERE code='emprendedor' AND (description IS NULL OR description='');
UPDATE plan SET description='El panel completo para tu negocio.'    WHERE code='restaurante' AND (description IS NULL OR description='');
UPDATE plan SET description='Varias sedes, una sola marca.'        WHERE code='cadena'      AND (description IS NULL OR description='');

-- 4) Oferta de ejemplo (lanza el 19 -> 15 por 30 días).
--    Se aplica UNA sola vez: el marcador `saas_promo_planes` evita que
--    una re-ejecución pise una oferta ya editada/borrada desde el panel.
UPDATE plan
   SET offer_price = 15.00,
       offer_from   = CURDATE(),
       offer_to     = DATE_ADD(CURDATE(), INTERVAL 30 DAY),
       offer_label  = 'Oferta de lanzamiento'
 WHERE code = 'restaurante'
   AND NOT EXISTS (SELECT 1 FROM configuration WHERE name='saas_promo_planes' AND cfg_id=1);

INSERT INTO configuration (name,label,kind,val,cfg_id) VALUES
  ('saas_promo_planes','Oferta de ejemplo ya sembrada',2,'1',1)
ON DUPLICATE KEY UPDATE label=VALUES(label), kind=VALUES(kind);
