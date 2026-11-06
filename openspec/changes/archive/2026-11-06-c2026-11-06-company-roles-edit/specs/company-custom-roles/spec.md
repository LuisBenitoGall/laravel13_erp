## ADDED Requirements

### Requirement: Edit rol empresa estilo plataforma (async + layout)
El sistema SHALL presentar el Edit de rol local no universal a ancho completo
con toggles async (permiso o Functionality, incl. custom), Guardar solo para
descripción, nombre readonly, matriz = autorizados ∩ activos ∩ ¬AdminOnly,
authz `company-roles.update` + perímetro. SHALL gestionar `{module}.access`
(grant con ≥1 permiso; revoke a 0). Ante fallo de toggle: revert + toast.
`manager` local: matriz readonly; descripción editable.

#### Scenario: Layout y Guardar descripción
- **WHEN** un usuario con `company-roles.update` abre Edit de un rol local
  no universal editable
- **THEN** ve layout ancho, nombre readonly, puede guardar solo la
  descripción, y la matriz no depende de ese submit

#### Scenario: Toggle async con custom y comodín
- **WHEN** marca o desmarca un permiso (canónico o custom) o «Todas las
  acciones» de una Functionality visible
- **THEN** persiste sin recarga; el primer permiso bajo un Module concede
  `{module}.access`; al quitar el último se revoca el comodín; fallo ⇒
  revert + toast

#### Scenario: Descripción de manager local
- **WHEN** edita la descripción del rol local `manager` y guarda
- **THEN** la descripción se actualiza y la matriz permanece no editable

#### Scenario: Matriz manager no mutable por toggle
- **WHEN** un cliente intenta conmutar permisos del rol local `manager`
- **THEN** la operación se rechaza y los permisos no cambian

### Requirement: Copiar rol empresa a companies del perímetro
El sistema SHALL ofrecer CTA Copiar en el Edit de un rol local no universal
cuando el usuario tenga `company-roles.update` y más de una company Active
vinculada, hacia otras companies Active de su perímetro (excluyendo la de
sesión). El rol destino SHALL crearse con `company_id` del destino y
`universal = false`, con permisos recortados al catálogo del destino. Si el
`name` ya existe en destino, SHALL versionar el nombre (`(2)`, `(3)`, …).

#### Scenario: Copy con recorte y colisión
- **WHEN** copia un rol local no universal a otra company del perímetro
- **THEN** existe el rol local en destino con `universal = false`, permisos
  ⊆ catálogo destino, y nombre versionado si había homónimo

#### Scenario: Sin CTA Copiar con una sola company
- **WHEN** el usuario solo tiene una company Active vinculada
- **THEN** no se ofrece CTA Copiar usable

#### Scenario: Copy no cruza perímetro
- **WHEN** se solicita copiar a una company fuera del perímetro Active del
  usuario o a la company de sesión
- **THEN** la operación se rechaza

### Requirement: Universales no editables por el tenant
El sistema SHALL NOT ofrecer control Editar (Index u otra UI tenant) sobre
un rol universal. Policy y rutas de Edit/update/toggles/copy SHALL rechazar
el acceso a roles universales en el contexto company (bloqueo por URL
incluido). SHALL NOT exponer copy/UI que mencione el concepto «universal»
al tenant.

#### Scenario: Sin CTA Editar ni URL
- **WHEN** el Index lista un rol con `universal = true`, o el tenant intenta
  abrir Edit / mutar por URL un rol universal
- **THEN** no hay control Editar usable y la mutación/acceso se rechaza
  (403/404)

## MODIFIED Requirements

### Requirement: CRUD de roles custom por empresa (manager)
El sistema SHALL permitir listar, crear y editar roles Spatie con
`company_id` de la company activa, y borrar roles custom de esa company, a
usuarios autorizados según Policy/permisos Spatie (`company-roles.*`) y
perímetro. Super Admin sigue el bypass de permisos del kernel sin ampliar
el perímetro de companies. Create: metadatos + `company-roles.create` (sin
matriz). Edit: patrón async + layout plataforma + `company-roles.update`;
universales fuera del Edit tenant. SHALL impedir borrar los roles locales
`manager` y `guest`. SHALL impedir alterar los permisos del rol local
`manager` vía este CRUD. SHALL permitir editar permisos de `guest` y de
roles custom no universales. SHALL permitir editar la descripción del
`manager` local. SHALL rechazar el borrado de un rol custom que tenga
usuarios asignados.

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

### Requirement: Matriz acotada a módulos autorizados y activos
El sistema SHALL mostrar en la matriz del tenant solo permisos de
Functionalities cuyo Module esté autorizado y activo para la company y no
sea AdminOnly, incluyendo acciones canónicas existentes y permisos custom
del slug de cada Functionality, con el mismo UI de toggles. SHALL NOT
persistir permisos fuera de ese conjunto aunque el cliente los envíe.
`{module}.access` SHALL permanecer fuera de las cards Functionality
(gobernado por la lógica de toggles).

#### Scenario: Módulo no activo no aparece
- **WHEN** un módulo está autorizado pero apagado, o no autorizado, para la
  company
- **THEN** sus funcionalidades no aparecen en la matriz de roles del tenant

#### Scenario: AdminOnly ausente
- **WHEN** un usuario autorizado edita un rol de su company
- **THEN** no se muestran módulos AdminOnly

#### Scenario: Custom visible en card tenant
- **WHEN** existe un permiso `{functionality}.{sufijo}` custom y el Module
  está en el catálogo filtrado de la company
- **THEN** ese permiso aparece en la card de esa Functionality en el Edit
  tenant

#### Scenario: Recorte de acción
- **WHEN** el usuario desmarca una acción de una Functionality visible
  (toggle) en un rol custom
- **THEN** ese rol local deja de tener ese permiso

#### Scenario: Payload con permiso fuera de catálogo
- **WHEN** el cliente envía un permission name que no está en el catálogo
  filtrado de la company
- **THEN** ese permiso no se asigna al rol (rechazo o filtrado estricto)
