# sazón — Documento de revisión

**Rama:** `saas-landing` · **HEAD previo:** `2a6605e` (Fase 4) · **Fase nueva:** 6
**Fecha:** 14 oct 2026 · **Entorno:** local (XAMPP) — `C:\Users\Userr\menu` servido por el
junction `C:\xampp\htdocs\menu` → `http://localhost/menu/`

Este documento resume **todo lo hecho** en la hoja de ruta SaaS y detalla la **Fase 6**
(super-admin de plataforma + recordatorio/renovación), que es lo último y lo que hay que
revisar. Al final está la lista de **pendientes** y cómo pasar de **demo a live**.

---

## 1. Cómo revisar (puesta en marcha)

```powershell
# 1) MariaDB (si el puerto 3306 no responde)
Start-Process C:\xampp\mysql\bin\mysqld.exe -ArgumentList '--defaults-file=C:\xampp\mysql\bin\my.ini','--standalone'
# 2) Apache (si el puerto 80 no responde)
Start-Process C:\xampp\apache\bin\httpd.exe
```

| Acceso | URL | Credenciales |
|---|---|---|
| Menú público | http://localhost/menu/ | — |
| Panel admin (dueño) | http://localhost/menu/admin/ | `admin` / `admin` |
| **Panel plataforma (super-admin)** | http://localhost/menu/admin/ | `superadmin` / `superadmin` |
| Landing | http://localhost/menu/landing/ | — |
| Carta imprimible | http://localhost/menu/?action=carta | — |

> El super-admin entra por el **mismo login** (`http://localhost/menu/login.php`), pero al
> autenticarse ve el panel igual que un dueño **más** la sección lateral
> **"Tiendas / Plataforma"**. Cambia la contraseña antes de desplegar.

### Checklist de revisión (Fase 6)

1. **Panel de plataforma** — `http://localhost/menu/admin/?view=platform`
   - KPIs (tiendas activas, ingresos del mes, por vencer / vencidas, pedidos del mes).
   - Tarjetas de tiendas con plan, periodo, días restantes y contadores de contenido.
   - Botones por tienda: **Entrar como**, **Activar/Suspender**, **Recordar**,
     **Extender N días**, **Cambiar plan**.
   - Alta manual de tienda (con usuario dueño opcional) y config del aviso.
2. **Pestaña Movimiento** — `?view=platform&opt=movimiento`: pagos/suscripciones y
   pedidos recientes de **todas** las tiendas.
3. **Pestaña Planes** — `?view=platform&opt=planes`: límites de cada plan (informativos).
4. **Impersonación** — pulsa **"Entrar como"**: verás el banner
   *"Estás viendo como &lt;tienda&gt; · Salir"* en la parte superior; todo lo que edites
   se aplica a esa tienda. **Salir** vuelve a Plataforma.
5. **Recordatorio de renovación** —
   - En el panel de **plataforma**, `Recordar` marca el aviso y abre **WhatsApp** con un
     mensaje prellenado (si la tienda tiene `whatsapp`); sin WhatsApp solo queda marcado.
   - En el panel del **dueño**, cuando el plan está por vencer/vencido aparece un **banner**
     superior con CTA *"Renovar plan"* → `?view=renovar`.
6. **Renovación** — `http://localhost/menu/admin/?view=renovar`: estado actual + selector
   de plan; en **modo demo** activa al instante (30 días, o sumados al periodo vigente).
7. **Tienda no disponible** — `Suspender` una tienda y abrir su menú por subdominio
   (p. ej. `http://localhost/menu/` con `Host: don-pepe.localhost`) → página **503
   "Menú no disponible"**.
8. **Permisos de usuarios** (arreglo de auditoría, ver §6) — entra como **dueño**
   (`admin/admin`) en `?view=users&opt=all`: **no** debe aparecer el super-admin ni
   usuarios de otras tiendas; intentar editar/borrar al super-admin rebota con
   *"No tienes permiso"*. Como **super-admin** sí se ven todos (y su propio rol no se
   puede degradar desde el formulario).
