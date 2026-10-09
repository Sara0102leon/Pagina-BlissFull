# Blissfull — Notas de proyecto

## PIVOT: Plataforma SaaS de menús digitales (octubre 2026)

El proyecto evolucionó de "menú de una pizzería" a una **plataforma multi-cliente
tipo OlaClick** (`landing/` es la marketing page). Roadmap en `PLAN.md` (A0–A4).

**Decisiones tomadas (usuario):**
- Stack: **extender el PHP/MariaDB actual** (el motor de menú ya validado es la base).
- Multi-tenant: **una sola BD** con campo `tienda_id` en las tablas de negocio; tablas
  nuevas `tienda`, `plan`, `suscripcion`. Subdominios resueltos por **Host header**
  (vhost comodín). Por ahora se desarrolla **en local** (subdominios simulados con
  `Host: cliente.localhost` vía curl).
- Roles: **un solo panel web por rol** (Dueño/Cocina/Repartidor). La pizzería actual
  migra como primer cliente.
- Pagos: el cliente paga en línea con **Zenobank** (`dashboard.zenobank.io`) y es
  redirigido de vuelta con el pago aprobado; el **subdominio se crea automáticamente**.

**Landing (A1):** `landing/` estático servido en `http://localhost/menu/landing/`.
- Brand provisional "sazón" (placeholder, cambiar nombre/colores en
  `landing/index.html`, `landing.css` `:root`). Pocket de capturas QA en
  `AppData\Local\Temp\opencode\`.
- Mockups usan fotos reales de `admin/storage/products/`.
- Sección **"Menú en vivo"** (`#editor`): demo grande con el look del **menú real**
  (categorías como **pills** + tarjetas de producto con **imagen grande y precio** en
  grilla). Drag con **pointer events**, lift + reordenamiento **FLIP**, persistencia en
  localStorage (`sazon_landing_cats_order`, `sazon_landing_prods_order`).
- **FAQ animado**: `<details>` con transición de altura (JS en `landing.js`).
- **Pago demo** (`#pay`): modal que simula el flujo Zenobank (resumen → conectando →
  aprobado). Todos los `.js-pay` lo abren. Es demo/copy; el checkout real es A0/A2.

## Despliegue en producción (VPS alianza)

**Sitio público:** https://prueba.alianzablissful.com

**Stack en el VPS (Ubuntu 24.04, root):**
- nginx 1.24 + PHP-FPM 8.3 (socket `/run/php/php8.3-fpm.sock`)
- MariaDB 10.11 — BD `tacomenu`, root sin password en localhost (compatible con `Database.php`)
- HTTPS Let's Encrypt (certbot + timer de renovación), redirect HTTP→HTTPS

**Rutas clave:**
- Código desplegado: `/var/www/blissfull` (propietario `www-data`). **Editar aquí para cambios en producción.**
- Repo clonado de referencia: `/root/menu/Pagina-BlissFull`
- Virtual host nginx: `/etc/nginx/sites-enabled/prueba.alianzablissful.com`
- Cert: `/etc/letsencrypt/live/prueba.alianzablissful.com/`

**Comandos útiles (VPS):**
```bash
nginx -t && systemctl reload nginx
systemctl reload php8.3-fpm
mariadb -u root tacomenu -e "SHOW COLUMNS FROM product;"   # por defecto, -h localhost usa unix socket
```

**Para probar endpoints con sesión admin (VPS):**
```bash
curl -s -c /tmp/cj.txt -d "email=admin&password=admin" "https://prueba.alianzablissful.com/admin/?action=access&opt=login"
curl -s -b /tmp/cj.txt "https://prueba.alianzablissful.com/admin/"
```

## Desarrollo local (configuración actual — octubre 2026)

**Única fuente de verdad:** `C:\Users\Userr\menu` (repo git clonado, rama `main`).

**Servir SIN sync manual:** hay un junction de directorio `C:\xampp\htdocs\menu` → `C:\Users\Userr\menu`.
Apache sirve el código directamente desde la carpeta del repo. Editar aquí **es** editar lo que sirve Apache.
- Sitio público: `http://localhost/menu/`
- Panel admin: `http://localhost/menu/admin/` (usuario `admin` / password `admin`)

