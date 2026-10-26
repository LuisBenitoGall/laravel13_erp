## Why

El RBAC de plataforma ya permite a Super Admin gestionar roles universales/genéricos, pero
el manager del tenant aún no puede crear roles locales ni recortar permisos dentro de los
módulos contratados. Sin eso, solo existen `manager` (todo lo autorizado) y `guest`
(vacío). Además hace falta un contrato harness/playbook para que cada módulo futuro
demuestre acceso tenant (capas + permiso), no solo aislamiento R-TEN-05.

## What Changes

- CRUD Inertia de **roles de la company activa** para el manager: listar, crear, editar,
  borrar (custom); `manager`/`guest` locales no borrables; matriz del `manager` local
  solo lectura (sync); `guest` y custom editables.
- Matriz acotada a módulos **autorizados y activos** de la company; sin AdminOnly; sin
  permisos fuera de ese catálogo.
- Functionality + menú tenant para `company-roles` (módulo Basic usable por empresas).
- **Contrato** de verificación de acceso tenant: playbook + plantilla Pest (+ gate
  25/30); este change lo publica; módulos futuros lo rellenan. Feature tests del propio
  CRUD company-roles como primer uso del patrón de autorización.
- **No** UI de asignación User↔rol (change posterior).
- **No** tocar sync del manager ni tres capas.

## Capabilities

### New Capabilities

- `company-custom-roles`: CRUD tenant de roles locales; matriz filtrada; protecciones
  manager/guest; menú/permisos `company-roles`.
- `tenant-module-access-contract`: contrato playbook/plantilla para que cada módulo
  futuro verifique acceso tenant (módulo off / sin permiso / con acceso).

### Modified Capabilities

- `company-roles`: aclarar no borrables locales, matriz manager no editable por tenant,
  existencia de roles custom adicionales.

## Impact

- Dominio Admin (Actions/Policy/Controllers/Requests/Resources) paralelo a platform-roles
  pero scoped a `CurrentCompany`.
- Catálogo: Module Basic (settings/workspace) + Functionality `company-roles` + menú.
- Support: catálogo de matriz filtrado por autorización+activación de la company.
- `docs/standards/module-playbook.md` (§6/§7), `templates-backend.md` (plantilla Pest);
  ficha `docs/modules/Admin.md`; glosario si hace falta.
- Tests Feature CRUD/authz/aislamiento del CRUD de roles; sin E2E (§4 N/A).