9. **Sin tiendas activas** (arreglo de auditoría, ver §6) — suspender la **última**
   tienda activa se rechaza con aviso; si se fuerza por SQL, la raíz responde **404
   "Sin tiendas activas"** (antes publicaba los datos de todas las tiendas mezclados).

---

## 2. Fase 6 — Super-admin de plataforma + renovación

### 2.1 Qué problema resuelve

Hasta ahora cada tienda vivía aislada (multi-tenant), pero **no había forma de ver ni
gestionar la plataforma completa** desde dentro: no existía un rol de operador, ni un
modo de recordar/cobrar renovaciones, ni una página para cuando una tienda vence. La
Fase 6 cierra ese hueco sin tocar el contrato del menú público.

### 2.2 Modelo y roles

- Nuevo rol **`user.role = 'superadmin'`**. En el layout admin, `$is_super` implica
  `$is_dueno` (mismo alcance de vistas) y añade el ítem **Plataforma**.
- El super-admin es el **único** que consulta la plataforma sin `Tenant::filter()`
  (a propósito, vía `PlatformData`), y lee la configuración siempre en `cfg_id=1`.
- La **impersonación** es explícita: `$_SESSION["impersonate_tienda"]` se honra en
  `Tenant::resolve()` **solo si** el usuario logueado es `superadmin`.

### 2.3 Archivos nuevos

| Archivo | Rol |
|---|---|
| `migracion_plataforma.sql` | Migración (aplicada). `recordatorio_at`, `saas_dias_aviso`, usuario `superadmin`. |
| `admin/core/app/model/PlatformData.php` | Datos globales: `cfg/setCfg`, `stats`, `tiendas`, `suscripciones`, `pedidos`, `slugify`, fechas. |
| `admin/core/app/view/platform-view.php` | Panel `?view=platform` (tabs `all`/`movimiento`/`planes`). |
| `admin/core/app/action/platform-action.php` | Acciones del panel: `estatus`, `extender`, `plan`, `recordar`, `entrar`, `salir`, `crear`, `cfg`. |
| `admin/core/app/view/renovar-view.php` | Vista `?view=renovar` (estado + selector de plan). |
| `admin/core/app/action/renovar-action.php` | Acción `?action=renovar` (demo activa; live crea checkout ZenoBank). |

### 2.4 Archivos modificados

| Archivo | Cambio |
|---|---|
| `admin/core/app/model/SuscripcionData.php` | `getLatestByTienda`, `setPlan`, `openRenewal`, `markRecordatorio`, `markCompleted` (**extiende**), `extend`, `diasRestantes`, `diasAviso`, `fechaFmt`, `tenantAlert`. |
| `admin/core/app/model/TiendaData.php` | `getAll`, `update`; `setEstatus` ahora también actualiza el objeto. |
| `admin/core/app/model/PlanData.php` | `getById`. |
| `admin/core/app/model/Tenant.php` | Impersonación (`impersonating()`) + salto al tenant impersonado si el usuario es super-admin. |
| `admin/core/app/layouts/layout.php` | `$is_super`, label "Super admin", ítem lateral **Plataforma**, títulos `platform`/`renovar`, **banner de renovación** y **banner de impersonación**; bump CSS `?v=13`. |
| `admin/core/app/action/order-action.php` · `users-action.php` · `pedidos-action.php` | Los guards aceptan `dueno` **o** `superadmin`. |
| `core/app/init.php` | Página **503 "Menú no disponible"** cuando el estatus de la tienda no es `activa` (solo en subdominios de tienda). |
| `admin/assets/css/admin-custom.css` | Estilos `.tt-tenant-banner--{info,warning,danger,imp}`. |
| `PLAN.md` / `AGENTS.md` | Documentada la Fase 6 (roadmap A5, decisiones, pruebas). |

