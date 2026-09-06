## Context

Kernel tooling (`c2026-07-16-kernel-tooling-fase0`) verificado: Pest/Pint/PHPStan verdes;
generador y Support listos; `--global` especificado en `tooling.md` pero aún no
implementado. Frontend bootstrap archivado. Este change materializa el dominio Admin
(ficha `docs/modules/Admin.md`) como Capa 1+2 de Fase 0.

Referencia normativa: R-AUT-01..08, R-TEN-01 excepción `--global`, frontend.md §2–§3,
module-map (Admin Capa 1).

## Goals / Non-Goals

**Goals:** `--global` en el generador; Module + Functionality reales; Spatie Teams +
Super Admin + usuario seed; menú R-AUT-08 sin CompanyModules; AppLayout + ServerTable +
forms; login/logout.

**Non-Goals:** Company/CompanyModules/roles por empresa/user_companies; poblar
`role_has_permissions` para roles no-superadmin; registro/reset password; otros
catálogos globales; activity log (V-20 diferido); migración datos v1 (V-15 diferido).

## Decisions

1. **`--global` primero, luego generar Module/Functionality** con el generador real
   (`Admin Module|Functionality --global --status --translatable`). Alternativa
   descartada: escribir a mano sin generador — derrota el propósito del kernel.

2. **Spatie Teams** (`teams => true`, `team_foreign_key => company_id`): roles/permisos
   con `company_id` nullable nativo. Alternativa descartada: columna "Universal" de v1.

3. **Super Admin** nombre exacto que Spatie reconoce; `company_id = NULL`; bypass solo
   vía `Gate::before` (R-AUT-03). Verificar en implementación el comportamiento de
   `hasRole`/`Gate` con team NULL contra la doc de la versión instalada (riesgo del
   brief) — si hace falta setPermissionsTeamId(null) o equivalente, documentarlo en
   código/tests.

4. **Permisos**: prefijo = slug de Functionality; comodín `<module-slug>.access` con
   punto (no `module_<slug>` de v1). Acciones 5, no 7.

5. **Menú**: un solo builder servidor → prop Inertia compartida; color/icon desde Module;
   hueco de logo tenant en AppLayout (sin tenant real aún).

6. **UI**: adaptar patrones shadcn-admin (MIT) a `Components/`/`Layouts/`; no clonar
   scaffold; Tabulator solo vía `ServerTable` (R-FE-07).

7. **Credenciales seed**: `config` + env (p. ej. `SUPERADMIN_EMAIL` /
   `SUPERADMIN_PASSWORD`); fallar el seeder en local si faltan, o valor por defecto solo
   en `local`/`testing` documentado — nunca commit de secretos.

8. **Arch ignoring**: modelos `--global` excluidos de la regla BelongsToCompany.

## Excepciones a standards

1. **R-AUT-08 parcial**: la cláusula «`CompanyModules` lo tiene activo para la empresa»
   **no** se aplica en este change (Non-Goal explícito: no existe `companies`). La
   visibilidad se reduce a la parte de permisos (comodín OR permiso de Functionality).
   Se reintroduce el AND con CompanyModules en el change que cree `companies`/
   `CompanyModules`. Sin esta excepción, el menú sería imposible de construir.

2. **R-AUT-05 / EnsureModuleIsActive**: las rutas Admin pueden registrarse; el gate de
   plan SaaS sigue siendo stub/no-op hasta CompanyModules. No se inventa un middleware
   ad-hoc (R-AUT-04).

## Risks / Trade-offs

- **[Riesgo]** Teams + Super Admin con `company_id` NULL → comportamiento version-dependent.
  **Mitigación**: test Feature explícito + lectura de docs Spatie de la versión fijada.
- **[Riesgo]** Primera entidad real ejercita `module:audit` y arch FormRequests.
  **Mitigación**: tarea de observación; si el backstop no atrapa huecos, fix en este
  change (es su primera prueba real).
- **[Riesgo]** Menú sin CompanyModules puede mostrar módulos "de plan" prematuramente.
  **Mitigación**: excepción documentada; seed solo Module `admin` en esta iteración.
- **[Trade-off]** `role_has_permissions` vacío: solo Super Admin usable — aceptable para
  bootstrap; roles de empresa llegan después.

## Migration Plan

Orden apply: Spatie+translatable → `--global` + tests generador → generate Module →
generate Functionality → Actions provisión permisos → seeds → menú builder →
AppLayout/ServerTable/forms → login → feature tests (CRUD, auth, menú, Super Admin) →
module:audit verde. Rollback = revertir change (catálogo seedable).

## Open Questions

Ninguna bloqueante tras el brief. Pendiente revisión humana (Luis) del DoR antes de
apply — en particular OK a la excepción R-AUT-08 parcial.
