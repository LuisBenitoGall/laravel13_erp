## Why

Tras tenancy, las companies existen y los módulos se autorizan/encienden, pero Spatie
Teams aún no materializa roles por empresa: no hay `manager`/`guest` al alta, ni
sincronización de permisos del manager con la Capa 3. Sin eso el RBAC multi-empresa no
es operable más allá del Super Admin.

## What Changes

- Al crear una Company: roles Spatie `manager` y `guest` con `company_id` de esa
  empresa; vincular al creador en `user_companies` y asignarle `manager`.
- `guest` nace sin permisos.
- Sincronizar permisos del `manager` con Functionalities de módulos Basic/Optional
  **autorizados** (nunca AdminOnly): al autorizar/desautorizar (y tras auto-Basic);
  encender/apagar no toca `role_has_permissions`.
- Backfill/seed de companies existentes de trabajo.
- UI mínima para asignar rol `manager`|`guest` a un usuario ya vinculado a la company
  (sin crear roles custom).
- Entradas de glosario para los roles.

## Capabilities

### New Capabilities

- `company-roles`: creación obligatoria manager/guest por company; asignación al creador;
  asignación mínima manager/guest a users vinculados.
- `manager-permission-sync`: sync idempotente del rol manager según autorizaciones
  Basic/Optional.

### Modified Capabilities

(ninguna a nivel de contrato de menú/tres capas — solo se consumen)

## Impact

- `CreateCompany` (y controller): recibe usuario creador; crea roles + vínculo + assign.
- `AuthorizeModuleForCompany` / `RevokeModuleAuthorizationForCompany` /
  `AuthorizeBasicModulesForCompany`: enganchan sync del manager.
- Nueva Action `SyncManagerPermissionsForCompany` (Admin o Support de RBAC en Admin).
- Seeders/demo y tests Feature; posible E2E ligero de login como manager.
- `docs/modules/Admin.md` / `Partners.md`; `glossary.md` (manager/guest).
