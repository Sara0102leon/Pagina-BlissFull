-- =====================================================================
-- Fase 2: panel "Apariencia / Menú"
--   * Imagen por categoría (columna category.image)
--   * Logo propio del negocio (tema_logo / tema_logo_alt)
--   * Sección de ofertas (toggle menu_show_ofertas)
-- Aditiva e idempotente; NADA se borra.
-- =====================================================================

-- 1) Imagen de categoría -------------------------------------------------
ALTER TABLE category ADD COLUMN IF NOT EXISTS image varchar(255) NULL;

-- 2) Claves de configuración base (cfg_id=1). INSERT IGNORE por el UNIQUE(name,cfg_id).
INSERT IGNORE INTO configuration (name,label,kind,val,cfg_id) VALUES
 ('tema_logo','Logo propio del negocio (menú público)',1,'',1),
 ('tema_logo_alt','Texto alternativo del logo',1,'',1),
 ('menu_show_ofertas','Sección de ofertas',2,'1',1);