### 2.5 Flujo de renovación (end-to-end)

```
Dueño abre su panel
   └─ SuscripcionData::tenantAlert()  →  banner "Tu plan está por vencer…"
        └─ CTA "Renovar plan"  →  ?view=renovar   (elige plan)
             └─ POST ?action=renovar
                  ├─ modo DEMO  →  openRenewal() + markCompleted() (extiende 30 días)
                  │                 + tienda.estatus = 'activa'
                  │                 →  ?view=renovar&pago=exito
                  └─ modo LIVE  →  ZenoBank::createCheckout(…)  →  redirect a pay.zenobank.io
                                    └─ al aprobar:  webhooks/zenobank.php
                                         →  markCompleted() (extiende) + tienda.estatus = 'activa'
```

**Clave de diseño:** se **reusa la fila `suscripcion`** de la tienda (no se crea una
nueva). `markCompleted()` calcula la base como `periodo_fin` si aún está vigente, si no
`hoy`, y suma 30 días → renovar antes de vencer **no pierde días**.

### 2.6 Reglas de aviso (`SuscripcionData::tenantAlert`)

| Situación | Nivel | Mensaje (resumen) |
|---|---|---|
| Tienda `suspendida` | danger | "Tu tienda está suspendida" |
| Pago `OPEN` | info | "Tienes un pago pendiente" |
| `periodo_fin` vencido | danger | "Tu plan venció hace N días" |
| Vence en ≤ `saas_dias_aviso` días | warning | "Vence en N días (dd/mm/aaaa)" |
| `EXPIRED` / `PARTIALLY_PAID` | danger | "Pago incompleto" |
| Cliente migrado sin suscripción | — | sin aviso (no molesta a la pizzería original) |

`saas_dias_aviso` es configurable desde **Plataforma → Aviso de vencimiento** (default 7).

---

## 3. Lo entregado antes (resumen)

| Fase | Qué es | Migración | Estado |
|---|---|---|---|
| A0/A2 | Multi-tenant + subdominios (Host header) + tienda #1 (la pizzería) | `migracion_multitenant.sql`, `migracion_a2.sql` | ✅ |
| A1 | Landing + registro + planes + pago Zenobank (demo/live) | `migracion_saas.sql` | ✅ |
| C1 | Config por tienda (`configuration.cfg_id`) + **carta imprimible** (`?action=carta`) | `migracion_config_tienda.sql` | ✅ |
| A3 | Paneles por rol (Cocina KDS / Repartidor) | — | ✅ |
| A4 | Orden del menú con **drag & drop** (`product.position`) | `migracion_a4.sql` | ✅ |
| Fase 1 | Motor de tema (`configuration` `tema_*`) + toggles de sección | `migracion_tema.sql`, `migracion_esquema.sql` | ✅ |
| Fase 2 | Imágenes de categoría, logo propio, sección de ofertas | `migracion_fase2.sql` | ✅ |
| Fase 3 | Tema 100% variable (sin colores de marca hardcodeados) | `migracion_fase3.sql` | ✅ |
| Fase 4 | Funciones opt-in: etiquetas, agotado, franja de oferta, favoritos, compartir, modo lista | `migracion_fase4.sql` | ✅ |
| **Fase 6** | **Super-admin de plataforma + recordatorio/renovación** | `migracion_plataforma.sql` | ✅ |
| Fase 5 | Refactor de admin | — | ⏳ (trabajo futuro del usuario) |

---

## 4. Migraciones (todas aditivas e idempotentes)

```
migracion_multitenant.sql   migracion_a2.sql        migracion_tema.sql
migracion_esquema.sql       migracion_config_tienda.sql   migracion_a4.sql
migracion_saas.sql          migracion_fase2.sql     migracion_fase3.sql
migracion_fase4.sql         migracion_plataforma.sql   ← nueva (Fase 6)
```

Aplicar la de Fase 6 en una BD existente:

```bash
mariadb -u root tacomenu < migracion_plataforma.sql
```

