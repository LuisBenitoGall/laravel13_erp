# Spec Delta: platform-roles

## ADDED Requirements

### Requirement: Layout Edit roles plataforma estilo v1
El sistema SHALL presentar el Edit a ancho completo con la organización de
permisos equivalente a v1 (anclas/bloques Module y tarjetas Functionality).

#### Scenario: Vista ancho completo organizada como v1
- **WHEN** Super Admin abre Edit de un rol de plataforma gestionable
- **THEN** la vista sigue el layout v1 a ancho completo (no el contenedor
  estrecho actual)

### Requirement: Persistencia de permisos por checkbox sin recarga
El sistema SHALL persistir al cambiar un checkbox de permiso individual o el
control global de una Functionality, sin recargar. Un permiso SHALL NOT
conceder el resto de acciones de esa Functionality ni permisos de otras
Functionalities del mismo Module. Ante fallo SHALL revertir el checkbox y
mostrar toast.

#### Scenario: Toggle individual
- **WHEN** se marca o desmarca un permiso concreto
- **THEN** queda persistido sin recarga y no arrastra otras acciones ni otras
  funcs

#### Scenario: Toggle Functionality completa
- **WHEN** se usa el control global de una Functionality
- **THEN** se conceden o revocan los permisos de esa Functionality sin recarga

#### Scenario: Fallo de toggle
- **WHEN** falla la persistencia del checkbox
- **THEN** se revierte el estado visual y se muestra un toast de error

### Requirement: Activación Module al tener permisos
Cuando el rol obtiene al menos un permiso de Functionality de un Module, el
sistema SHALL dejar el Module “activado” para ese rol (comodín
`{module}.access`). Cuando el rol deja de tener cualquier permiso de
Functionalities de ese Module, SHALL revocar `{module}.access`. Un permiso de
Functionality no implica el resto de permisos de esa Functionality ni otras
Functionalities del Module.

#### Scenario: Al menos un permiso activa el Module
- **WHEN** el rol recibe el primer permiso bajo un Module
- **THEN** el rol tiene el comodín `{module}.access` de ese Module

#### Scenario: Sin permisos de Functionalities revoca access
- **WHEN** se revoca el último permiso de Functionalities de un Module en el
  rol
- **THEN** el rol deja de tener `{module}.access` de ese Module

### Requirement: Guardar solo descripción
El botón Guardar del Edit SHALL persistir la descripción editable. SHALL NOT
ser el mecanismo para guardar la matriz de permisos.

#### Scenario: Guardar descripción
- **WHEN** Super Admin cambia la descripción y pulsa Guardar
- **THEN** se actualiza la descripción; los permisos no dependen de ese submit

### Requirement: Copiar rol a companies del perímetro
El sistema SHALL ofrecer CTA Copiar (como v1: no aplicable a Super Admin ni a
universales) solo cuando el usuario en sesión está vinculado a más de una
company Active, hacia companies Active de su perímetro **excluyendo** la
empresa en sesión (`CurrentCompany`). Si el `name` ya existe en destino, SHALL
versionar el nombre del rol nuevo.

#### Scenario: Copy con nombre libre
- **WHEN** se copia un rol no universal a una company del perímetro (distinta
  de la de sesión) sin rol homónimo
- **THEN** existe el rol local con ese nombre y los permisos del origen

#### Scenario: Copy con colisión de nombre
- **WHEN** en destino ya existe un rol con el mismo `name`
- **THEN** el rol nuevo se crea con nombre versionado

#### Scenario: Universales sin CTA Copiar
- **WHEN** Super Admin edita un rol universal
- **THEN** no se ofrece el CTA Copiar (o no es usable)

#### Scenario: Una sola empresa vinculada sin CTA Copiar
- **WHEN** Super Admin edita un rol no universal y solo está vinculado a una
  company Active
- **THEN** no se ofrece el CTA Copiar

#### Scenario: Destinos sin empresa en sesión
- **WHEN** el CTA Copiar está disponible
- **THEN** la lista de destinos no incluye la company de sesión

## MODIFIED Requirements

### Requirement: Matriz de permisos en edición de rol de plataforma
El sistema SHALL mostrar en la edición de un rol de plataforma los permisos de
las Functionalities del catálogo (agrupados por Module; acciones
`viewAny`/`view`/`create`/`update`/`delete`) y permitir concederlos o
revocarlos mediante checkboxes con persistencia inmediata (sin submit de
matriz). En roles universales SHALL omitir módulos AdminOnly. SHALL permitir
editar la matriz de las plantillas universales `manager` y `guest` vía esos
toggles (salvo flags de solo lectura ya existentes). SHALL NOT permitir
eliminar esas plantillas desde este CRUD. SHALL NOT usar la matriz de la
plantilla universal `manager` como techo que recorte funcionalidades de un
módulo ya autorizado y activado para una empresa. Los permisos efectivos del
rol local `manager` de cada company SHALL seguir gobernados por el sync de
autorizaciones; cambiar la plantilla SHALL NOT sustituir ese sync. El botón
Guardar del formulario Edit queda limitado a descripción (ver requisitos
ADDED).

#### Scenario: Universales sin AdminOnly en la matriz
- **WHEN** Super Admin edita un rol universal
- **THEN** no se muestran módulos AdminOnly en la matriz de permisos

#### Scenario: Genérico de plataforma puede incluir AdminOnly
- **WHEN** Super Admin edita un rol genérico de plataforma (`universal = false`)
- **THEN** la matriz puede incluir módulos AdminOnly y persistir esos permisos
  en el rol vía toggles

#### Scenario: Plantillas manager y guest editables
- **WHEN** Super Admin abre la edición de la plantilla universal `manager` o
  `guest` y cambia permisos con los checkboxes
- **THEN** `role_has_permissions` de esa plantilla refleja la selección; la
  plantilla sigue sin poder borrarse

#### Scenario: Sync manager local intacto
- **WHEN** se modifican permisos de la plantilla universal `manager` vía
  toggles
- **THEN** el rol local `manager` de cada company no se recorta automáticamente
  por esa plantilla; sigue el sync de módulos autorizados/activados

#### Scenario: Guardar permisos en rol genérico o universal
- **WHEN** Super Admin marca o desmarca permisos en un rol de plataforma
  editable
- **THEN** `role_has_permissions` del rol refleja la selección persistida por
  el toggle (sin depender del botón Guardar de descripción)

#### Scenario: Nuevas funcionalidades aparecen en la matriz
- **WHEN** se crea una Functionality (y sus permisos) en el catálogo
- **THEN** esos permisos pasan a estar disponibles en el panel de edición de
  roles de plataforma (omitidos en universales si el Module es AdminOnly)
