# Reinicio del Menú Digital — Plan

**Objetivo:** "volver a empezar" dejando lo esencial (BD + funcionalidad de menú/admin)
y cambiando la mayoría de las cosas: **rediseño visual completo** y un **admin que
configure el menú** (colores, tipografías, orden del menú, secciones y más).

**Base:** código PHP existente en `C:\Users\Userr\menu` (rama `main`), BD `tacomenu`.
**Regla de oro (contrato invisible):** NO renombrar/eliminar identificadores usados
por AJAX/JS (`#btn_confirm_order`, `#product_search`, `#grid-title`,
`.btn-category-ajax`, `data-cat`, `openExtrasModal`, `$_SESSION["cart"]`,
`blissfull_sede_id`).

---

## Visión: Plataforma de menús digitales multi-cliente (referencia: OlaClick)

El proyecto pasa de "reiniciar el menú de Blissfull" a una **plataforma SaaS**:
el cliente se registra, paga un plan y se le activa su **menú en un subdominio propio**.

### Modelo (lo que OlaClick hace → lo que adaptamos y mejoramos)
- **Landing** (marketing) → planes → registro → pago → **se crea el subdominio del cliente**.
- **Menú digital por cliente** en subdominio (`cliente.dominio.com`): carta, carrito,
  pedidos por WhatsApp, pagos, sedes, horarios — el motor ya construido (Fases 1-5).
- **Panel por cliente con roles**: dueño/admin (todo), **cocina** (KDS: pedidos
  listos/pendientes), **repartidor** (delivery: estados/rutas), caja/mesas (opcional).
- **Mejoras propias**: ordenar el menú **manualmente con drag&drop** (categorías y
  productos dentro de categoría), tema configurable por cliente, subida de fotos.

### Arquitectura decidida (confirmada por el usuario — oct 2026)
- **Stack: extender el PHP/MariaDB actual** (el motor de menú validado es la base; no se reescribe).
- Multi-tenant con **base única** + `tienda_id` en las tablas de negocio (tablas
  nuevas: `tienda`, `plan`, `suscripcion`; `role` en `user`).
- Subdominios resueltos por **Host header** → tienda activa (vhost comodín en nginx).
  Ahora se desarrolla **en local** (simular subdominios con `Host: x.localhost`).
- Roles: **un solo panel web por rol** (el login decide la pantalla: Dueño todo,
  Cocina = KDS, Repartidor = entregas).
- Pagos: el cliente paga en línea con **Zenobank** y es redirigido de vuelta con el
  pago aprobado; el **subdominio se crea automáticamente**. (Landing: demo del flujo.)
- El primer cliente = la pizzería actual: sus datos `tacomenu` migran como una tienda.

### Hoja de ruta de producto
- **A0** Arquitectura multi-tenant (tienda/plan/roles) + subdominios → ✅ (oct 2026)
- **A1** Landing + registro + planes → ✅ (oct 2026)
- **A2** Migrar el menú actual como primer cliente (tienda 1) → ✅ (oct 2026)
- **C1** Carta por tienda: menú imprimible (`?action=carta`) + configuración por tienda (`configuration.cfg_id`) → ✅ (oct 2026)
- **A3** Panel con roles: cocina (pedidos) y repartidor (delivery) → ✅ (oct 2026)
- **A4** Drag & drop del orden de menú (categorías y productos) → ✅ (oct 2026)
  (se conservan también las flechas ↑/↓ en Categorías).
- Las **Fases 1-5** (abajo) siguen siendo el motor del menú por cliente.

### A0/A2 — Multi-tenant implementado (oct 2026)
- Migración `migracion_multitenant.sql`: `tienda_id` en `category`, `product`, `sede`,
  `buy`, `delivery_zone` (+ índices), y clave `saas_alt_domains`.
- Modelo **`Tenant`** (`admin/core/app/model/Tenant.php`): resuelve la tienda activa
  por **sesión** (admin → `user.tienda_id`), por **subdominio del Host**
  (`cliente.localhost` / `cliente.sazon.app`) y con **fallback** a la primera tienda
  activa. Expone `Tenant::id()`, `Tenant::idSql()` e `Tenant::filter()`
  (`tienda_id=N` o `1=1` si no hay tenant).
- Modelos escopados por `Tenant::filter()`: `CategoryData`, `ProductData`, `SedeData`,
  `BuyData`, `DeliveryZoneData` (lecturas y altas).
- Migración `migracion_a2.sql`: crea la tienda #1 (`subdomain='principal'`) y estampa
  `tienda_id=1` en los datos actuales; el admin pasa a `tienda_id=1, role='dueno'`.
- **Verificado en local** (`Host: x.localhost` vía curl): dominio raíz y
  `principal.*` sirven la tienda 1; una tienda 2 de prueba con su categoría/producto
  quedó aislada (no aparecía en el menú principal ni viceversa).
