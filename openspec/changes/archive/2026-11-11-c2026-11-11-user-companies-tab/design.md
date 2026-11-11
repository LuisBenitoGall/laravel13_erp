# Design

## Context

See proposal.md — Why. Tab Empresas placeholder. Pivot
`user_companies` = `id`, `user_id`, `company_id`, timestamps (unique pair).
`DetachCompanyUser` ya desvincula un member de **una** company (index
Usuarios empresa). Kernel: User sin company no entra al ERP; soft delete
`user_companies` no. Authz ficha = self o `updateCompanyUser`.

## Goals / Non-Goals

**Goals:** migration position/department; ServerTable tab; detach; sesión
tras detach; documentar ≠ Empleo.

**Non-Goals:** alta/edición de vínculos o de position/department en UI;
entidad Empleo; borrar User/Company; otros tabs de la ficha.

**E2E:** no aplica (`e2e.md` §4).

**Excepciones a standards:** ninguna.

## Decisions

1. **Capabilities**  
   `user-companies-membership` durable; `user-edit` delta del tab.

2. **Vínculo ≠ Empleo**  
   Este change solo membership (`user_companies`). Empleo = entidad futura
   (puede coexistir o no con el vínculo). Position/department en el pivot
   son metadatos del **vínculo de acceso**, no la ficha laboral. Glosario:
   anotar membership vs Empleo (Empleo TBD).

3. **Schema**  
   `position` string nullable, `department` string nullable (varchar 255
   salvo design más corto 100 — **255**). Sin softDeletes en pivot
   (kernel: `user_companies` no soft-delete).  
   Relación: `belongsToMany` → `withPivot(['id','position','department'])`
   + `withTimestamps()`; o modelo `UserCompany` para TableBuilder
   (preferible para `*/table` y destroy por pivot id).  
   **Decisión:** modelo Eloquent `App\Models\UserCompany` (tabla
   `user_companies`) para listados/Policy/destroy; mantener belongsToMany
   en User/Company sincronizado.

4. **ServerTable**  
   Como Module functionalities: panel en tab; `endpoint`  
   - profile: `/profile/companies/table`  
   - company: `/admin/company-users/{user}/companies/table`  
   Query: pivots del **user editado** (todas las companies), join Company
   name; columns: company name, position, department, created_at
   (formato locale front o API). Row id = `user_companies.id`.  
   Acción fila: delete (confirm) → destroy. Sin create CTA en tab.

5. **Detach + sesión**  
   Action `DetachUserCompany` (o extender `DetachCompanyUser`):
   - Authz Policy sobre owner User (self / updateCompanyUser).
   - `detach` / delete pivot row.
   - Si `actor.id === owner.id` y `CurrentCompany` era la company
     detachada:
     - Si owner tiene otra company activa vinculada → set
       `current_company_id` a una de ellas (p. ej. primera por nombre/id;
       documentar criterio estable: **menor `companies.id` restante**).
     - Si no queda ninguna → `Auth::logout()` + invalidate session →
       redirect login.
   - Si actor ≠ owner (manager detach ajeno): no tocar sesión del actor
     salvo que… no aplica; la sesión es del actor. Si manager detacha al
     member de la company de **sesión del manager**, el member pierde
     vínculo pero la sesión del manager sigue (el index ya hace detach
     análogo).  
   - Detach última del **owner** (aunque actor sea manager): permitido;
     owner queda sin companies (no entra ERP hasta nuevo vínculo).

6. **Authz**  
   `viewAny`/table + `delete` membership: self o `updateCompanyUser` sobre
   el user de la ficha. Sin permiso Spatie nuevo.

7. **UI position/department**  
   Solo lectura en tabla (pueden ser null → “—” / vacío). Sin form edit
   en este change. Relleno futuro: Create company-users u otro flujo /
   Empleo.

8. **V-01..V-20**  
   Migración, modelo, Policy, Action, table endpoint, i18n, tests
   (perímetro, detach, última, switch sesión, logout). No: menú Module
   nuevo, BelongsToCompany en UserCompany (es la tabla de vínculo), E2E.

## Risks / Trade-offs

- [position en pivot vs Empleo] → riesgo de confusión. Mitigación: glosario
  + NonGoals Empleo; UI tab “Empresas” no “Empleos”.  
- [logout tras detach self] → UX brusca pero acordada.  
- [criterio “otra company”] → menor id; documentado.  
- [Duplicate detach paths] → index `DetachCompanyUser` vs ficha; compartir
  Action core + hook de sesión solo cuando actor===member.

## Migration Plan

1. Migration columns + modelo UserCompany / withPivot.  
2. table + destroy + sesión/logout.  
3. Tab UI ServerTable.  
4. Docs + tests.  
Rollback: drop columns (nullable; sin datos críticos).

## Open Questions

Ninguna.
