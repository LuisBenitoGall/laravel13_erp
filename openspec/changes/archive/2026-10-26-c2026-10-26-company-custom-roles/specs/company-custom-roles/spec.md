## ADDED Requirements

### Requirement: CRUD de roles custom por empresa (manager)
El sistema SHALL permitir al manager de la company activa (o Super Admin) listar, crear
y editar roles Spatie con `company_id` de esa company, y borrar roles custom de esa
company. SHALL impedir borrar los roles locales `manager` y `guest`. SHALL impedir
alterar los permisos del rol local `manager` vía este CRUD. SHALL permitir editar
permisos de `guest` y de roles custom. SHALL rechazar el borrado de un rol custom que
tenga usuarios asignados.

#### Scenario: Crear rol custom en la company
- **WHEN** un manager crea un rol con nombre (y descripción opcional) en su company
  activa
- **THEN** el rol queda con `company_id` de esa company y `universal = false`

#### Scenario: No borrar manager ni guest locales
- **WHEN** un manager intenta eliminar el rol local `manager` o `guest`
- **THEN** la operación se rechaza

#### Scenario: Matriz del manager local no editable
- **WHEN** un manager intenta guardar permisos distintos en el rol local `manager`
- **THEN** la operación se rechaza o los permisos no cambian

#### Scenario: Editar guest o custom
- **WHEN** un manager guarda permisos en `guest` o en un rol custom
- **THEN** `role_has_permissions` de ese rol local refleja la selección permitida

#### Scenario: No borrar custom con usuarios
- **WHEN** un manager intenta eliminar un rol custom con usuarios en `model_has_roles`
- **THEN** la operación se rechaza

#### Scenario: Aislamiento entre companies
- **WHEN** un manager de la company A intenta editar o borrar un rol con
  `company_id` de la company B
- **THEN** la operación se rechaza (404/403)

---

### Requirement: Matriz acotada a módulos autorizados y activos
El sistema SHALL mostrar en la matriz del tenant solo permisos de Functionalities cuyo
Module esté autorizado y activo para la company y no sea AdminOnly. SHALL NOT persistir
permisos fuera de ese conjunto aunque el cliente los envíe.

#### Scenario: Módulo no activo no aparece
- **WHEN** un módulo está autorizado pero apagado, o no autorizado, para la company
- **THEN** sus funcionalidades no aparecen en la matriz de roles del tenant

#### Scenario: AdminOnly ausente
- **WHEN** un manager edita un rol de su company
- **THEN** no se muestran módulos AdminOnly

#### Scenario: Recorte de acción
- **WHEN** el manager desmarca una acción de una Functionality visible y guarda un rol
  custom
- **THEN** ese rol local deja de tener ese permiso

#### Scenario: Payload con permiso fuera de catálogo
- **WHEN** el cliente envía un permission name que no está en el catálogo filtrado de
  la company
- **THEN** ese permiso no se asigna al rol (rechazo o filtrado estricto)
