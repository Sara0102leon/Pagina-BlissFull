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

## Apariencia / Menú por tienda (Fase 2 — oct 2026)

Migración **`migracion_fase2.sql`** (aplicada; aditiva/idempotente).

**Imágenes por categoría**
- Columna **`category.image`**; `CategoryData` la incluye en `add()`/`update()`.
- Alta/Edición de categoría (`?view=categories&opt=new|edit`) tienen `enctype`
  multipart + input `image`; el action (`categories-action.php`) sube con `Upload` a
  `admin/storage/categories/`. En edición hay checkbox **`remove_image`**.
- Menú público (`index-view.php`): el chip de categoría muestra una miniatura redonda
  (`.cat-chip-img`) y expone `data-img`; el JS del título del grid (`catTitleHtml()`)
  usa esa imagen como banner (`.grid-title-img`) al seleccionar la categoría.

**Logo propio del negocio**
- Claves `tema_logo` (archivo en `admin/storage/brand/`) y `tema_logo_alt` (alt).
- Se gestionan en **Apariencia → tarjeta "Marca"** (`?view=settings&opt=apariencia`;
  subir/quitar + texto alternativo). El POST va a `savetema` (con `enctype` multipart).
- Helpers `tt_logo_url()` / `tt_logo_alt()` en `core/app/layouts/layout.php` (navbar y
  footer). Fallback: `assets/img/logo-sazon.svg`.

**Sección de ofertas**
- Toggle **`menu_show_ofertas`** (default "1"), en Apariencia → "Secciones visibles".
- `ProductData::getOffers($sede_id=0)` (ahora filtra por sede). El bloque "OFERTAS DEL
  DÍA" se pinta tras el hero reusando `product-grid-view` con `$_POST["ofertas"]=1`
  (`View::load` con backup/restore de `$_POST`). Si no hay ofertas, no se muestra.

**Restaurar por defecto** (Apariencia) también limpia `tema_logo`/`tema_logo_alt`.

**Probar en local:** sube una imagen en `?view=categories&opt=edit&id=2` y mira el chip
en `http://localhost/menu/`; sube un logo en Apariencia y verifica navbar/footer.

## Tema 100% variable (Fase 3 — oct 2026)

Migración **`migracion_fase3.sql`** (aplicada; aditiva/idempotente): añade la clave
`tema_bg_3` (base `cfg_id=1`).

El menú público ya **no tiene colores de marca hardcodeados**: se pinta con variables
`--tt-*` (definidas en `custom-dark.css` `:root` e inyectadas por `layout.php`).
Cambiar la paleta en **Apariencia** recolorea todo el sitio.

**Tokens nuevos** (`:root` de `custom-dark.css` + inyección en `layout.php`):
- `--tt-accent` — acento explícito (antes solo se inyectaba si `tema_accent` existía).
- `--tt-bg-3` — superficie alterna (tarjetas de sede, `<option>`, cuerpo de modales);
  clave editable **`tema_bg_3`** (Apariencia → Colores → "Fondo alterno").
- `--tt-on-gold` — tinta sobre el acento; **calculada por luminancia** en PHP
  (`tt_on_accent()`): acento claro → texto oscuro (`#000`); acento oscuro → `#fff`.
- Compañeros RGB para usar marca **con alfa**:
  `--tt-gold-rgb · --tt-gold-2-rgb · --tt-accent-rgb · --tt-red-rgb · --tt-red-2-rgb · --tt-bg-3-rgb`
  → `rgba(var(--tt-accent-rgb), 0.35)`. Se calculan en `layout.php` con `tt_hex_rgb()`.

**Helpers nuevos** en `layout.php`: `tt_hex_rgb()`, `tt_hex_light()`, `tt_on_accent()`.

**Alcance del barrido:** `assets/css/custom-dark.css`, `core/app/layouts/layout.php` e
`core/app/view/index-view.php`. Se conservan a propósito los colores **decorativos**
(ladrillo/brasas) y **semánticos** (verdes de éxito) y las superficies blanco-alfa.

**Probar el tema:** en `?view=settings&opt=apariencia` cambia "Color principal" y
"Fondo alterno"; el menú entero (chips, precios, botones, modales, footer) cambia y la
tinta sobre el dorado se ajusta sola. Verificar computed styles:
`getComputedStyle(document.documentElement).getPropertyValue('--tt-gold')`.

## Funciones de menú opt-in (Fase 4 — oct 2026)

