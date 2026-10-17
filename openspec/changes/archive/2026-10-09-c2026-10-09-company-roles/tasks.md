## 1. Roles al crear Company

- [x] 1.1 Action `EnsureCompanyDefaultRoles` (o equivalente): crea `manager` y `guest`
      con `company_id` vía `setPermissionsTeamId`; guest sin permisos
- [x] 1.2 Extender `CreateCompany` para recibir `User $creator`: `user_companies`, roles,
      `assignRole('manager')`, luego Basic authorize + sync manager
- [x] 1.3 Actualizar `CompanyController@store` / FormRequest para pasar el usuario
      autenticado
- [x] 1.4 Feature test: crear company ⇒ roles con company_id, creator vinculado y manager,
      guest sin `role_has_permissions`

## 2. Sync de permisos del manager

- [x] 2.1 Action `SyncManagerPermissionsForCompany`: resync idempotente (Basic+Optional
      autorizados y Active; excluir AdminOnly)
- [x] 2.2 Enganchar sync al final de `AuthorizeModuleForCompany` y
      `RevokeModuleAuthorizationForCompany`
- [x] 2.3 Tras `AuthorizeBasicModulesForCompany` (o un resync al cerrar CreateCompany)
      dejar manager alineado
- [x] 2.4 Tests: autorizar Optional añade permisos; desautorizar los quita; AdminOnly no
      entra; Enable/Disable module no cambia `role_has_permissions`

## 3. Asignación mínima manager|guest (sin crear roles)

- [x] 3.1 Action `AssignCompanyRole` (solo `manager`|`guest`; user debe estar en
      `user_companies`)
- [x] 3.2 Policy/autorización: Super Admin o manager de esa company
- [x] 3.3 UI mínima Inertia (lista users vinculados + asignar rol) + i18n es/en
- [x] 3.4 Feature test de asignación guest/manager

## 4. Seed, glosario y fichas

- [x] 4.1 Backfill/seed: companies existentes con roles; manager en user de trabajo;
      sync según autorizaciones
- [x] 4.2 Entradas glosario Manager / Guest (rol de empresa)
- [x] 4.3 Actualizar `docs/modules/Admin.md` y `Partners.md` (roles por empresa en alcance
      “como quedó” al archivar)

## 5. Calidad y DoD

- [x] 5.1 `composer test`, Pint, PHPStan verdes
- [x] 5.2 E2E login como manager con menú Basic usable
      > `tests/Browser/Auth/LoginAsManagerSeesBasicMenuTest.php` — login manager (no Super
      Admin) → `/dashboard` + AppLayout (tenant/Salir) + ítem de Functionality Basic
      autorizada/encendida. Ajuste mínimo: `PostLoginRedirect` (Super Admin → admin,
      resto → dashboard), página `Dashboard`, `SetCurrentCompany` fija
      `setPermissionsTeamId` e invalida cache Spatie del User.
- [x] 5.3 `openspec validate c2026-10-09-company-roles` limpio
