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
- **Imágenes optimizadas** en `landing/assets/img/menu/` (≈45–57 KB c/u, generadas con
  `System.Drawing` desde `admin/storage/products/`). El héroe usa 2 (eager +
  `fetchpriority=high`) y el demo 6 con `loading="lazy"` + `decoding="async"`. Peso
  total de la landing ≈ **400 KB** (antes ~5 MB).
- **SEO/OG real**: `canonical`, Open Graph + Twitter (`assets/img/og.jpg` 1200×630,
  generado con Playwright desde un HTML en temp), `favicon.svg` + `apple-touch-icon.png`,
  `robots.txt`, `sitemap.xml` y **JSON-LD** (`Organization`, `WebSite`,
  `SoftwareApplication` con los 3 `Offer` y `FAQPage`).
- **A11y**: `<main id="main">` + skip link, demo reordenable por **teclado** (flechas)
  con anuncio en `#sortLive`, y modal de pago con **focus trap** + `inert` del fondo y
  anuncio de pasos en `#payLive`.
- **Dominio provisional** en metas/JSON-LD (`https://prueba.alianzablissful.com/landing/`):
  cambiar cuando exista el dominio definitivo de sazón.

## Pagos SaaS (A1 — implementado oct 2026)

Registro + planes + pago Zenobank real con creación automática de subdominio.
Migración aditiva/idempotente: **`migracion_saas.sql`** (tablas `plan`, `tienda`,
`suscripcion`; columnas `user.tienda_id` + `user.role`; claves `zeno_*`/`saas_*`).

**Flujo:** la landing (`landing/index.html` + `landing/assets/js/landing.js`) abre el
modal `#pay` con formulario (nombre → subdominio auto-slug, email/contraseña opcionales)
→ `POST landing/api/checkout.php` → en **modo demo** activa al instante; en **modo live**
crea el checkout en Zenobank y redirige a `pay.zenobank.io` → al volver
(`?pago=exito&tienda=slug`) el modal hace polling a `landing/api/checkout-status.php`.

**Endpoints/archivos nuevos:**
- `admin/core/app/model/PlanData|TiendaData|SuscripcionData.php`
- `admin/core/controller/ZenoBank.php` — cliente API Zenobank + verificación de firma
  **Svix** (HMAC-SHA256 sobre `{svix-id}.{svix-timestamp}.{rawBody}`, clave
  `base64_decode(secret sin whsec_)`, tolerancia 5 min; portado del SDK oficial).
- `webhooks/zenobank.php` — receptor: valida firma → `checkout.completed` marca la
  suscripción COMPLETED (periodo 30 días) y la tienda `activa`. Idempotente.
- `landing/api/` → `_bootstrap.php` (incluye `core/autoload.php` + autoload de modelos
  + ZenoBank; helper `saz_config()` que lee claves `zeno_*`/`saas_*` de `configuration`
  con caché, `saz_json()`, `saz_q()` escape mysqli, `saz_slugify()`), `config.php`
  (GET: modo/moneda/dominio/planes), `checkout.php`, `checkout-status.php`.

**Pasar de demo a live** (config en tabla `configuration`):
`zeno_api_key` = `sk_...` de dashboard.zenobank.io → Developers; `zeno_webhook_secret`
= `whsec_...`; `zeno_mode` = `live`; opcional `saas_landing_url` (fallback: host actual).
Registrar el webhook en Zenobank → URL `<host>/webhooks/zenobank.php` (eventos completed/
expired/partially_paid). El ruteo por subdominio (Host header) es A0/A2, aún no activo.

**Nota:** los modelos del proyecto no escapan SQL; los endpoints de `landing/api/` SÍ
usan `saz_q()` para lo que entra por HTTP (registro público).

## Multi-tenant + subdominios (A0/A2 — implementado oct 2026)

Cada cliente = una **tienda** (tenant). Una sola BD; las tablas de negocio llevan
`tienda_id` y los modelos filtran por la tienda activa.