Migración **`migracion_fase4.sql`** (aplicada; aditiva/idempotente): columnas
`product.badge`, `product.badge_color`, `product.offer_from`, `product.offer_to`
(+ índice de ofertas intacto) y las claves **`menu_show_favs` · `menu_show_share` ·
`menu_show_listmode`** (default **"0"**). **Todo es opt-in**: con los toggles en "0" el
menú público es idéntico al de antes de Fase 4 (verificado con smoke HTTP).

**Data-driven (por producto, sin toggle):**
- **Etiqueta**: `badge` (texto ≤24) + `badge_color` (`accent|gold|red|dark|outline`).
  Se pinta `.pc-badge.pc-badge--<color>` en la tarjeta. El color va **por clase CSS**,
  NO con `tt_on_accent()`: `product-grid-view.php` se sirve por AJAX
  (`?action=cart&opt=search`) **sin layout**, así que los helpers `tt_*` de
  `layout.php` no existen ahí. Editado en productos (preview `#pv_badge`).
- **Agotado**: reutiliza **`product.in_existence=0`** (sin columna nueva). Tarjeta
  `.is-soldout` + overlay `.pc-soldout` "AGOTADO" + imagen gris + botón
  `.pc-add-off` deshabilitado. **Guard real** en `cart-action.php` `opt=add`:
  descarta producto inexistente / agotado / de otra tienda. (El detalle público
  `?view=products&opt=open` ya respetaba `in_existence`.)
- **Oferta por franja**: `offer_from`/`offer_to` (TIME, opcionales). `offerActive()`
  soporta **cruce de medianoche** (20:00–02:00) y un solo extremo; `getOffers()` ahora
  filtra con `offerActive()` (antes **ignoraba `offer_finish`**). El form los normaliza
  con `tt_norm_time()` (HH:MM[:SS] → `HH:MM:SS` o "" si inválido).

**Opt-in (Apariencia → tarjeta "Funciones del menú", `$ttc_features` en
`settings-view.php`; acción `savetema` con `$tt_toggles` extendido; `restoretema` los
devuelve a "0"):** `menu_show_favs` (corazón + filtro "solo favoritos"),
`menu_show_share` (Web Share API / copiar enlace), `menu_show_listmode` (cuadrícula /
lista compacta). Son **solo front-end** (`localStorage`: `tt_menu_favs`,
`tt_menu_listmode`) y `ttApplyGridState()` los re-aplica tras el AJAX del grid.
La barra `#tt_fav_filter` / `#tt_view_toggle` (`.tt-tool-group`) solo se pinta si el
toggle está activo; con los toggles en "0" no aparece markup (el JS queda inerte).

**Ojo con el form de producto (bug sutil):** `is_public`/`in_existence`/`is_featured`
usan `isset($_POST[...])` → **enviar `in_existence=0` lo interpreta como MARCADO (=1)**.
Para poner 0 hay que **omitir** el campo. Y un POST parcial necesita `sedes_all=1`
(si no, `sede_id` pasa a 0 y el producto sale del grid, cuyo filtro exige
`sede_id is null` o pivote).

**Probar:** `?view=products&opt=edit&id=N` (etiqueta + franja) →
`?view=settings&opt=apariencia` (activar toggles) → `http://localhost/menu/` (badges,
agotado, corazón, compartir, modo lista). Lógica de oferta: test CLI `27/27` en
`%TEMP%\opencode\test-fase4.php`; E2E Playwright en `%TEMP%\opencode\fase4-shot.js`.

## Plataforma: super-admin + renovación (Fase 6 — oct 2026)

Migración **`migracion_plataforma.sql`** (aplicada; aditiva/idempotente):
`suscripcion.recordatorio_at` · clave `saas_dias_aviso` (base `cfg_id=1`, default 7) ·
usuario **`superadmin` / `superadmin`** (`role='superadmin'`, **cambiar al desplegar**).

**Rol `superadmin`** (`user.role`): acceso total al panel **de cualquier tienda** +
sección **Plataforma**. En el layout `$is_super` fuerza `$is_dueno` (mismo alcance),
etiqueta "Super admin", y solo para él aparece el ítem lateral **"Tiendas / Plataforma"**.
Los guards de `order-action`/`users-action`/`pedidos-action` aceptan `dueno|superadmin`.

**Panel `?view=platform`** (`platform-view.php` + `PlatformData`): KPIs, listado de
tiendas (plan/periodo/días), **alta manual** (tienda + usuario dueño opcional) y config
del aviso. Pestañas `all|movimiento|planes`. `PlatformData` consulta **sin**
`Tenant::filter()` a propósito (es la vista global) y lee `configuration` siempre en
`cfg_id=1`.

