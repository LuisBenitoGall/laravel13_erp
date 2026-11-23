# Mapa de capabilities (contexto on-demand)

Índice breve para agentes 10/20/25/30: **qué cargar** según el tipo de trabajo.
No sustituye `openspec/specs/` ni `docs/standards/`; evita inyectar el catálogo
entero en cada hilo.

Uso: abrir este fichero + las filas del patrón que aplique + solo las specs del
change (`openspec/changes/<change>/specs/`). Skills: activar on-demand.

Always-on Cursor (presupuesto): `00-project` + `01-kernel` + `laravel-boost` +
`codegraph`. Detalle: `docs/standards/tooling.md` (bloque Boost solo en
`CLAUDE.md`).

## Patrones → contexto mínimo

| Patrón de change | Standards | Capabilities / specs típicas | Skills |
|---|---|---|---|
| **Módulo nuevo (SaaS)** | kernel · module-playbook · module-map · architecture · frontend · templates-* · glossary · libraries | ficha `docs/modules/<M>.md`; specs del change; `tenant-module-access-contract` / menú si toca capas | `new-module` · `openspec-propose`/`openspec-apply-change` · `tenancy-rbac` |
| **Entidad tenant CRUD** | architecture · frontend · templates-* | `server-table-reference` · `form-kit` · spec de la entidad | `laravel-best-practices` · `inertia-react-development` · `tenancy-rbac` |
| **Catálogo global Admin** | architecture · frontend | specs `admin-*-catalog` hermanas; `admin-sole-domain` si aplica | `laravel-best-practices` · `inertia-react-development` |
| **Shell / menú / branding** | frontend · kernel (menú) | `shell-*` · `menu-*` · `erp-branding` | `inertia-react-development` · `tenancy-rbac` |
| **Auth / login / registro** | kernel | `admin-ui-login` · `auth-*` · `password-reset` | `tenancy-rbac` · `laravel-best-practices` |
| **Storage / ficheros** | architecture | specs de la entidad (p. ej. `user-images`, `admin-gallery`, `user-docs`) | `laravel-best-practices` |
| **Solo hardening / tooling** | tooling · kernel si authz | delta del change | `agent-setup` si deps/MCP |

## Ejemplo de referencia: RRHH (oleada 1)

Change: `c2026-11-23-rrhh-contract-types-employees`. Ficha: `docs/modules/HR.md`.

| Qué | Cargar |
|---|---|
| Dominio | `App\Domains\HR`; Module slug `rrhh` (Optional) |
| Entidades | `ContractType` (`contract-types`), `Employee` (`employees`) |
| Standards | kernel · playbook · module-map (HR→Admin D) · architecture · frontend · glossary (Empleo ≠ UserCompany) |
| Specs del change | `hr-contract-types` · `hr-employees` · `hr-module` · (mapa = este doc) |
| Reutilizar | Workplace (Admin, solo lectura) · User/UserCompany · ServerTable · form kit |
| Skills | `new-module` · `tenancy-rbac` · `openspec-*` · `laravel-best-practices` · `v1-reference` (solo lectura) |
| Tests mínimos | R-TEN-05 por entidad · V-21 acceso módulo · membresía user_companies · ≤1 active · renovación |
| No cargar | resto de `openspec/specs/*` Admin/catálogos; fichajes/calendarios (NonGoals) |

Al archivar el change, mantener esta fila alineada con las specs principales
archivadas (`openspec/specs/hr-*` tras sync/archive).

## Mantenimiento

- Cada módulo nuevo añade una fila “Ejemplo” o amplía la tabla de patrones si
  introduce un tipo de trabajo distinto.
- No duplicar el contenido de las specs aquí: solo punteros.