- VPS: snippet `deploy/nginx-subdominios.conf.example` (vhost comodín `*.sazon.app`
  + cert wildcard); el hosting real se activa al desplegar.
- **Subdominios inexistentes → 404** ✅: `Tenant::isStoreStyleHost()` + `$hostIsStore`
  detectan un Host tipo `"<sub>.<base>"` sin tienda y, en lugar del fallback, el
  front público (`core/app/init.php`) responde **404** con una página "Tienda no
  encontrada" (estilo sazón, `X-Robots-Tag: noindex`). Dominios raíz y hosts
  ajenos siguen usando el fallback (primera tienda activa).
- **Carrito por tienda** ✅: `$_SESSION["cart"]` (contrato intacto) se acompaña de
  `$_SESSION["cart_tienda"]`; si el carrito pertenece a otra tienda se descarta en
  `core/app/init.php`. Además, la cookie PHP de sesión es **host-only** (sin
  `Domain=`), así que cada subdominio ya tiene sesión y carrito aislados de fábrica;
  el guard `cart_tienda` es la red de seguridad para sesiones compartidas.

### C1 — Config por tienda + carta imprimible (oct 2026)
- **Config por tienda:** `configuration` pasa de `UNIQUE(name)` global a
  `UNIQUE(name, cfg_id)` (migración `migracion_config_tienda.sql`). `cfg_id=1` es la
  base/plantilla; cada tienda hereda de la base y sobreescribe solo lo suyo.
  - `ConfigurationData::cfgId()` = `Tenant::id()` (fallback a 1).
  - Lectura `getByPreffix` con herencia; `getAll()` devuelve la config efectiva
    (base + overrides); `updateValFromName` hace upsert en la tienda activa
    clonando `label`/`kind` de la base. Valores escapados (`real_escape_string`).
- **Carta imprimible:** `core/app/action/carta-action.php` → `?action=carta`
  (página completa, sin layout, con `@media print` tamaño A4 y botón Imprimir/PDF).
  Filtro por sede `&sede=N`; "Todas las sedes" usa el getter nuevo
  `ProductData::getPublicsByCategoryIdAll()`. Incluye precios (con ofertas
  tachadas), descripciones, imágenes opcionales y columnas 1-3.
  - Se usa **action** y no `?view=carta` porque `View::load()` (quirk del framework:
  línea 292 y 313 del layout) incluye la vista **dos veces** cuando `?view` está
  definido (rompe si la vista declara funciones).
- **Admin:** ítem lateral "Carta" (abre la carta en pestaña nueva) + sección
  `?view=settings&opt=carta` (título, subtítulo, logo/precios/descripción/imágenes,
  columnas, nota al pie) guardada con `?action=settings&opt=savecarta`
  (switches no marcados = "0"). Footer público: enlace "Carta / Menú imprimible".
- **Probar en local:** `curl -H "Host: principal.localhost" "http://localhost/menu/?action=carta"`.

### A3 — Panel con roles: Cocina (KDS) y Repartidor (oct 2026)
- **Login por rol** (`login.php`): Dueño → `admin/`, Cocina → `admin/?view=cocina`,
  Repartidor → `admin/?view=repartidor` (según `user.role`).
- **Guard de vistas en el layout admin**: cocina/repartidor solo ven su panel
  (rebote a su vista); header sin notificaciones/tour y sidebar mínimo.
- **Cocina** (`cocina-view.php` + `partials/cocina-list.php`): cola de pedidos
  status 1|2 con ítems, extras y nota; botón **"Listo para reparto"** (→ 4);
  polling cada 15 s con destello + sonido al llegar pedidos.
- **Repartidor** (`repartidor-view.php` + `partials/repartidor-list.php`): cola
  status 4 con cliente/teléfono/zona/distancia/mapa/WhatsApp; botón **"Entregado"**
  (→ 5); polling igual.
- **Transiciones** (`pedidos-action.php`): `preparar` (cocina/dueño, solo desde
  1|2) y `entregar` (repartidor/dueño, solo desde 4); verifica `tienda_id` del
  pedido. Filas parciales AJAX (`opt=cocina_rows` / `repartidor_rows`).
- **Usuarios**: alta/edición con campo **"Rol en la tienda"** (`role` +
  `tienda_id` del tenant) y guard de dueño en `users-action`.
- **Fix de columnas**: `UserData::add()/register()` insertaban la columna `kind`
  inexistente en la BD real (el alta web de usuarios estaba roto); ahora persisten
  `role` + `tienda_id`. Se quitó el select "Tipo" de la vista de usuarios.

### A4 — Orden del menú por drag & drop (oct 2026)
- Migración `migracion_a4.sql` (aplicada): columna **`product.position`**
  (`int NOT NULL DEFAULT 0`) + índice `idx_product_order (tienda_id, category_id,
  position)`. Idempotente; **no backfillea** (todas en 0 = orden actual).