**Acciones `?action=platform`** (`platform-action.php`, guard superadmin):
`estatus=activa|pendiente|suspendida` · `extender` (días) · `plan` · `recordar`
(marca `recordatorio_at` y abre **WhatsApp** `wa.me` con mensaje prellenado; sin
WhatsApp solo marca) · `entrar`/`salir` (impersonar) · `crear` · `cfg`.

**Impersonación**: `$_SESSION["impersonate_tienda"]`, honrada por `Tenant::resolve()`
**solo si** el usuario logueado es `superadmin` (`Tenant::impersonating()`). Banner
global "Estás viendo como X · Salir" en el layout.

**Renovación / recordatorio**
- `SuscripcionData::tenantAlert($tienda_id)` → nivel del aviso (pago pendiente / por
  vencer / vencido / suspendido), usado por el **banner del panel** (rol dueño,
  `.tt-tenant-banner--{info|warning|danger}`) con CTA "Renovar plan".
- `?view=renovar` (`renovar-view.php`) + `?action=renovar` (`renovar-action.php`):
  el dueño elige plan y paga. **Demo** (sin `zeno_api_key` o `zeno_mode!=live`) activa
  al instante; **Live** crea el checkout en **ZenoBank** y redirige; el retorno es
  `?view=renovar&pago=exito`. Se **reusa la fila `suscripcion`** y
  `markCompleted()` **extiende** 30 días (base = `periodo_fin` vigente, si no hoy);
  `extend($dias)` hace lo propio desde el panel.
- **Tienda no disponible**: subdominio de tienda con `estatus != 'activa'` →
  **503** "Menú no disponible" (`core/app/init.php`, `X-Robots-Tag: noindex`).

**Probar en local:** login `superadmin/superadmin` → `admin/?view=platform`
(entrar como / activar / suspender / recordar / extender / plan) → banner de
vencimiento en el panel del dueño → `admin/?view=renovar` (demo activa al instante).

**Endurecimiento por auditoría (oct 2026).** Revisión de código del diff de la fase →
1 bloqueante + 2 medios, todos arreglados y verificados (detalle y pruebas en
`REVISION.md §6`):
- **ALTA — escalada al super-admin**: un dueño podía editar/borrar (y cambiar rol o
  contraseña de) **cualquier** usuario, incluido el super-admin, desde `?view=users`.
  Nuevos `UserData::puedeTocar($me,$obj)` / `getAllVisibles($me)`: al super-admin sólo lo
  toca otro super-admin; cada dueño ve/edita sólo su tienda. El form de edición **no**
  ofrece el rol si el objetivo es super-admin (no se puede degradar) y el action conserva
  el rol si el campo no viene. Los ids entran casteados a `(int)` (id vacío = "sin
  permiso", antes Fatal error).
- **MEDIA — doble checkout en live**: reutiliza el checkout pendiente del mismo plan
  (`renovar-action.php`); y el webhook, si el `checkout_id` no matchea, localiza la fila
  por el **`orderId`** del payload (`saz-<tienda>-<sus>` / `renov-<tienda>-<sus>-<ts>`).
- **MEDIA — fuga sin tiendas activas**: `core/app/init.php` responde **404** ("Sin tiendas
  activas" en la raíz) cuando `Tenant::current()` es null (antes el fallback `1=1`
  publicaba los datos de todas las tiendas); y `?action=platform&opt=estatus` **no deja
  suspender la última tienda activa**.
- Bajas: alta manual valida dueño **antes** de crear (`FILTER_VALIDATE_EMAIL` + mín. 6) y
  el password va en `type="password"`; KPI del panel relabelado ("Pagos con periodo que
  inicia este mes"); `TiendaData::update()` muerto eliminado.
- **Pre-existente arreglado**: `UserData::add()` descartaba `username` en silencio (el
  login acepta email **o** username).
- **Falso positivo**: los husos horarios. Se medía `Europe/Berlin` del `php.ini`, pero cada
  entry point fija `America/Caracas` antes de correr (probe en vivo en el pipeline real).
  Igual se **endureció**: la zona del negocio ahora se fija también en `core/autoload.php`
  y `admin/core/autoload.php`.

**Pendiente (fuera de la fase):** límites de plan, gate automático por vencimiento,
emails y recobro automático.

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