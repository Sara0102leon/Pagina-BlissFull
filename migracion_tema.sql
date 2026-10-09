-- =====================================================================
-- Fase 1: Motor de tema + orden del menú (idempotente)
-- BD objetivo: tacomenu (MariaDB 10.4)
-- =====================================================================

-- 1) Orden del menú editable en categorías
ALTER TABLE category ADD COLUMN IF NOT EXISTS position int NOT NULL DEFAULT 0;
UPDATE category SET position = id WHERE position = 0;

-- 2) Claves de tema (colores / tipografías) y visibilidad de secciones
-- kind: 0=texto  2=si/no  3=color
INSERT IGNORE INTO configuration (name, label, kind, val, cfg_id) VALUES
('tema_primary',       'Color principal',              3, '#e0a96d', 1),
('tema_primary_2',     'Color principal oscuro',       3, '#b87e38', 1),
('tema_accent',        'Color de acento (botones)',    3, '#ffb703', 1),
('tema_red',           'Color de alerta / oferta',     3, '#e63946', 1),
('tema_red_2',         'Color de alerta intenso',      3, '#ff2a2a', 1),
('tema_bg',            'Fondo principal',              3, '#000000', 1),
('tema_bg_2',          'Fondo secundario',             3, '#050505', 1),
('tema_card',          'Fondo de tarjetas',            3, '#050505', 1),
('tema_card_2',        'Fondo tarjeta destacada',      3, '#1a0004', 1),
('tema_text',          'Color de texto',               3, '#ffffff', 1),
('tema_muted',         'Texto secundario (rgba)',      0, 'rgba(255,255,255,0.62)', 1),
('tema_border',        'Color de bordes (rgba)',       0, 'rgba(184,126,56,0.35)', 1),
('tema_font_display',  'Tipografía de títulos',        0, 'Bebas Neue', 1),
('tema_font_hand',     'Tipografía cursiva',           0, 'Caveat', 1),
('tema_font_body',     'Tipografía de texto',          0, 'Inter', 1),
('menu_show_hero',     'Mostrar sección hero',         2, '1', 1),
('menu_show_slides',   'Mostrar carrusel de ofertas',  2, '1', 1),
('menu_show_featured', 'Mostrar productos destacados', 2, '1', 1),
('menu_show_bebidas',  'Mostrar sección de bebidas',   2, '1', 1),
('menu_show_sedes',    'Mostrar sedes / ubicación',    2, '1', 1),
('menu_cat_icons',     'Iconos en chips de categorías',2, '1', 1);