# Módulo HR (RRHH) — tipos de contrato y empleados

> Ficha playbook §1. Change: `c2026-11-23-rrhh-contract-types-employees`.
> Module SaaS slug: **`rrhh`**. Dominio PHP: `App\Domains\HR`.

## Propósito y alcance

**Entra (oleada 1):**

- Catálogo **ContractType** (`contract_types`): tipos de contrato laboral por
  company (CRUD ServerTable + formularios).
- **Employee**: periodos laborales de un User en una company (alta, renovación,
  cierre; ≤1 `active` por user+company).
- Module `rrhh` + Functionalities `contract-types` y `employees` + menú +
  permisos Spatie.
- Mapa de capabilities de referencia (`docs/standards/capabilities-map.md`)
  con RRHH como primer ejemplo.

**No entra (en dominio HR):**

- Fichajes (`TimeEntry`), planning, vehículos.
- Vencimientos/notificaciones programadas de contratos.
- Alta de User desde el flujo de empleo.
- Empleado sin fila en `user_companies` de la company.
- Importación de datos v1 (tablas vacías; Viuda Vila no usa la funcionalidad).

**Calendarios laborales:** viven en el dominio **Calendar**
(`docs/modules/Calendar.md`). HR/Module `rrhh` solo los **consume** (menú
`calendars` / `day-types`, rutas `/rrhh/calendars*` y `/rrhh/day-types*`).

## Entidades

| Entidad | Tabla | Dominio | Notas |
|---|---|---|---|
| `ContractType` | `contract_types` | HR | `BelongsToCompany`; soft delete; duration en **días** (`null` = indefinido) |
| `Employee` | `employees` | HR | Periodo laboral; `BelongsToCompany`; FK User + ContractType + Workplace? |

`Workplace` y `User` / `user_companies` se **leen** desde Admin (no se escriben
desde HR).

## Dependencias

- **Admin** (matriz module-map: HR → Admin = D): Company sesión, User,
  UserCompany, Workplace.
- **Admin → HR = D** (lectura): tab Empleos en ficha Edit User proyecta
  periodos `Employee` multi-company en solo lectura (R-TEN-10); sin mutar
  desde Admin.

## Librerías

Ninguna fuera del canon (`docs/standards/libraries.md`).

## Permisos y roles

| Functionality slug | Permisos Spatie | Roles seed (matriz) |
|---|---|---|
| `contract-types` | `contract-types.{view,viewAny,create,update,delete}` + comodín `rrhh.access` | manager: CRUD; guest: view (ajustar en seeder como catálogos tenant) |
| `employees` | `employees.{view,viewAny,create,update,delete}` + `rrhh.access` | manager: CRUD; guest: view |

Module level: **Optional** (activable SaaS; no Basic).

## Referencia v1

| v1 | v2 |
|---|---|
| `Contract` / `contracts` | `ContractType` / `contract_types` |
| `Employee` / `employees` (periodo; company vía join a contracts) | `Employee` + **`company_id`** explícito |
| `EmployeeController` / `ContractController` | Controllers HR + Actions |
| Renovar ≈ nuevo periodo | Cerrar `active` → `ended` + crear periodo nuevo |
| Requisito implícito user en company | **Explícito:** exige `user_companies` |

Rutas v1: `admin/contracts*`, `admin/employees*`. No clonar Livewire/Blade/yajra.

## Migración de datos v1→v2

**No aplica** en oleada 1 (0 filas en local; producto no usado por Viuda Vila).

## Checklist de vinculaciones (§6)

| Id | ¿Aplica? | Nota |
|---|---|---|
| V-01 | No | Sin stock |
| V-02 | No | Sin asientos |
| V-03 | No | Sin efectos |
| V-04 | No | Sin numeración de documento |
| V-05 | No | Sin PDF/Excel propio (export ServerTable genérico OK) |
| V-06 | No | Sin notificaciones/realtime (vencimientos = oleada posterior) |
| V-07 | Sí | Permisos `contract-types.*` / `employees.*` + `rrhh.access` |
| V-08 | Sí | Menú Module `rrhh` + ítems Functionalities |
| V-09 | Sí | Module activable `rrhh` (Optional) |
| V-10 | No | Sin portal |
| V-11 | No | Sin multi-almacén |
| V-12 | No | name string; sin Spatie translatable obligatorio en oleada 1 |
| V-13 | No | Sin patrones documento |
| V-14 | No | Sin series |
| V-15 | No | Sin seeder de datos de negocio masivo |
| V-16 | Sí | Cron `hr:sync-employee-statuses` (scheduled→active; active→ended por caducidad) |
| V-17 | No | Sin settings hub nuevos |
| V-18 | No | Sin side-effect CreateCompany |
| V-19 | No | Sin onboarding steps nuevos |
| V-20 | Sí | Activity log en ContractType y Employee (maestros/documentos relevantes) |
| V-21 | Sí | Rutas `/rrhh/*` bajo `module:rrhh` + Feature acceso tenant |
| R-TEN-05 | Sí | Ambas entidades con `company_id` |
| E2E | N/A | No toca flujos de `e2e.md` §4; Feature HTTP basta |

## Decisiones de producto (explore)

1. Empleado solo si User ∈ `user_companies` de la company.
2. Naming `ContractType` / `contract_types`.
3. ≤1 periodo `active` por (user, company); renovar = cerrar + crear.
4. Alta solo User existente.
5. Duration del tipo en **días**; soft-delete tipos sí; Module slug **`rrhh`**.
6. Status Employee: enum `scheduled` | `active` | `ended`.
