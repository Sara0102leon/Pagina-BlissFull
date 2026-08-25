# Blissfull — Notas de proyecto

## Arquitectura

PHP puro, sin Composer/npm/build tools. Framework MVC custom ("Lb/Legobox").

**Dos entry points, dos codebases separados:**
- `index.php` → sitio público (carpeta raíz)
- `admin/index.php` → panel admin (carpeta `admin/`)
- Apache sirve desde `C:\xampp\htdocs\blissfull` (live), el repo está en `C:\Users\Userr\Videos\Pagina-BlissFull-main`
- **Siempre sync despues de editar**: copiar archivos modificados a htdocs

**Routing por query string (sin .htaccess):**
- `?view=X` → carga `core/app/view/X-view.php` dentro del layout
- `?action=X&opt=Y` → ejecuta `core/app/action/X-action.php` (sin layout)
- Sin parámetros → carga `index-view.php`

**Modelos compartidos:** Todos viven en `admin/core/app/model/` y se usan desde ambos lados. El autoloader SPL los resuelve por nombre (`ProductData` → `admin/core/app/model/ProductData.php`). La clase `Database.php` real está en `admin/core/controller/Database.php` (la de `core/controller/` está vacía).

**Models son clases planas** con métodos estáticos (`getAll()`, `getById()`, `getLike()`) e instancias (`add()`, `update()`, `del()`). SQL raw vía `Executor::doit($sql)` — sin prepared statements.

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
- La tarjeta de producto (`tt-product-card`, `pc-price-pill`...) se reutiliza en grid (AJAX) y carrusel del index
- Tabler UI framework como base, custom-dark.css lo overridea

## Base de datos

**MariaDB 10.4.32** en XAMPP (root, sin password, db `tacomenu`). MySQL80 en puerto 3307 es irrelevante.

Schema canónico: `schema.sql` (DDL + seed). Dump de producción: `database.sql`. Migración incremental: `migracion_sedes.sql`.

**Gotcha recurrente: columnas faltantes.** El código PHP usa columnas que no existen en la BD real. Siempre comparar:
```
SHOW COLUMNS FROM product;   -- vs lo que usan ProductData::add(), products-view.php, products-action.php
```
Columnas conocidas que han causado este bug: `price_llevar`, `allow_halves`, `house_ingredients`, `tipo_division`.

**Contraseña de usuario admin:** `sha1(md5("password"))` — hash débil pero funcional, no cambiar.

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
& "C:\xampp\mysql\bin\mysql.exe" -u root tacomenu -e "SHOW COLUMNS FROM product;"
# Comparar contra ProductData::add() y products-view.php
```

## Respaldos

- Tag git: **`backup-pre-tito-redesign`** (volver atrás en un segundo)
- XAMPP MariaDB se cae frecuentemente (tablas Aria corruptas). Si MySQL no arranca: eliminar archivos basura `master-*`, `mysql-relay-bin-*`, `relay-log-*`, `*.dmp` de `C:\xampp\mysql\data\`
- Para verificar MySQL: `& "C:\xampp\mysql\bin\mysql.exe" -u root -e "select 1;"`

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
- **`ProductExtraData::addProductToAllGroups()`** es idempotente: chequea existencia antes de insertar
- **`tt_build_sabores()`** en `product-extras-helper.php`: filtra productos con ingredientes fijos (`count($pay["sel"])>0`), excluye "a tu Preferencia"
- **Estaciones** (`4_estaciones`, `2_estaciones`): el modal `openExtrasModal` renderiza radios por división (MITAD/CUARTO) con sabores como opciones
- **`free_ingredients`**: cantidad de ingredientes gratis. Se detectan "house ingredients" por fuzzy match del texto de descripción contra el catálogo `product_extra`
- **`unit_id`** puede ser NULL en la tabla `product` (se eliminó del formulario admin)
- **Sedes/Zonas**: tabla pivot `sede_delivery_zone` con switch por sede×zona. Precio de zona base siempre es $1, el real está en la pivot
