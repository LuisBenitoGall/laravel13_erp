## ADDED Requirements

### Requirement: Index roles empresa sin exigir manager
El sistema SHALL permitir el Index/table de roles de la company de sesión a
cualquier usuario vinculado con permiso Spatie `company-roles.view` (método
Policy de listado según semántica change 1). SHALL NOT exigir el rol local
`manager` para ese Index.

#### Scenario: Index con view sin ser manager
- **WHEN** un usuario de la company tiene `company-roles.view` y no es
  `manager`, y abre Index o `table` de roles empresa
- **THEN** el acceso se concede (200) dentro del perímetro

#### Scenario: Index sin view
- **WHEN** un usuario sin `company-roles.view` (y sin bypass SA) fuerza Index
  o `table`
- **THEN** recibe 403

### Requirement: Sin copy de roles tenant
El sistema SHALL NOT ofrecer ni ejecutar copia de roles en el contexto
company/tenant (UI, rutas, Actions).

#### Scenario: Sin superficie copy tenant
- **WHEN** se inspeccionan rutas, Edit UI y Actions de company roles
- **THEN** no existe flujo usable de copiar un rol de empresa a otras
  companies

## REMOVED Requirements

### Requirement: Copiar rol empresa a companies del perímetro
**Reason**: Luis — quitar copy tenant para evitar conflictos (manager/guest/
custom y superficie de producto). El copy de plataforma permanece con recorte
de catálogo (delta `platform-roles`).

**Migration**: eliminar ruta `company.roles.copy`, Action/Request/UI/tests de
copy tenant. Clientes que llamaran la ruta dejan de tener endpoint.

## MODIFIED Requirements

### Requirement: CRUD de roles custom por empresa (manager)
El sistema SHALL permitir listar, crear y editar roles Spatie con
`company_id` de la company activa, y borrar roles custom de esa company, a
usuarios autorizados según Policy/permisos Spatie (`company-roles.*`) y
perímetro. El Index/listado SHALL autorizarse con `company-roles.view` sin
exigir rol `manager`. Super Admin sigue el bypass de permisos del kernel sin
ampliar el perímetro de companies. Create: metadatos + `company-roles.create`
(sin matriz). Edit: patrón async + layout plataforma + `company-roles.update`;
universales fuera del Edit tenant. SHALL NOT incluir copy entre companies en
el CRUD tenant. SHALL impedir borrar los roles locales `manager` y `guest`.
SHALL impedir alterar los permisos del rol local `manager` vía este CRUD.
SHALL permitir editar permisos de `guest` y de roles custom no universales.
SHALL permitir editar la descripción del `manager` local. SHALL rechazar el
borrado de un rol custom que tenga usuarios asignados.

#### Scenario: Crear rol custom en la company
- **WHEN** un usuario con `company-roles.create` crea un rol con nombre (y
  descripción opcional) en su company activa, sin asignar permisos en el alta
- **THEN** el rol queda con `company_id` de esa company y `universal = false`

#### Scenario: No borrar manager ni guest locales
- **WHEN** un manager intenta eliminar el rol local `manager` o `guest`
- **THEN** la operación se rechaza

#### Scenario: Matriz del manager local no editable
- **WHEN** un usuario autorizado intenta guardar o conmutar permisos
  distintos en el rol local `manager`
- **THEN** la operación se rechaza o los permisos no cambian

#### Scenario: Editar guest o custom
- **WHEN** un usuario con `company-roles.update` conmuta permisos en `guest`
  o en un rol custom local (no universal)
- **THEN** `role_has_permissions` de ese rol local refleja la selección
  permitida sin depender del Guardar de descripción

#### Scenario: No borrar custom con usuarios
- **WHEN** un manager intenta eliminar un rol custom con usuarios en
  `model_has_roles`
- **THEN** la operación se rechaza

#### Scenario: Aislamiento entre companies
- **WHEN** un usuario de la company A intenta editar o borrar un rol con
  `company_id` de la company B
- **THEN** la operación se rechaza (404/403)

### Requirement: Universales no editables por el tenant
El sistema SHALL NOT ofrecer control Editar (Index u otra UI tenant) sobre
un rol universal. Policy y rutas de Edit/update/toggles SHALL rechazar el
acceso a roles universales en el contexto company (bloqueo por URL incluido).
SHALL NOT exponer UI/copy/i18n que mencione el concepto «universal» al tenant
en este flujo.

#### Scenario: Sin CTA Editar ni URL
- **WHEN** el Index lista un rol con `universal = true`, o el tenant intenta
  abrir Edit / mutar por URL un rol universal
- **THEN** no hay control Editar usable y la mutación/acceso se rechaza
  (403/404)
