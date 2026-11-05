# Spec Delta: platform-roles

## ADDED Requirements

### Requirement: Matriz de roles incluye permisos custom de Functionality
El sistema SHALL listar en la matriz Edit de roles de plataforma, por cada
Functionality, los permisos Spatie canónicos y los custom cuyo prefijo sea el
slug de esa Functionality. SHALL permitir asignarlos/revocarlos por la misma
UI de checkboxes. SHALL NOT ocultar un permiso custom existente del catálogo
por el solo hecho de no ser una de las cinco acciones automáticas. SHALL NOT
incluir el comodín `{module}.access` (ni `{slug}.access`) en la card
Functionality.

#### Scenario: Custom visible en la card
- **WHEN** existe el permiso `companies.limpiar-algunas-empresas` y Super Admin
  abre Edit de un rol de plataforma con la Functionality `companies` en matriz
- **THEN** ese permiso aparece en la card «Mis empresas» junto a los canónicos

#### Scenario: Toggle custom igual que canónico
- **WHEN** Super Admin marca o desmarca un permiso custom en la matriz
- **THEN** el cambio se persiste con el mismo mecanismo que un canónico (sin
  recarga)

#### Scenario: Todas las acciones incluye custom
- **WHEN** Super Admin usa «Todas las acciones» en una Functionality que tiene
  canónicos y custom
- **THEN** se conceden o revocan todos los permisos listados de esa
  Functionality, incluidos los custom

## MODIFIED Requirements

### Requirement: Matriz de permisos en edición de rol de plataforma
El sistema SHALL mostrar en la edición de un rol de plataforma los permisos
Spatie ligados a cada Functionality del catálogo (agrupados por Module): las
cinco acciones canónicas `viewAny`/`view`/`create`/`update`/`delete` cuando
existan y cualquier permiso custom con prefijo `{functionalitySlug}.`,
excluyendo el comodín `access` de Module. SHALL permitir concederlos o
revocarlos mediante checkboxes con persistencia inmediata (sin submit de
matriz). En roles universales SHALL omitir módulos AdminOnly. SHALL permitir
editar la matriz de las plantillas universales `manager` y `guest` vía esos
toggles (salvo flags de solo lectura ya existentes). SHALL NOT permitir
eliminar esas plantillas desde este CRUD. SHALL NOT usar la matriz de la
plantilla universal `manager` como techo que recorte funcionalidades de un
módulo ya autorizado y activado para una empresa. Los permisos efectivos del
rol local `manager` de cada company SHALL seguir gobernados por el sync de
autorizaciones; cambiar la plantilla SHALL NOT sustituir ese sync. El botón
Guardar del formulario Edit queda limitado a descripción.

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