**Verificación del setup (si al abrir el navegador no responde):**
```powershell
# 1. MariaDB 10.4 en 3306 (BD tacomenu)
& C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini --standalone  # si no hay 3306 escuchando
& C:\xampp\mysql\bin\mysql.exe -u root -h 127.0.0.1 -P 3306 -e "SHOW DATABASES;"
# 2. Apache en 80
Start-Process C:\xampp\apache\bin\httpd.exe   # si no hay 80 escuchando
# 3. El junction debe existir
Get-Item C:\xampp\htdocs\menu
```
> Nota: `MySQL80` en el puerto **3307** es un servicio distinto e irrelevante; no tocarlo.

## Revisión del checkout viejo `C:\xampp\htdocs\blissfull` — CONCLUIDA

`C:\xampp\htdocs\blissfull` es un checkout viejo (commit 994aa0c) con **48 archivos
modificados sin commitear**. Se revisó archivo por archivo contra `main` remoto:

- **31 imágenes** (fotos de productos) → idénticas en ambas versiones. Nada que hacer.
- **16 archivos de código** (+641/−378) → **todo ya existe en `main`**, y en forma
  **más completa**. En concreto, `main` ya incluye: soft-delete de clientes,
  columnas nuevas en `ProductData::add()` (normaliza con `num()`, genera código),
  `BuyData` con `delivery_zone_id`/`scheduled_at`, `has_extras`, fallback de
  categorías 5/6 en el grid, badges de "Pedido programado" en sells, campos del
  form admin, `FLOTANTE_HAS_EDIT`, fix de `flotante_tipo`, `sb.desc` en sabores,
  modal "agregado al carrito" con detalle de extras y pedidos programados.
- **Horario por sede:** el local usaba `sede.horario_open`/`horario_close`;
  `main` lo supera con **horario semanal por sede** — tabla `sede_horario`,
  `SedeHorarioData::mapForSede()`, edición en `admin/?view=settings&opt=horarios`
  (action `updhorariosede`), display "Hoy: …" en la hero y detección de cerrado
  (`getSedeClosedState()`, apertura==cierre = cerrado).
- **Conclusión: NADA que portar.** El diff local genera conflicto en 11 archivos
  precisamente porque `main` ya contiene el mismo trabajo con otra forma.

Los PNG `admin/storage/products/keyler.png` / `keyler_1.png` (ausentes en el clon) se
copiaron al repo sin commitear; el resto no aporta valor. Para regenerar el diff
local si alguna vez hace falta:
`git -C C:\xampp\htdocs\blissfull diff --output=htdocs-uncommitted.patch`.

## Arquitectura

PHP puro, sin Composer/npm/build tools. Framework MVC custom ("Lb/Legobox").

**Dos entry points, dos codebases separados:**
- `index.php` → sitio público (carpeta raíz)
- `admin/index.php` → panel admin (carpeta `admin/`)
- Apache sirve desde el junction `C:\xampp\htdocs\menu` apuntando al repo (sin copias duplicadas)

**Routing por query string (sin .htaccess):**
- `?view=X` → carga `core/app/view/X-view.php` dentro del layout
- `?action=X&opt=Y` → ejecuta `core/app/action/X-action.php` (sin layout)
- Sin parámetros → carga `index-view.php`

**Modelos compartidos:** Todos viven en `admin/core/app/model/` y se usan desde ambos
lados. El autoloader SPL los resuelve por nombre (`ProductData` →
`admin/core/app/model/ProductData.php`). La clase `Database.php` real está en
`admin/core/controller/Database.php` (la de `core/controller/` está vacía).

**Models son clases planas** con métodos estáticos (`getAll()`, `getById()`,
`getLike()`) e instancias (`add()`, `update()`, `del()`). SQL raw vía
`Executor::doit($sql)` — sin prepared statements.

## Rediseño "TITO BURGER" (agosto 2026)

Alcance exacto del rediseño — **solo estos archivos fueron modificados, una sola vez**:

1. `assets/css/custom-dark.css` — tema visual completo
2. `core/app/layouts/layout.php` — navbar, offcanvas y footer
3. `core/app/view/index-view.php` — hero y todas las secciones
4. `core/app/view/product-grid-view.php` — tarjeta de producto

## Contrato invisible (NO romper)

Está prohibido renombrar/eliminar estos identificadores (se usan por AJAX/JS):

