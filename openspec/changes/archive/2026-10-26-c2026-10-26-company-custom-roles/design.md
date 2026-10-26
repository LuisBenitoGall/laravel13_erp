## Context

Brief: `docs/prompts/c2026-10-26-company-custom-roles.md`. Archivado
`platform-roles`: plantillas universales, CRUD Super Admin, `PlatformRolePermissionCatalog`.
Falta el entorno tenant: manager crea roles locales y recorta permisos dentro del techo
modular. El módulo `admin` es AdminOnly → no sirve como menú tenant.

## Goals / Non-Goals

**Goals:**
- CRUD roles `company_id = CurrentCompany` para manager.
- Matriz = módulos autorizados ∩ activos ∩ ¬AdminOnly.
- Protecciones manager/guest; sync manager intacto.
- Contrato acceso tenant para módulos futuros (playbook + plantilla).

**Non-Goals:**
- Asignación User↔rol UI.
- Cambiar platform-roles, tres capas, o sync manager.
- Rellenar tests de acceso de todos los módulos existentes.
- Consola Platform / Form kit / Fase 1.

## Decisions

1. **Reutilizar modelo Spatie `roles`**  
   Roles tenant = filas con `company_id` = empresa activa, `universal = false`.  
   No nueva tabla. Alternativa descartada: tabla `company_role_definitions`.

2. **Paralelo a platform-roles, no el mismo controller**  
   `CompanyRoleController` + Actions `Create/Update/Delete/SyncCompanyRolePermissions`
   + Policy `CompanyRolePolicy` + catálogo `CompanyRolePermissionCatalog` (filtra por
   company). Reusar UI patterns de `Admin/Roles/*` en `Admin/CompanyRoles/*` o
   `Partners/...` — **ruta Inertia bajo prefijo company/settings**, dominio Admin
   (RBAC es Admin). Alternativa descartada: un solo controller con flag
   platform|tenant (mezcla perímetros).

3. **Menú / Functionality**  
   Nuevo Module **Basic** `workspace` (label i18n «Espacio de trabajo» / settings de
   empresa) + Functionality `company-roles`. Auto-autorización Basic al crear company
   → sync manager obtiene `company-roles.*`. Menú visible con las 3 capas + viewAny.  
   Alternativa descartada: colgar de `admin` (AdminOnly → tenants no lo ven).

4. **Quién opera**  
   Policy: Super Admin (bypass) **o** usuario con rol Spatie `manager` de la
   `CurrentCompany` (y permiso `company-roles.*` cuando no es SA). Guest/custom sin
   permiso → 403. Alternativa descartada: cualquier usuario con `company-roles.update`
   sin ser manager para create/delete de roles — create/update/delete de **definición
   de roles** queda en manager (+ SA); 10 asume permisos Spatie estándar en la
   functionality; el manager los tendrá vía sync.

5. **Protecciones**  
   - No delete si `name` ∈ {manager, guest} (local).  
   - No syncPermissions sobre rol local `manager` (update rechaza cambios de
     permissions; description opcional sí o no — **name inmutable** en manager/guest;
     description editable en guest/custom; manager description editable OK, permissions
     no).  
   - Delete custom: rechazar si hay `model_has_roles`.  
   Alternativa descartada: permitir editar permisos del manager local.

6. **Matriz**  
   `CompanyRolePermissionCatalog::forCompany(Company)`: Modules Active ∧ authorized
   ∧ enabled ∧ level ≠ AdminOnly; Functionalities Active; 5 acciones.  
   Al guardar: intersectar payload con ese set (ignorar/400 si vienen permisos
   fuera).  
   UI manager: matriz read-only + copy i18n.

7. **Contrato acceso tenant (módulos futuros)** — decisión brief  
   - **Playbook**: nuevo ítem **V-21** «¿Expone rutas HTTP de módulo activable?» →
     Feature de acceso tenant (off → bloqueo; sin permiso → 403; con capas+permiso →
     OK). Ampliar §7 con checkbox obligatorio cuando V-21 = sí.  
   - **Plantilla Pest** en `docs/standards/templates-backend.md` (y/o fichero example
     bajo `tests/Feature/Support/`).  
   - **Gate**: 25-architecture-review / 30-testing comprueban V-21; **no** arch-test
     dinámico global en este change (evita falsos positivos con módulos sin rutas).  
   Alternativa descartada: arch que liste todos los Module y exija fichero test — frágil
   hasta que haya convención de paths estable.  
   Nota: actualizar playbook/templates **es entregable** de este change (no “excepción
   a standards” improvisada; el brief lo exige). No se altera kernel ni R-AUT-08.

8. **E2E**  
   N/A (`e2e.md` §4). Feature tests cubren CRUD + authz + aislamiento (rol de company A
   no editable desde company B).

9. **Glosario / ficha**  
   Término «Rol custom (empresa)»; actualizar `docs/modules/Admin.md` (entra custom
   tenant; sale del “No entra”). module-map: sin dependencia nueva.

## Risks / Trade-offs

- **[Riesgo]** Manager edita guest quitando/poniendo permisos de módulos luego
  desautorizados → filas huérfanas en pivot. **Mitigación**: al guardar, sync solo
  permite set actual; opcional limpieza en `SyncManagerPermissions` no aplica a guest —
  task: al desautorizar módulo, ¿strip de todos los roles locales? **Fuera de alcance
  mínimo**; documentar follow-up. En este change: validación en save + matriz filtrada.
- **[Riesgo]** Confusión UI platform Roles vs company Roles. **Mitigación**: rutas/menú
  distintos; i18n «Roles de empresa».
- **[Trade-off]** Module Basic `workspace` nuevo vs meter en Partners. Admin posee RBAC;
  workspace es cáscara mínima Basic.

## Migration Plan

1. Seed Module `workspace` + Functionality `company-roles` + auto Basic.  
2. Actions/Policy/HTTP/UI.  
3. Playbook V-21 + plantilla Pest + ficha/glosario.  
4. Feature tests.  
5. Rollback: soft — desactivar functionality; no drop de roles locales custom.

## Open Questions

Ninguna bloqueante. Revisión humana (Luis) del DoR antes de apply.

## Excepciones a standards

Ninguna respecto a kernel/architecture. Se **amplía** playbook/templates como parte del
alcance (V-21 + §7), no se contradice R-*.

## E2E

No aplica.
