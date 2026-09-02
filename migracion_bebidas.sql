-- MIGRACION: MODULO DE BEBIDAS (solo para pizzas gigantes)
-- Aplicar en produccion:  mariadb -u root tacomenu < migracion_bebidas.sql

-- Tabla independiente de bebidas (no toca product_extra ni los cupos gratis)
CREATE TABLE IF NOT EXISTS bebida (
	id int not null auto_increment primary key,
	sabor varchar(120) not null,
	medida varchar(80) not null default '',
	precio decimal(10,2) not null default 0.00,
	is_active tinyint not null default 1,
	unique key uq_bebida (sabor, medida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Guardar las bebidas elegidas en cada linea del pedido, separadas de los extras
ALTER TABLE buy_product ADD COLUMN bebidas varchar(500) NULL AFTER extras;

-- V2: refresco gratis en pizzas gigantes
-- sabor_options = sabores disponibles (separados por coma), es_gratis = bebida incluida en el precio
ALTER TABLE bebida ADD COLUMN sabor_options varchar(500) NULL AFTER medida;
ALTER TABLE bebida ADD COLUMN es_gratis tinyint(1) NOT NULL DEFAULT 0 AFTER precio;

-- Valor de la bebida que va "incluida gratis" con la pizza gigante.
-- La diferencia a pagar de cualquier otra bebida = precio - bebida_base
INSERT INTO configuration (name, label, val) VALUES ('bebida_base', 'Valor base del refresco gratis (USD)', '1')
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- Catalogo (aplicable sobre la migracion original o sobre una BD ya poblada)
INSERT INTO bebida (sabor, medida, sabor_options, precio, es_gratis, is_active) VALUES
("Coca-Cola", "1.5 Litros", "", 3.00, 0, 1),
("Coca-Cola", "2 Litros", "", 4.00, 0, 1),
("Up7", "1.5 Litros", "", 1.00, 1, 1),
("Golden", "1.5 Litros", "Uva,Piña,Kolita,Manzanita,Naranja", 1.00, 1, 1),
("Coca-Cola", "1 Litro", "", 2.50, 0, 1)
ON DUPLICATE KEY UPDATE sabor_options = VALUES(sabor_options), precio = VALUES(precio), es_gratis = VALUES(es_gratis), is_active = VALUES(is_active);

-- Quitar del catalogo las bebidas que ya no se ofrecen (se elimina solo por sabor+medida)
DELETE FROM bebida WHERE sabor = 'Agua Mineral';
DELETE FROM bebida WHERE sabor = 'Sprite';