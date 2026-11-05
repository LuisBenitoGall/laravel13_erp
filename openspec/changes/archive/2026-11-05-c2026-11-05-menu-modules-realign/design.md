# Design

## Context

Hoy `MenuBuilder` mezcla catálogo BD con raíces sintéticas (`dashboardItem`,
`empresasBlock`, `configurationBlock`, `CONFIGURATION_SLUGS`,
`FUNCTIONALITY_GROUPS`) y Modules `admin` / `workspace`. El kernel y
`architecture.md` R-AUT-08 documentan OR (comodín **o** `viewAny`). Brief
explore + Luis: raíces solo desde `modules`, fusión de slugs al PDF MENU_ERP
(mínimo), permisos **AND**, cache de catálogo. Ver `proposal.md` — Why.

## Goals / Non-Goals

**Goals:**

- Menú 100 % data-driven: raíz = Module; hijo = Functionality (o Module hoja).
- Migrar catálogo mínimo + reasignar Functionalities; retirar `admin`/`workspace`.
- AND Spatie en menú y acceso; alinear kernel + architecture + rules.
- Cache catálogo Active; filtrado company/permisos por request.
- Middleware `module:{slug}` y tests al nuevo mapa de slugs.

**Non-Goals:**

- Sembrar Optional del PDF (RRHH, Catálogo, Stocks, …).
- Pantalla «Todos», polish tiles company-modules, landings marketing.
- Campo `sort` nuevo en menú; URLs anidadas por Module.
- Cambiar reglas Basic/Optional/AdminOnly de las tres capas.

**E2E:** no aplica (`e2e.md` §4) — cobertura Feature/Pest existente.

## Decisions

### D1 — Mapa Modules mínimos (levels PDF)

| slug | level | hoja | notas |
|---|---|---|---|
| `dashboard` | Basic | sí | href `/dashboard`; exige `dashboard.access` |
| `cuenta` | Basic | no | hijos: `profile` (menú→profile existente), `company-modules` («Mis módulos») |
| `usuarios` | Basic | no | `company-users`, `company-roles` |
| `empresas` | Basic | no | `companies` |
| `configuracion` | AdminOnly | no | catálogo SA + `company-module-authorizations` |

**Alternativa descartada:** mantener `admin`/`workspace` y solo renombrar labels —
sigue desalinear el PDF y el hardcode.

### D2 — Reasignación Functionality (slugs inmutables)

| Functionality | Module destino |
|---|---|
| `modules`, `functionalities`, `roles`, `permissions`, `countries`, `currencies`, `banks`, `units`, `vat-types`, `accounting-account-types`, `company-module-authorizations` | `configuracion` |
| `companies` | `empresas` |
| `company-users`, `company-roles` | `usuarios` |
| `company-modules` | `cuenta` |
| `profile` (nueva fila catálogo si no existe; **sin** pantalla nueva) | `cuenta` → `profile.show` / ruta profile existente |

`company-roles`: bajo `usuarios` (ítem PDF no listado; no inventar Module).
`company-module-authorizations`: bajo `configuracion` (herramienta SA; no está en
leyenda Configuración del PDF pero permanece en menú si Active).

Tras migración: soft-delete o hard-retirar Modules `admin` y `workspace` (sin
Functionalities huérfanas); provisionar comodines
`dashboard|cuenta|usuarios|empresas|configuracion.access`; migrar grants
`admin.access` → `configuracion.access`, `workspace.access` → repartir a
comodines Basic según rol Manager sync.

### D3 — Permisos AND (corrige kernel)

Para **ver/usar** una Functionality: `{module}.access` **y**
`{functionality}.{acción}` (menú: `viewAny`). Module hoja (`dashboard`): solo
`{module}.access` (+ tres capas). Comodín **ya no implica** todas las funcs.
Super Admin: `Gate::before` intacto; perímetro company intacto.
Excepción menú SA AdminOnly: Module `configuracion` Active visible para SA
**sin** exigir authorize/enable en company (misma semántica que hoy el bloque
Configuración); rutas: bypass `EnsureModuleIsActive` AdminOnly+SA intacto con
slug `configuracion`.

