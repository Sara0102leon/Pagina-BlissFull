# Blissfull — Notas de proyecto

## Rediseño "TITO BURGER" (agosto 2026)

Alcance exacto del rediseño — **solo estos archivos fueron modificados, una sola vez**:

1. `assets/css/custom-dark.css` — tema visual completo
2. `core/app/layouts/layout.php` — navbar, offcanvas y footer
3. `core/app/view/index-view.php` — hero y todas las secciones
4. `core/app/view/product-grid-view.php` — tarjeta de producto

Fuera del rediseño (no tocar): `schema.sql` incluye la columna `price_llevar` (línea 76) y el resto del código PHP/JS quedó intacto.

## Contrato invisible (NO romper)

El rediseño fue 100% HTML estructural + CSS. Lo PHP y JavaScript se copió tal cual. Está prohibido renombrar/eliminar estos identificadores:

- `#btn_confirm_order`, `#product_search`, `#grid-title`
- `.btn-category-ajax`, atributo `data-cat`
- `onclick="openExtrasModal(...)"`
- Los loops `foreach` PHP (se recargan por AJAX)

Regla de oro: **nunca mezclar lo dinámico con lo decorativo**. Se puede corregir textos y borrar secciones sin romper nada siempre que el contrato se mantenga.

## Sistema de diseño

- Paleta en variables CSS: `--tt-gold`, `--tt-red`, `--tt-bg`... (fuente única de verdad, un solo lugar para cambiar colores)
- Fondo de ladrillos = gradientes CSS apilados + dos brillos radiales (dorado y rojo), NO es imagen
- Google Fonts: Bebas Neue (títulos) y Caveat (cursivas rojas) cargadas en el `<head>`
- La tarjeta de producto (`tt-product-card`, `pc-price-pill`...) se reutiliza en grid (AJAX) y en el carrusel del index

## Respaldos y técnicas útiles

- Tag git de resguardo: **`backup-pre-tito-redesign`** (para volver atrás en un segundo)
- Validación: `php -l` sobre los 3 PHP + grep para verificar que IDs/onclicks/data-cat sigan existiendo tras cualquier cambio
- El bug del admin no era código: era la columna `price_llevar` faltante en MySQL real (comparar `schema.sql` contra `SHOW COLUMNS`), aunque las imágenes se subieran bien