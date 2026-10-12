-- =====================================================================
-- Fase 3: tokens de tema adicionales (aditiva e idempotente)
-- BD objetivo: tacomenu (MariaDB 10.4)
--   Depende de: migracion_config_tienda.sql (indice UNIQUE(name,cfg_id))
-- =====================================================================

-- Superficie alterna (--tt-bg-3): tarjetas de sede, <option> de selects,
-- cuerpo de modales y otros fondos que antes estaban hardcodeados (#0a0a0a).
-- La fila base (cfg_id=1) sirve de plantilla; cada tienda puede sobreescribirla.
INSERT INTO configuration (name, label, kind, val, cfg_id)
VALUES ('tema_bg_3', 'Fondo alterno', 3, '#0a0a0a', 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), kind = VALUES(kind);