> **No re-importar `schema.sql`** sobre la BD viva (empieza con `CREATE DATABASE` y
> contiene `DROP`).

---

## 5. Verificación ejecutada (Fase 6)

**Sintaxis** — `php -l` sin errores en: `platform-view.php`, `platform-action.php`,
`PlatformData.php`, `renovar-view.php`, `renovar-action.php`, `SuscripcionData.php`,
`TiendaData.php`, `PlanData.php`, `Tenant.php`, `layout.php`, `core/app/init.php`,
`order-action.php`, `users-action.php`, `pedidos-action.php`.

**Smoke HTTP** (sesión real con cookie):

| Prueba | Resultado |
|---|---|
| Login `superadmin/superadmin` | ✅ sesión iniciada |
| `?view=platform` (super-admin) | ✅ 200, KPIs + listado |
| `?view=platform&opt=movimiento` / `opt=planes` | ✅ 200 |
| `?action=renovar` (demo, plan Restaurante) | ✅ `suscripcion` COMPLETED, periodo 14/10 → 13/11 |
| `?action=platform&opt=recordar&id=1` | ✅ `recordatorio_at` grabado |
| `?action=platform&opt=entrar&id=1` + `?view=home` | ✅ banner "Estás viendo como …" |
| `?action=platform&opt=salir` | ✅ vuelve a Plataforma |
| `?action=platform&opt=crear` (tienda "Don Pepe") | ✅ tienda + suscripción creadas |
| Menú público `Host: don-pepe.localhost` | ✅ 200 (activa) |
| Suspender → `Host: don-pepe.localhost` | ✅ **503** "Menú no disponible" |
| Reactivar | ✅ 200 de nuevo |
| Plan por vencer (3 días) → banner dueño | ✅ warning "Vence en 3 días" |
| Plan vencido → banner dueño | ✅ danger "Tu plan venció" |
| `?view=platform` como **dueño** | ✅ redirige (no ve contenido) |
| `?action=platform…` como **dueño** | ✅ redirige (no ejecuta) |
| `?action=order&opt=cats` como super-admin | ✅ `{"ok":true,"n":…}` (guard OK) |
| Smoke páginas principales (público, landing, home/products/categories/order/sells/settings/apariencia/clients, `?action=carta`) | ✅ 200 |

**Datos de prueba que quedaron en la BD local** (para que la revisión se vea con vida):
tienda **#9 "Don Pepe"** (`don-pepe`, activa, plan Emprendedor, vence 17/10/2026) y su
suscripción. Se borra desde `?view=platform` o con SQL cuando ya no haga falta.

