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
- **A0** Arquitectura multi-tenant (tienda/plan/roles) + subdominios en el VPS.
- **A1** Landing + registro + planes ← "primero mi landing".
- **A2** Migrar el menú actual como primer cliente en su subdominio.
- **A3** Panel con roles: cocina (pedidos) y repartidor (delivery).
- **A4** Drag & drop del orden de menú (categorías y productos).
- Las **Fases 1-5** (abajo) siguen siendo el motor del menú por cliente.

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