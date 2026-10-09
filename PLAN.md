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