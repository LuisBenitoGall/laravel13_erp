## 1. Catálogo / menú (tenant)

- [x] 1.1 Seed Module Basic `workspace` (Active) + Functionality `company-roles` vía
      Actions existentes; permisos auto; auto-autorización Basic en companies activas +
      sync manager
- [x] 1.2 Menú data-driven: entrada «Roles de empresa» / i18n bajo workspace (V-07, V-08,
      V-09)

## 2. Backend Admin (CRUD company roles)

- [x] 2.1 `CompanyRolePermissionCatalog::forCompany(Company)` — módulos autorizados ∩
      activos ∩ ¬AdminOnly; 5 acciones
- [x] 2.2 Actions: CreateCompanyRole, UpdateCompanyRole, DeleteCompanyRole,
      SyncCompanyRolePermissions (protecciones manager/guest; reject delete con users;
      filtrar permisos al set del catálogo)
- [x] 2.3 Policy `CompanyRolePolicy` (manager de CurrentCompany o Super Admin) +
      FormRequests (unique name por company_id/team; permissions array)
- [x] 2.4 Controller Inertia + rutas (`index`/`table`/`create`/`store`/`edit`/`update`/
      `destroy`) scoped a CurrentCompany; ServerTable; middleware módulo `workspace`

## 3. Frontend

- [x] 3.1 Páginas listado ServerTable + create/edit (description, matriz)
- [x] 3.2 Matriz: ocultar AdminOnly / no autorizados / no activos; manager local solo
      lectura; guest/custom editables
- [x] 3.3 i18n es/en/ca (snake_case; cero literales)

## 4. Contrato acceso tenant (módulos futuros)

- [x] 4.1 Playbook: añadir **V-21** + checkbox en §7 (Feature acceso tenant cuando V-21 =
      sí)
- [x] 4.2 Plantilla Pest en `templates-backend.md` (y/o example bajo tests) — módulo off /
      sin permiso / con acceso
- [x] 4.3 Nota en ficha de roles 25/30 o `docs/agents` mínima: gate DoD V-21 (sin arch
      global dinámico en este change)

## 5. Vinculaciones y registro

- [x] 5.1 Actualizar `docs/modules/Admin.md` (custom tenant entra; asignación User↔rol
      sigue fuera)
- [x] 5.2 Glosario: Rol custom (empresa)
- [x] 5.3 Resto V-01..V-20: V-07/08/09 sí; V-21 es el contrato; resto N/A

## 6. Tests

- [x] 6.1 Feature CRUD company roles (create/update/delete custom; validación)
- [x] 6.2 Feature authz: no manager / sin permiso → 403; guest no administra roles
- [x] 6.3 Feature protecciones: no delete manager/guest; no cambiar permissions de
      manager local; no delete custom con users
- [x] 6.4 Feature aislamiento R-TEN-05: company A no muta roles de company B
- [x] 6.5 Feature matriz: módulo apagado/no autorizado/AdminOnly no asignable
- [x] 6.6 E2E Pest browser: **N/A** (declarado en design)

## 7. Calidad

- [x] 7.1 `vendor/bin/pint` + `vendor/bin/phpstan analyse` + tests Feature del change en
      verde