- **Página "Orden del menú"** (`?view=order`): nueva vista independiente
  (`order-view.php`) — lista de categorías + una lista de productos por categoría.
  No usa `datatable` (evita el DataTables global del layout).
- **Arrastre** (`admin/assets/js/admin-order.js`): pointer events + FLIP, portado del
  demo de la landing; incluye reordenado por **teclado** (flechas ↑/↓ sobre el
  elemento enfocado). Al soltar guarda por `fetch` (JSON) y muestra "Guardado ✓".
- **Persistencia** (`order-action.php`, JSON): `?action=order&opt=cats`
  (`{ids:[...]}`) y `?action=order&opt=prods` (`{category_id, ids:[...]}`).
  Guard: sesión + `role='dueno'` (403 si no) y verificación de que categoría/ids
  pertenezcan a la tienda (`Tenant::filter()`).
- **Modelos**: `CategoryData::reorder($ids)` reasigna `position` 0..n-1;
  `ProductData::reorderInCategory($cat,$ids)` hace lo propio dentro de una categoría.
  `ProductData::nextPosition($cat)` coloca los productos nuevos al final.
- **Menú público**: las consultas de producto pasan a
  `order by position asc, created_at desc` (`getPublicsByCategoryId`,
  `getPublicsByCategoryIdAll` → `position asc, name asc`, `getActivesByCategory`).
  Con todas las posiciones en 0 el orden es el de antes.
- **Verificado en local**: reordenar categorías y productos persiste en BD y se
  refleja en el grid AJAX público; guards 401 (sin sesión) / 403 (no-dueño, categoría
  ajena); sin errores de consola (Playwright + Chrome).

---

## Fase 1 — Motor de tema + orden del menú  ✅ (completada)
- Concepto: el menú público se pinta 100% con variables CSS (`--tt-*`). Esas variables
  pasan a **configurarse desde admin** (tabla `configuration`, prefijo `tema_*`).
- Migración `migracion_tema.sql` (aplicada):
  - `category.position` (columna nueva) → orden del menú editable.
  - Claves `tema_*` (colores/fuentes) y `menu_*` (visibilidad de secciones).
- Admin: pestaña **Apariencia** en Settings con selectores de color, fuentes,
  secciones visibles y restaurar por defecto.
- Admin: flechas ↑/↓ en Categorías para reordenar el menú (`CategoryData::getPrev/Next`,
  swap de `position`).
- Frontend: `layout.php` inyecta `:root { --tt-* }` desde `configuration` (helper
  `tt_theme_get()`); hardcodes del layout/loader/Swal convertidos a `var(--tt-*)`.
- Frontend: `index-view.php` respeta los 6 toggles `menu_*` (hero, slides, featured,
  bebidas del modal, sedes/horarios, iconos de categoría).
- **Reparación extra (bloqueaba el sitio):** la BD viva no tenía `product_sede`,
  `bebida` ni `sede_horario`, y a `buy` le faltaban columnas (chatwoot/geo/notified).
  Migración aditiva `migracion_esquema.sql` (aplicada). También se silenciaron los
  deprecations de PHP 8.2 (`@` en `Model::one/many`) que polucionaban el output.

## Fase 2 — Panel "Apariencia / Menú" completo
- Imágenes por categoría, orden dentro de la categoría (productos), textos del hero
  (ya parcial), logo, sección de ofertas. Configurar todo desde admin.

## Fase 3 — Rediseño visual del menú público
- Nueva identidad/paleta/tipografías/layout con el motor de tema por debajo.
- Sustituir colores hardcodeados de layout/index por variables `--tt-*`.
- Conservar estructura de flujo (hero → categorías → grid → carrito → WhatsApp).

## Fase 4 — Nuevas funciones de menú (a definir con Sara)
- Ideas: badges dinámicos, ofertas por franja horaria, "agotado", favoritos,
  compartir producto, modo listado compacto, etc.

## Fase 5 — Refactor de admin
- "Cambiaré cómo funcionan la mayoría de cosas en admin": rehacer experiencias de
  productos, categorías, sedes y pedidos según los nuevos requisitos.

---

## Decisiones de arquitectura
- Tema = filas `configuration` (`tema_<grupo>`), leídas en `core/app/layouts/layout.php`
  e inyectadas como `:root { --tt-*: ... }` que overridera `custom-dark.css`.
- Orden de menú = columna `position` en `category` (y después en `product`).
- Migraciones incrementales con `INSERT IGNORE` + `ADD COLUMN IF NOT EXISTS`
  (idempotentes). Nunca tocar `schema.sql`.
- Validación siempre con `php -l` y prueba de humo en `http://localhost/menu/`.