**Standards en este change (excepción explícita Luis):** actualizar
`docs/standards/kernel.md` §4–5, `architecture.md` R-AUT-08 + anti-patrón AND,
`.cursor/rules/01-kernel.mdc` / CLAUDE adaptadores que citen OR. No es
«inventar negocio»: corrige el estándar contradicho por decisión de producto.

### D4 — MenuBuilder sin pilares hardcode

1. Cargar catálogo cacheado (Modules Active + Functionalities Active ordenadas).
2. Filtrar por company: Active∩autorizado∩encendido; **o** SA+AdminOnly Active.
3. Permisos: Module hoja → comodín; con hijos → comodín **y** `viewAny` por ítem;
   omitir raíz si no queda ningún hijo (salvo hoja).
4. Payload: Module puede llevar `href` opcional (hoja) y `functionalities[]`.
5. Eliminar `CONFIGURATION_SLUGS` / `FUNCTIONALITY_GROUPS` / bloques sintéticos.
6. `hrefFor` / labels especiales pueden permanecer como mapa de rutas (no son
   raíces). Criterio de orden: el actual (`orderBy('slug')` o equivalente).

**Front:** aside debe tratar Module con `href` y `functionalities` vacío como
enlace directo (Dashboard).

### D5 — Rutas y middleware

- Prefijo `/admin/...` = zona ERP autenticada; paths planos (sin `/admin/{module}/…`).
- Sustituir `module:admin` → `module:configuracion` en `routes/domains/admin.php`,
  `partners.php` (donde aplique) y tests.
- `module:workspace` → `module:usuarios` / `module:empresas` / `module:cuenta`
  según Functionality del grupo de rutas (Members/`company-users` → `usuarios`;
  company-modules → `cuenta`; companies → `empresas`; company-roles → `usuarios`).
- Catálogo Modules Index puede seguir fuera de `module:configuracion` si hoy está
  fuera de `module:admin` para reactivar Inactive (preservar ese escape hatch;
  documentar en task).

### D6 — Cache catálogo

- Clave estable p.ej. `menu.catalog.active` (versión bump si hace falta).
- Contenido: árbol Modules Active + Functionalities Active (ids, slugs, labels,
  level, icon, color) — **sin** company ni user.
- Invalidar en Actions CUD de Module y Functionality (y restore si existe).
- No JSON en disco como fuente de verdad.

**Alternativa descartada:** Redis-only / file cache custom — Cache Laravel basta.

### D7 — Seeder / migración de datos

- `AdminCatalogSeeder` idempotente: upsert Modules nuevos; reassign
  `module_id`; crear `profile` si falta; autorizar+encender Basics en companies
  demo como hoy con `workspace`.
- Migración one-shot (o comando Artisan invocado por seeder) para entornos ya
  sembrados: reassign + retire admin/workspace + rename permisos comodín.
- Sync Manager: incluir comodines Basic nuevos y permisos de funcs reubicadas.

### D8 — Documentación de trabajo PDF

- Nota corta en `docs/modules/Admin.md` (o anexo menú): mapa PDF = backlog
  ordenado (1 change ≥ Module; ≥1 por Functionality al abrirla); no sembrar
  Optional vacíos ahora.

## Risks / Trade-offs

- [Roles con solo `viewAny` sin comodín] → pierden menú/acceso hasta sync Manager /
  reasignación — mitigar migración de permisos + tests AND.
- [SA Configuración sin capas] → sigue siendo excepción documentada; no ampliar a
  Basic/Optional.
- [Module hoja vs glosario «Module ≠ ítem submenú»] → actualizar kernel: Module
  puede ser hoja de menú si no tiene Functionalities; Functionality sigue siendo
  la unidad de permiso de acciones.
- [Tests/E2E acoplados a `module:admin`] → barrido obligatorio en tasks.

## Migration Plan

1. Standards (kernel/architecture/rules) + specs delta.
2. Migración/seeder catálogo + permisos.
3. MenuBuilder + cache + front hoja.
4. Rutas middleware.
5. Tests Feature menú/capas/shell; Pint/PHPStan afectados.
6. Rollback: revertir change + restaurar seed `admin`/`workspace` (no hot-swap
   parcial AND/OR).

## Open Questions

Ninguna bloqueante; D2 cierra `company-roles` y `company-module-authorizations`.