- `#btn_confirm_order`, `#product_search`, `#grid-title`
- `.btn-category-ajax`, atributo `data-cat`
- `onclick="openExtrasModal(...)"`
- Los loops `foreach` PHP (se recargan por AJAX)
- `$_SESSION["cart"]`, `blissfull_sede_id` (localStorage)

Regla de oro: **nunca mezclar lo dinámico con lo decorativo**.

## Sistema de diseño

- Paleta en variables CSS: `--tt-gold`, `--tt-red`, `--tt-bg`... (fuente única de verdad)
- Fondo de ladrillos = gradientes CSS apilados + dos brillos radiales (NO imagen)
- Google Fonts: Bebas Neue (títulos) y Caveat (cursivas rojas)
- La tarjeta de producto (`tt-product-card`, `pc-price-pill`...) se reutiliza en grid
  (AJAX) y carrusel del index
- Tabler UI framework como base, custom-dark.css lo overridea

## Motor de tema (Fase 1)

- El tema vive en `configuration` como claves `tema_*`; `core/app/layouts/layout.php`
  las inyecta en `<style id="tt-theme-vars">` como `:root { --tt-* }`, overrideando
  `assets/css/custom-dark.css`. Helper `tt_theme_get($name,$def)` con caché estática.
- Colores: primary/primary_2/accent/red/red_2/bg/bg_2/card/card_2/text/muted/border.
  Fuentes: tema_font_display → `--tt-display`, tema_font_hand → `--tt-hand`,
  tema_font_body → `--tt-font-body`.
- Admin: `?view=settings&opt=apariencia` (editar),
  `?action=settings&opt=savetema` (guardar POST), `?action=settings&opt=restoretema`
  (defaults). Los switches `menu_*` persisten "1"/"0" (sin checkbox = "0").
- Secciones del menú controladas por `index-view.php`: los 6 toggles `menu_show_hero/
  slides/featured/bebidas/sedes` + `menu_cat_icons`.
- Orden de menú: `category.position`. Reorden desde `?view=categories&opt=all` con
  `?action=categories&opt=up|down&id=N` (swap de position con prev/next activos;
  `CategoryData::getPrev()`/`getNext()`/`nextPosition()`).

## Base de datos

**MariaDB 10.4.32** en XAMPP (root, sin password, db `tacomenu`, puerto 3306).
El arranque se describe arriba en "Verificación del setup".

Schema canónico: `schema.sql` (DDL + seed). Dump de producción: `database.sql`.
Migración incremental: `migracion_sedes.sql`.
Migraciones del reinicio (Fase 1, aplicadas, aditivas e idempotentes):
`migracion_tema.sql` (`category.position` + claves `tema_*`/`menu_*`) y
`migracion_esquema.sql` (tablas `product_sede`/`bebida`/`sede_horario` + columnas
nuevas de `buy`: notified, chatwoot_*, lat/lng/maps/distance_km).
**No re-importar `schema.sql` sobre la BD viva:** empieza con `create database` y
contiene `DROP` — borraría los datos reales.

**Gotcha recurrente: columnas faltantes.** El código PHP usa columnas que no existen
en la BD real. Siempre comparar:
```
SHOW COLUMNS FROM product;   -- vs lo que usan ProductData::add(), products-view.php, products-action.php
```
Columnas conocidas que han causado este bug: `price_llevar`, `allow_halves`,
`house_ingredients`, `tipo_division`. Tablas enteras que faltaban en la BD viva
y provocaban fatal en `ProductData::getFeatureds()`: `product_sede`, `bebida`,
`sede_horario`. Ya creadas por `migracion_esquema.sql`.

**Contraseña de usuario admin:** `admin`, hash `sha1(md5("admin"))` =
`90b9aa7e25f80cf4f64e990b78a9fc5ebd6cecad`. (AGENTS viejo decía "password" pero el
hash real NO coincide.) Hash débil pero funcional, no cambiar. El login está en
`admin/core/app/action/access-action.php`.

**Login admin por HTTP (para probar endpoints con sesión):**
```powershell
$sess = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$base = "http://localhost/menu/admin"
Invoke-WebRequest -Uri "$base/?action=access&opt=login" -Method POST -Body @{email="admin"; password="admin"} -WebSession $sess -UseBasicParsing -MaximumRedirection 5 | Out-Null
```

