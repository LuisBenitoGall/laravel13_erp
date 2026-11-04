# Design

## Context

See proposal.md — Why. v1: tabla `accounting_account_types` (autoreference, code,
name, mode), softDeletes, Index DataTables, Create página / Edit modal (solo
name), `show` con árbol duplicado + `trans('fiscal.*')`, seeder anidado PGC,
`select` AJAX diferido. v2: patrón Configuración (modales, ServerTable).

## Goals / Non-Goals

**Goals:**

- Catálogo jerárquico en Admin + Index CRUD + show asistente + seeder i18n.

**Non-Goals:**

- Cascada `select`; CRUD AccountingAccount; status Active/Inactive; E2E.

## Decisions

### D1 — Admin + slug `accounting-account-types`
**Elección:** `app/Domains/Admin`; Functionality y permisos
`accounting-account-types.*`; rutas `admin/accounting-account-types*`. module-map:
entidad en Admin (catálogo Configuración); Accounting **lee** en fase cuentas.
**Descartado:** carpeta Accounting ahora (solo referencia).

### D2 — Esquema v1
**Elección:** `autoreference` int (0=raíz; si >0 = id padre), `code` string,
`name` string, `mode` char(2) nullable (valor `group` del seeder: false→null,
`'p'`, etc.), softDeletes. Level en listado = `strlen(code)` (computed, no
columna). Sin status.
**Descartado:** renombrar autoreference en este change (rompería semántica
seeder/v1).

### D3 — Seeder + i18n fiscal
**Elección:** Portar estructura/lógica de
`AccountingAccountTypesTableSeeder`; nombres con `__('fiscal.xxx')` / claves
equivalentes. Portar `lang/es/fiscal.php` (y en/ca: traducir o fallback es).
Fuera de DatabaseSeeder; README. Idempotente: vaciar/reseed solo en limpio o
`updateOrCreate` por code — preferir truncate/delete + insert en entorno limpio
documentado.
**Descartado:** nombres hardcode solo ES sin claves.

### D4 — Show asistente desde BD
**Elección:** Show construye el árbol desde filas sembradas (autoreference/code),
labels = `name` persistido (ya resuelto al seed según locale del artisan). Evita
duplicar 3k líneas del blade v1. CTA desde Index (ActionBar o acción) hacia show.
**Descartado:** árbol estático i18n duplicado del blade (doble mantenimiento).

### D5 — UI CRUD
Index ServerTable: code, name, level; Acciones Edit modal + Delete; Create modal
(code + name; autoreference default 0 o selector padre opcional — **mínimo v1
create**: code+name, autoreference=0; hijos del seeder ya cubren PGC; altas
manuales raíz o 10 documenta si Create permite padre).
Edit: solo name; code readonly/inmutable.
Default sort: `code` ASC (sin Active-first).
**Descartado:** Create página full; toggle status.

### D6 — Delete
Soft delete. Si hay hijos no trashed, bloquear (como geografía) o soft-cascade —
**bloquear** por defecto.

### D7 — Vinculaciones / E2E
| Id | ¿Aplica? | Nota |
|---|---|---|
| V-07 | Sí | Functionality + permisos |
| V-08 | Sí | Menú Grupos contables |
| V-09 | No | Módulo admin |
| R-TEN-05 | N/A | Global; test cross-company |
| E2E | N/A | Feature tests |

## Risks / Trade-offs

- [Seeder locale] → Ejecutar seed con locale `es` (o App::setLocale) para names
  ES canónicos; documentar.
- [Show vs names en otro locale] → Names en BD fijos al idioma del seed; show no
  re-traduce. Aceptable fase 0; mejora futura = keys en BD.
- [Volumen filas] → Aceptable; tests smoke count > N / code `1` existe.

## Migration Plan

1. Migración → Functionality → port fiscal i18n → seeder → menú + Index/Show.
2. Rollback: menú/Functionality; dropear tabla si no hay FKs.

## Open Questions

Ninguna. Create con padre opcional: 20 sigue mínimo v1 (raíz autoreference=0)
salvo que el form v1 create permita más — revisar create blade en apply.