> Las pruebas que tocaron configuración o pagos **restauraron el estado**: `zeno_mode=demo`,
> `zeno_api_key` y `zeno_webhook_secret` vacíos, y las suscripciones tal como estaban
> (#5 tienda 1: plan Restaurante, 14/10 → 13/11, 19.00 · #6 tienda 9: 14/10 → 17/10).

---

## 6. Auditoría de la Fase 6 y arreglos aplicados

Se pasó una **revisión de código** sobre el diff de la Fase 6. Resultado: **1 bloqueante**
y 2 medios — todos **arreglados y verificados**; el resto quedó como deuda documentada.

### 6.1 Arreglados

| # | Sev. | Hallazgo | Arreglo |
|---|---|---|---|
| 1 | **ALTA** | Un **dueño cualquiera** podía editar o borrar al **super-admin** desde `?view=users` (le cambiaba la contraseña, lo degradaba o lo eliminaba) → escalada total a la plataforma. Además la tabla listaba usuarios de **otras tiendas**. | Nuevos `UserData::puedeTocar()` / `getAllVisibles()`: al super-admin sólo lo toca otro super-admin y cada dueño ve/edita sólo su tienda. El formulario ya no ofrece el rol cuando el objetivo es super-admin (no se puede degradar) y el action **conserva el rol** si el campo no viene. |
| 2 | MEDIA | **Doble checkout en live**: el segundo clic en "Renovar" pisaba `zeno_checkout_id`; si el cliente pagaba el primero, el webhook no hallaba la fila → **cobro sin renovar**. | `renovar-action`: si hay checkout pendiente del mismo plan se **reutiliza** (o se acredita si la API lo da por COMPLETED). `webhooks/zenobank.php`: si el id no coincide, se localiza la fila por el **`orderId`** del payload (`saz-<tienda>-<sus>` / `renov-<tienda>-<sus>-<ts>`). |
| 3 | MEDIA | Sin ninguna tienda activa, el fallback multi-tenant caía en `1=1` y la **raíz publicaba los datos de todas las tiendas mezclados**. | `core/app/init.php`: sin tienda resuelta → **404** ("Sin tiendas activas" en la raíz, "Tienda no encontrada" en un subdominio ajeno). Además `?action=platform&opt=estatus` **no permite suspender la última tienda activa**. |
| 4 | BAJA | El alta manual con datos de dueño inválidos creaba la tienda **sin dueño y sin avisar**; la contraseña se veía en claro. | Validación (`FILTER_VALIDATE_EMAIL` + mín. 6) **antes** de crear nada; input `type="password"`. |
| 5 | BAJA | El KPI "Suscripciones este mes" sumaba por `periodo_inicio` (no por fecha de cobro) → engañoso. | Etiqueta honesta: "Pagos con periodo que inicia este mes". |
| 6 | BAJA | `TiendaData::update()` sin llamadores (riesgo de drift). | Eliminado. |
| 7 | — | **Pre-existente**: `UserData::add()` descartaba `username` en silencio (el login acepta email **o** username y el formulario lo pide); un `user_id`/`id` vacío provocaba **Fatal error 500** en el CRUD. | Se guarda `username`; los ids se castean a `(int)` (id 0 → "sin permiso", ya no rompe). |

### 6.2 Falso positivo (y endurecimiento)

**Husos horarios.** El informe decía que el panel corría en `Europe/Berlin` porque
`php.ini` tiene `date.timezone=Europe/Berlin`. **Verificado en vivo: no es así** — cada
entry point fija `America/Caracas` antes de ejecutar nada (probe temporal en el pipeline
real: `TZ=America/Caracas | NOW=12:34 | UTC=16:34`, idéntico al `NOW()` de MySQL).
Aun así se **endureció**: la zona del negocio ahora se fija también en `core/autoload.php`
y `admin/core/autoload.php`, de modo que cualquier entry nuevo (cron, endpoint,
`login.php`) la hereda sin depender del `php.ini`.

### 6.3 Verificación de los arreglos

| Prueba | Resultado |
|---|---|
| Dueño → POST `?action=users&opt=upd` con `user_id=7` (super-admin) | ✅ "No tienes permiso"; hash, rol y nombre intactos |
| Dueño → `?action=users&opt=del&id=7` | ✅ bloqueado; el usuario sigue existiendo |
| Dueño → `?view=users&opt=edit&id=7` | ✅ rebota con aviso |
| Dueño → `?view=users&opt=all` | ✅ no lista al super-admin |
| Dueño edita un usuario de **otra tienda** (creado en la #9) | ✅ bloqueado y no listado; el super-admin sí lo ve |
| Super-admin → `?view=users&opt=edit&id=7` | ✅ sin selector de rol (no se puede degradar) |
| Ciclo normal alta → edición → baja como dueño | ✅ funciona; `username` ahora se guarda |
| `user_id` vacío en `upd` | ✅ "sin permiso" (antes: Fatal error) |
| Webhook **firmado** (Svix) con checkout **huérfano** + `orderId` | ✅ acredita por `orderId`: COMPLETED, 17/10 → 16/11 |
| Webhook sin `orderId` / con `orderId` inexistente | ✅ `ignored`, sin errores |
| Firma inválida | ✅ 403 |
| Reenvío del mismo `checkout.completed` | ✅ idempotente (no vuelve a extender) |
| Modo live con API key inválida | ✅ degrada a `err=zeno` sin tocar la suscripción |
| Suspender la **última** tienda activa | ✅ rechazado con aviso |
| Todas las tiendas suspendidas (por SQL) → raíz / subdominio | ✅ 404 "Sin tiendas activas" / 503 |
| Barrido de 22 páginas (público, landing, admin, plataforma, usuarios, carta…) | ✅ 200 y **sin warnings/notices/fatales** |

---

## 7. Contrato invisible respetado

No se renombró ni eliminó ninguno de los identificadores del menú público:
`#btn_confirm_order`, `#product_search`, `#grid-title`, `.btn-category-ajax`,
`data-cat`, `openExtrasModal`, `$_SESSION["cart"]`, `blissfull_sede_id`.
El menú público se sirve íntegro (smoke 200) y la Fase 4 sigue siendo **opt-in**.

---

## 8. Pendientes / limitaciones conocidas

- **Límites de plan** (`max_products`, `max_sedes`, `kds`, `drag_drop`, `theme`,
  `reports`) están en la tabla `plan` pero **no se aplican** automáticamente (hoy son
  informativos en la pestaña Planes).
- **Gate por suscripción vencida**: el front público **no** se bloquea automáticamente
  al vencer; el dueño ve el banner y la suspensión la hace el super-admin (o el webhook
  al pagar). No hay cron de vencimiento.
- **Sin emails ni recobro automático**: el recordatorio es **manual** (WhatsApp desde el
  panel). No hay envío programado.
- **Sin historial de cambios** de plataforma (ni auditoría de impersonación).
- **Semántica del KPI de dinero**: `ingresos_mes` suma `paid_amount` por *inicio* del
  periodo (no hay columna de fecha de cobro) y `extend()` manual no aporta monto; la
  etiqueta ya lo aclara, pero no es contabilidad.
- **Impugnación de pagos**: cubierto el doble checkout y el webhook que no encuentra la
  fila; no hay conciliación/reembolsos (fuera de alcance).
- **Demo → Live** de Zenobank está listo en código pero **apagado**: `zeno_api_key` y
  `zeno_webhook_secret` están vacíos y `zeno_mode=demo`.
- **Fase 5** (refactor de admin) sin tocar, como se acordó.

---

## 9. Pasar de demo a live (Zenobank)

En la tabla `configuration` (claves base `cfg_id=1`), o por SQL:

```
zeno_api_key        = sk_...            (dashboard.zenobank.io → Developers)
zeno_webhook_secret = whsec_...         (firma Svix)
zeno_mode           = live
zeno_price_currency = USD               (opcional)
saas_landing_url    = https://…/landing (opcional; si vacío usa el host actual)
saas_base_domain    = sazon.app         (dominio raíz de los subdominios)
saas_alt_domains    = otros.dominios    (separados por espacio)
```

Registrar el webhook en Zenobank → `https://<host>/webhooks/zenobank.php`
(eventos `checkout.completed`, `checkout.expired`, `checkout.partially_paid`).

---

## 10. Notas y rarezas a tener presentes

- **`View::load()` duplica la vista** si se usa `?view=X` con una vista que declara
  funciones → las páginas standalone van por **action** (la carta ya lo hace).
- **Sesión PHP host-only**: cada subdominio tiene cookie propia (aislamiento natural del
  carrito); el guard `cart_tienda` cubre el caso de sesiones compartidas.
- **MariaDB de XAMPP se cae con frecuencia**: si no conecta, reinicia `mysqld` y limpia
  `master-*`/`mysql-relay-bin-*`/`*.dmp` en `C:\xampp\mysql\data\`.
- El **password del admin** es `admin` (`sha1(md5("admin"))`); el del super-admin es
  `superadmin`. Ambos deben cambiarse en producción.
