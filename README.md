# Alianza Blissfull — Menú digital (plataforma SaaS)

Aplicación PHP + MariaDB para menús digitales de restaurantes. Nació como el menú
de una pizzería y evoluciona hacia una **plataforma multi-cliente tipo OlaClick**:
el cliente se registra, paga un plan y obtiene su menú en un subdominio propio.
El roadmap de producto está en [`PLAN.md`](PLAN.md) y las notas técnicas y de
arquitectura en [`AGENTS.md`](AGENTS.md).

## Estado

- **Menú público + panel admin** funcionando sobre la BD `tacomenu`.
- **Fase 1** completada: motor de tema configurable (variables CSS `--tt-*`) y orden
  del menú editable desde admin.
- **A1** en marcha: landing de marketing (`landing/`) con demo del menú y flujo de
  pago (mock Zenobank).

## Requisitos

- PHP 8.2+ con `mysqli`
- MariaDB / MySQL 10.4+
- (Opcional) Apache/XAMPP o nginx para servirlo

No usa Composer, npm ni build tools: PHP puro con un framework MVC propio.

## Puesta en marcha

1. Copia el proyecto en el directorio web (p. ej. `C:\xampp\htdocs\menu`) o crea un
   *junction* que apunte a este repo.
2. Crea la base de datos e importa el esquema:
   `schema.sql` es el esquema canónico (DDL + datos de ejemplo).
   > **Cuidado:** `schema.sql` empieza con `create database` y contiene `DROP`, así que
   > **no** lo re-importes sobre una base de datos viva. Para cambios incrementales usa
   > las migraciones `migracion_*.sql` (idempotentes).
3. Ajusta las credenciales de BD en `admin/core/controller/Database.php`
   (por defecto: `root` sin contraseña, base `tacomenu`, `localhost`).
4. Accede:
   - Menú público: `http://localhost/menu/`
   - Panel admin: `http://localhost/menu/admin/` — usuario `admin` / password `admin`

## Estructura

```
index.php                 entry point del sitio público
admin/                    panel de administración (entry point propio)
  index.php
  core/app/model/         22 modelos, compartidos por público y admin
  core/app/action/        procesamiento de formularios
  core/app/view/          vistas del panel
  core/controller/        Database.php (conexión real)
  storage/                imágenes de productos, sedes, slides y sonidos
core/
  app/layouts/            shell del sitio público (inyecta el tema)
  app/view/               vistas públicas (index, grid de productos)
  app/helpers/            helpers (extras de producto)
  controller/             autoloader y utilidades
assets/                   CSS y JS del tema público
migracion_*.sql           migraciones incrementales (aditivas e idempotentes)
schema.sql                esquema canónico de la base de datos
landing/                  landing de marketing (sitio estático)
```

## Convenciones

- **Contrato invisible:** no renombrar ni eliminar identificadores usados por AJAX/JS
  (`#btn_confirm_order`, `#product_search`, `#grid-title`, `.btn-category-ajax`,
  `data-cat`, `openExtrasModal`, `$_SESSION["cart"]`, `blissfull_sede_id`).
- Validación manual: `php -l archivo.php` para comprobar sintaxis. No hay CI ni tests.
- Las migraciones son **aditivas e idempotentes**; nunca se toca `schema.sql`.

## Licencia

Uso privado. Basado en el framework Lb/Legobox (Evilnapsis).
