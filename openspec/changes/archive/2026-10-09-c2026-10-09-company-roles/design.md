## Context

Tenancy archivado: `CreateCompany` crea la company y, si Active, llama
`AuthorizeBasicModulesForCompany` → `AuthorizeModuleForCompany`. No vincula al creador ni
crea roles. Spatie Teams ya activo (`company_id`); Super Admin con `company_id` NULL y
`Gate::before`. Brief: `docs/prompts/c2026-10-09-company-roles.md`.

## Goals / Non-Goals

**Goals:** manager/guest por company; creador = manager; sync permisos manager ↔
autorizaciones Basic/Optional; UI mínima asignar manager|guest; seed/backfill.

**Non-Goals:** crear roles custom; registro/onboarding/Platform; cambiar tres capas ni
Super Admin bypass; CustomerProvider.

## Decisions

1. **Nombres Spatie** `manager` y `guest` (mismo `name` en todas las companies; distinción
   por `team_id`/`company_id`). Alternativa descartada: prefijo `{id}/manager` de v1 —
   innecesario con Teams.

2. **`EnsureCompanyRoles` + extensión de `CreateCompany`**  
   - `CreateCompany::handle(array $data, User $creator)` (o equivalente tipado).  
   - Tras create: `user_companies`; crear roles con `setPermissionsTeamId($company->id)`;
     `assignRole('manager')` al creator; luego `AuthorizeBasicModulesForCompany` (ya
     existente) + sync manager.  
   Alternativa descartada: observer suelto sin Action — rompe el patrón R-CAP-02.

3. **`SyncManagerPermissionsForCompany` (resync completo idempotente)**  
   Calcula el set de permisos = union de permisos de Functionalities de Modules
   autorizados ∩ Active ∩ level ∈ {Basic, Optional}.  
   `role->syncPermissions($names)`.  
   Se invoca al final de `AuthorizeModuleForCompany`, `RevokeModuleAuthorizationForCompany`
   y tras el lote Basic (o desde AuthorizeModule una vez por módulo; tras Basic, un
   resync final basta).  
   Alternativa descartada: add/remove incremental — más frágil si cambian Functionalities.

4. **AdminOnly siempre excluido del sync** aunque hubiera fila de autorización (defensa
   en profundidad; seed no debe autorizar admin al manager).

5. **UI mínima de asignación** (delimitación del brief): Action
   `AssignCompanyRole(User, Company, 'manager'|'guest')` + pantalla/acción Inertia
   asociada a users vinculados de la company (o desde show Company). No CRUD de roles.
   Quién puede: Super Admin + usuarios con rol `manager` de esa company (permiso a
   definir o policy sobre asignación). Alternativa descartada: solo seeder — dejaría el
   guest inutilizable en demo.

6. **Backfill**: seeder/comando o paso en DatabaseSeeder que, para cada company existente,
   asegure manager/guest; asigne manager a un user vinculado (superadmin de trabajo si
   aplica); ejecute sync.

7. **E2E**: no obligatorio un flujo largo; Feature tests cubren alta company + sync.
   E2E opcional: login como manager ve menú de módulos Basic autorizados/encendidos —
   task opcional rol 40 si el entorno lo permite; si no, declarar N/A en DoD de apply.

8. **Glosario**: añadir Manager / Guest (rol de empresa).

## Excepciones a standards

Ninguna.

## Risks / Trade-offs

- **[Riesgo]** `hasRole('manager')` con Teams requiere `setPermissionsTeamId` = company
  de sesión. **Mitigación**: middleware/`CurrentCompany` ya fija contexto; tests con team
  id; documentar en Assign/Sync.
- **[Riesgo]** Resync borra permisos “manuales” añadidos al manager fuera del sync.
  **Mitigación**: aceptado — el manager se define como proyección de autorizaciones;
  permisos sueltos no son el modelo.
- **[Trade-off]** UI mínima de asignación añade superficie; sin ella el Goal del guest
  queda teórico.

## Migration Plan

Orden: Actions roles + sync → enganchar CreateCompany/Authorize/Revoke → UI asignación →
backfill seed → tests. Sin migración de esquema salvo que falte índice único
(role name + company_id) — verificar migración Spatie existente.

## Open Questions

Ninguna bloqueante. Revisión humana (Luis) del DoR antes de apply.