**Cómo se resuelve la tienda activa** (`admin/core/app/model/Tenant.php`, caché estática
por request, se autoejecuta al primer `Tenant::filter()` / `Tenant::id()`):
1. **Sesión (admin):** el usuario logueado pertenece a una tienda (`user.tienda_id`).
2. **Host header (menú público):** el subdominio (`cliente.localhost`,
   `cliente.sazon.app`). Las bases reconocidas son `localhost`, `saas_base_domain`
   (config) y los dominios de `saas_alt_domains` (separados por espacio, hoy
   `prueba.alianzablissful.com`). Un subdominio desconocido cae al fallback.
3. **Fallback:** primera tienda `activa` (tras A2 = tienda #1, la pizzería).

**API de scoping:** `Tenant::id()` (int, 0 = sin tenant) · `Tenant::idSql()` (para
INSERTs: el entero o `NULL`) · `Tenant::filter($alias="")` → devuelve `tienda_id=N`
o `1=1` (así, sin tienda, el comportamiento es idéntico al de antes de A0).

**Modelos escopados:** `CategoryData`, `ProductData`, `SedeData`, `BuyData`,
`DeliveryZoneData` (lecturas + altas). Los `getById` quedan sin escopar (carrito/edición).
`ConfigurationData` todavía es global (tema por tienda = Fase 2, `configuration.cfg_id`).

**Migraciones aplicadas:**
- `migracion_multitenant.sql` — `tienda_id` en `category`,`product`,`sede`,`buy`,
  `delivery_zone` (+ índices vía information_schema) y clave `saas_alt_domains`.
- `migracion_a2.sql` — tienda **#1** `subdomain='principal'` (id forzado a 1 con
  `ON DUPLICATE KEY UPDATE`), estampa `tienda_id=1` en los datos actuales; admin →
  `tienda_id=1, role='dueno'`.

**Probar en local (subdominios simulados):**
```powershell
curl.exe -s -H "Host: principal.localhost" "http://localhost/menu/" | Select-Object -First 1
# grid AJAX de una categoría (endpoint real del menú):
curl.exe -s -X POST -H "Host: principal.localhost" -d "cat_id=2&q=&sede_id=0" "http://localhost/menu/?action=cart&opt=search"
```
Probe de resolución CLI en `%TEMP%\opencode\test-tenant.php` (incluye `core/autoload.php`
+ `core/app/autoload.php` y prueba varios `Host`).

