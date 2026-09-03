CREATE TABLE IF NOT EXISTS sede (
	id int not null auto_increment primary key,
	name varchar(200) not null,
	address varchar(500),
	phone varchar(20) not null,
	is_active boolean default 1,
	created_at datetime default current_timestamp
);

INSERT INTO sede (name, address, phone) VALUES
("Blissfull Villa Roca", "Villa Roca (sucursal principal)", "+584120000001"),
("Blissfull Cabudare", "Cabudare (sucursal)", "+584120000002"),
("Blissfull Agua Viva", "Agua Viva (sucursal)", "+584120000003");

ALTER TABLE buy ADD COLUMN sede_id int AFTER delivery_zone_id;
ALTER TABLE buy ADD CONSTRAINT fk_buy_sede FOREIGN KEY (sede_id) REFERENCES sede(id);

-- HORARIOS POR SEDE + PEDIDOS PROGRAMADOS
ALTER TABLE sede ADD COLUMN horario_open TIME NULL DEFAULT NULL AFTER phone;
ALTER TABLE sede ADD COLUMN horario_close TIME NULL DEFAULT NULL AFTER horario_open;
ALTER TABLE buy ADD COLUMN scheduled_at DATETIME NULL DEFAULT NULL AFTER note;