## Desarrollo y validación

**No hay composer, npm, tests, lint, typecheck, ni CI.** La validación es manual:

```powershell
# Syntax check PHP
php -l admin\core\app\view\products-view.php
php -l admin\core\app\action\products-action.php
php -l admin\core\app\model\ProductData.php
php -l core\app\view\index-view.php
php -l core\app\view\product-grid-view.php

# Verificar que identificadores críticos siguen existiendo tras cambios
rg "openExtrasModal|btn_confirm_order|btn-category-ajax|data-cat|grid-title" core\app\view\index-view.php
rg "openExtrasModal|btn_confirm_order|btn-category-ajax|data-cat|grid-title" core\app\view\product-grid-view.php

# Verificar columnas de BD vs código
& "C:\xampp\mysql\bin\mysql.exe" -u root -h 127.0.0.1 -P 3306 tacomenu -e "SHOW COLUMNS FROM product;"
# Comparar contra ProductData::add() y products-view.php
```

**Git — varias ramas en juego:** `main` (producción/remoto), `cambios-sara`,
`dev_keyler`. Al trabajar local: commitea en una rama nueva por feature y haz `fetch`
antes de tocar lo que podría chocar con un push ajeno. Hay trabajo local sin
commitear en el checkout viejo de htdocs (ver sección "Merge pendiente").

## Respaldos

- Tag git: **`backup-pre-tito-redesign`** (volver atrás en un segundo)
- XAMPP MariaDB se cae frecuentemente (tablas Aria corruptas). Si MySQL no arranca:
  eliminar archivos basura `master-*`, `mysql-relay-bin-*`, `relay-log-*`, `*.dmp`
  de `C:\xampp\mysql\data\`
- Para verificar MySQL: `& "C:\xampp\mysql\bin\mysql.exe" -u root -e "select 1;"`
- El junction `C:\xampp\htdocs\menu` se recrea con:
  `New-Item -ItemType Junction -Path C:\xampp\htdocs\menu -Target C:\Users\Userr\menu`

## Estructura de archivos clave

```
admin/
  core/app/model/       # 22 modelos (compartidos público+admin)
  core/app/action/      # 13 action handlers (form processing)
  core/app/view/        # 18 vistas admin (settings, products, sells...)
  core/app/action/notifications-action.php  # JSON endpoint para alertas
  core/app/view/settings-view.php  # Sedes, zonas, ingredientes, configuración
  core/app/view/products-view.php  # Alta/edición de productos
  admin/storage/products/  # Imágenes de producto subidas
  admin/storage/sedes/     # Fotos de sedes subidas

core/
  app/view/index-view.php      # Página principal completa
  app/view/product-grid-view.php  # Grid AJAX de productos
  app/helpers/product-extras-helper.php  # Detección inteligente de ingredientes
  app/layouts/layout.php       # Shell del sitio público

assets/css/custom-dark.css     # Tema visual completo
schema.sql                     # Schema canónico de BD
```

## Patrones de código importantes

- **`ProductData::offerActive()`** verifica `offer_price > 0` directamente (no usa checkbox `is_offert`)
- **`ClientData::del()`** hace SOFT-DELETE (marca `is_active=0`) y `getAll()` filtra
  `is_active=1`. NO borra físicamente: `buy.client_id` tiene FK y un DELETE duro de un
  cliente con ventas da error. `getById()` no filtra para no romper la sesión/ventas
- **`ProductExtraData::addProductToAllGroups()`** es idempotente: chequea existencia antes de insertar
- **`tt_build_sabores()`** en `product-extras-helper.php`: filtra productos con
  ingredientes fijos (`count($pay["sel"])>0`), excluye "a tu Preferencia"
- **Estaciones** (`4_estaciones`, `2_estaciones`): el modal `openExtrasModal` renderiza
  radios por división (MITAD/CUARTO) con sabores como opciones
- **`free_ingredients`**: cantidad de ingredientes gratis. Se detectan "house
  ingredients" por fuzzy match del texto de descripción contra el catálogo `product_extra`
- **`unit_id`** puede ser NULL en la tabla `product` (se eliminó del formulario admin)
- **Sedes/Zonas**: tabla pivot `sede_delivery_zone` con switch por sede×zona. Precio de
  zona base siempre es $1, el real está en la pivot