**Producción:** snippet `deploy/nginx-subdominios.conf.example` (vhost comodín
`*.sazon.app` + cert wildcard de Let's Encrypt). Mientras no se despliegue, los
subdominios siguen sin actividad real.

**Resuelto (oct 2026):**
- **Carrito por tienda:** `$_SESSION["cart"]` (contrato intacto) lleva una clave
  compañera `$_SESSION["cart_tienda"]`; si el carrito pertenece a otra tienda se
  descarta en `core/app/init.php`. Además la cookie de sesión PHP es host-only
  (sin `Domain=`), por lo que cada subdominio ya tiene sesión/carrito aislados de
  fábrica; el guard es la red de seguridad para sesiones compartidas.
- **Ocultar subdominios inexistentes:** `Tenant::$hostIsStore`
  (`Tenant::isStoreStyleHost()`) detecta un Host tipo `<sub>.<base>` sin tienda y
  el front público responde **404** con una página "Tienda no encontrada" (estilo
  sazón, `X-Robots-Tag: noindex`); la raíz y hosts ajenos mantienen el fallback.
- `Tenant.php` expone además `Tenant::baseDomains()` (localhost + `saas_base_domain`
  + `saas_alt_domains`). El **tema/ajustes por tienda**
ya está resuelto con C1 (sección siguiente).

## Carta por tienda + configuración por tienda (C1 — oct 2026)

Cada tienda tiene su propio menú imprimible ("carta") y su propia configuración.

**Config por tienda** (`migracion_config_tienda.sql`, aplicada):
- `configuration.name` dejó de ser UNIQUE global → índice **`UNIQUE(name, cfg_id)`**.
  `cfg_id=1` es la configuración base/plantilla; el resto son overrides por tienda.
- `ConfigurationData::cfgId()` = `Tenant::id()` (fallback a 1); `getByPreffix` lee la
  tienda y **hereda de la base** si la tienda no define la clave; `getAll()` devuelve
  la config **efectiva** (base + overrides, la tienda gana); `updateValFromName` hace
  **upsert** en la tienda activa clonando `label`/`kind` de la base (los valores se
  escapan con `real_escape_string`). No hay que sembrar filas al crear una tienda:
  con herencia + upsert basta (hay helper `seedFromBase()` disponible si se prefiere).
- Claves nuevas de la carta (base, editables por tienda): `carta_titulo`,
  `carta_subtitulo`, `carta_mostrar_logo|precios|descripcion|imagenes`,
  `carta_columnas`, `carta_nota`.

**Carta imprimible** — `core/app/action/carta-action.php` → **`?action=carta`**:
- Página completa **sin layout** (para imprimir/PDF), `@media print` A4, botón
  "Imprimir / PDF", filtro por sede (`&sede=N`), "Todas las sedes" muestra todo
  (getter nuevo `ProductData::getPublicsByCategoryIdAll()`).
- **NUNCA usar `?view=carta`**: `View::load()` incluye la vista **dos veces** cuando
  `?view` está definido (líneas 292/313 de `core/app/layouts/layout.php`) — duplica
  el HTML y rompe si la vista declara funciones. Las páginas standalone van por
  **action**.
- Admin: ítem lateral "Carta" (abre `../?action=carta` en pestaña nueva), sección
  `?view=settings&opt=carta` con las opciones (guardar: `?action=settings&opt=savecarta`,
  switches no marcados = "0"). Footer público: enlace "Carta / Menú imprimible".
- El título por defecto usa `tienda.nombre` (hoy "Menú principal"); cambiar con
  `carta_titulo` o editando el nombre de la tienda.

**Probar en local:** `curl -H "Host: principal.localhost" "http://localhost/menu/?action=carta"`

## Orden del menú por drag & drop (A4 — oct 2026)

Categorías y productos se reordenan arrastrando en una página nueva.

- **Migración** `migracion_a4.sql` (aplicada): columna **`product.position`**
  (`int NOT NULL DEFAULT 0`) + índice `idx_product_order (tienda_id, category_id,
  position)`. Idempotente, **no backfillea** (todo en 0 = orden previo).
- **Página** `?view=order` → `admin/core/app/view/order-view.php`: lista de categorías
  (col. izq.) + una lista de productos por categoría (col. der.). **No** lleva la clase
  `datatable` (se salta el DataTables global del layout, línea ~267).
- **JS** `admin/assets/js/admin-order.js`: pointer events + FLIP (portado del demo de
  `landing/assets/js/landing.js`), arrastre **solo desde el grip** (`.order-grip`) y
  reordenado por **teclado** (flechas; el foco va en el `<li tabindex=0>`). Al soltar,
  `fetch` JSON y muestra "Guardado ✓" en `#orderSaved`.
- **Action JSON** `admin/core/app/action/order-action.php`:
  `?action=order&opt=cats` (`{ids:[…]}`) y `?action=order&opt=prods`
  (`{category_id, ids:[…]}`). Guard: sesión + `role='dueno'` (401/403) y verifica que
  ids/categoría pertenezcan a la tienda (`Tenant::filter()`).
- **Modelos**: `CategoryData::reorder($ids)` (posición 0..n-1) y
  `ProductData::reorderInCategory($cat,$ids)`; `ProductData::nextPosition($cat)` pone
  los productos nuevos al final.
- **Menú público**: `ProductData::getPublicsByCategoryId` ahora
  `order by position asc, created_at desc`; `getActivesByCategory` igual;
  `getPublicsByCategoryIdAll` → `position asc, name asc`. Con positions en 0 se
  mantiene el comportamiento anterior.
- **Sidebar**: ítem "Orden del menú" tras Categorías (solo dueño; el guard de rol del
  layout rebota a cocina/repartidor). Título en `$admin_titles["order"]`.

**Probar en local:** entra a `http://localhost/menu/admin/?view=order` con `admin/admin`.

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
- `login.php` → **página de acceso standalone** de la plataforma (brand "sazón", fuera de la
  landing y del menú público). Valida contra `user` (`sha1(md5(pass))`, `is_active=1`) y
  entra a `admin/`. El admin redirige aquí cuando no hay sesión (`../login.php`, también
  tras logout y login fallido). La landing NO tiene opción de login.
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