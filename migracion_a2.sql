-- =====================================================================
-- A2: la pizzería actual = primer cliente de la plataforma (tienda 1)
-- Crea la tienda por defecto y estampa tienda_id=1 en los datos actuales.
-- Idempotente; NADA se borra.
-- =====================================================================

-- Tienda por defecto (su menú se sirve en el dominio raíz y en subdominio principal.*)
-- Se fija id=1: la tienda por defecto es SIEMPRE la #1 y los datos se estampan contra ella.
INSERT INTO tienda (id, subdomain, nombre, descripcion, estatus, zeno_mode) VALUES
(1, 'principal', 'Menú principal', 'Primer cliente: el menú de la pizzería actual', 'activa', 'demo')
ON DUPLICATE KEY UPDATE subdomain='principal', nombre='Menú principal', estatus='activa';

-- Estampar los datos actuales como tienda 1
UPDATE category      SET tienda_id=1 WHERE tienda_id IS NULL;
UPDATE product       SET tienda_id=1 WHERE tienda_id IS NULL;
UPDATE sede          SET tienda_id=1 WHERE tienda_id IS NULL;
UPDATE buy           SET tienda_id=1 WHERE tienda_id IS NULL;
UPDATE delivery_zone SET tienda_id=1 WHERE tienda_id IS NULL;

-- El admin actual pasa a ser el dueño de la tienda 1
UPDATE user SET tienda_id=1, role='dueno' WHERE (id=1 OR username='admin') AND (tienda_id IS NULL OR tienda_id=0);