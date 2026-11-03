# Spec Delta

## ADDED Requirements

### Requirement: Index ServerTable de roles de plataforma
El sistema SHALL exponer bajo Configuración un Index ServerTable de roles globales
(`company_id` NULL) con columnas al menos nombre, descripción, universal e i18n, y
Acciones. El rol `Super Admin` SHALL NOT aparecer en ese listado.

#### Scenario: SA ve listado sin Super Admin
- **WHEN** Super Admin abre Roles en Configuración
- **THEN** ve el ServerTable de roles de plataforma y no ve una fila del rol
  `Super Admin`

#### Scenario: Columna universal
- **WHEN** el Index carga filas
- **THEN** cada fila muestra si el rol es universal (sí/no o equivalente i18n)

### Requirement: Acciones Edit y Delete en Index de roles de plataforma
El Index SHALL ofrecer Acciones al estilo Modules: Edit (navega al Edit/matriz) en
todas las filas gestionables del listado (incluidas las plantillas `manager` y
`guest`), y Delete con confirmación solo cuando el rol sea borrable (`can_delete`).
SHALL NOT mostrar Delete usable en plantillas `manager`/`guest` ni en roles con
usuarios asignados (reglas de protección existentes). Super Admin sigue fuera del
listado.

#### Scenario: Edit en Acciones incluye plantillas
- **WHEN** Super Admin ve Acciones de una fila `manager`, `guest` u otro rol de
  plataforma en el Index
- **THEN** hay acción Edit que lleva al flujo de edición / matriz de permisos

#### Scenario: Delete de manager o guest no usable o rechazado
- **WHEN** la fila es la plantilla universal `manager` o `guest`
- **THEN** Delete no es usable o la operación se rechaza sin borrar el rol

#### Scenario: Delete de rol no protegido sin usuarios
- **WHEN** el usuario confirma Eliminar en Acciones de un rol de plataforma no
  protegido y sin usuarios asignados
- **THEN** el rol se elimina y deja de aparecer en el listado

### Requirement: CTA Crear rol stub en Index
El Index SHALL mostrar un control Crear rol en la ActionBar. Ese control SHALL NOT
navegar al flujo Create mientras Create permanezca fuera del DoD de este incremento
(stub visible). Edit desde Acciones SÍ está en alcance de este change (ratificado).

#### Scenario: CTA Crear visible y no completa Create
- **WHEN** Super Admin está en el Index de roles de plataforma
- **THEN** ve el control Crear rol y al usarlo no completa un alta de rol

## MODIFIED Requirements

### Requirement: Matriz de permisos en edición de rol de plataforma
El sistema SHALL mostrar en la edición de un rol de plataforma los permisos de las
Functionalities del catálogo (agrupados por Module; acciones
`viewAny`/`view`/`create`/`update`/`delete`) para sincronizarlos al rol. En roles
universales SHALL omitir módulos AdminOnly. SHALL permitir editar y guardar la
matriz de las plantillas universales `manager` y `guest`. SHALL NOT permitir
eliminar esas plantillas desde este CRUD. SHALL NOT usar la matriz de la plantilla
universal `manager` como techo que recorte funcionalidades de un módulo ya
autorizado y activado para una empresa. Los permisos efectivos del rol local
`manager` de cada company SHALL seguir gobernados por el sync de autorizaciones;
guardar la plantilla SHALL NOT sustituir ese sync.

#### Scenario: Universales sin AdminOnly en la matriz
- **WHEN** Super Admin edita un rol universal
- **THEN** no se muestran módulos AdminOnly en la matriz de permisos

#### Scenario: Genérico de plataforma puede incluir AdminOnly
- **WHEN** Super Admin edita un rol genérico de plataforma (`universal = false`)
- **THEN** la matriz puede incluir módulos AdminOnly y guardar esos permisos en el rol

#### Scenario: Plantillas manager y guest editables
- **WHEN** Super Admin abre la edición de la plantilla universal `manager` o `guest`
  y guarda permisos marcados
- **THEN** `role_has_permissions` de esa plantilla refleja la selección; la plantilla
  sigue sin poder borrarse

#### Scenario: Sync manager local intacto
- **WHEN** se guarda la matriz de la plantilla universal `manager`
- **THEN** el rol local `manager` de cada company no se recorta automáticamente por
  esa plantilla; sigue el sync de módulos autorizados/activados

#### Scenario: Guardar permisos en rol genérico o universal
- **WHEN** Super Admin marca o desmarca permisos en un rol de plataforma editable y
  guarda
- **THEN** `role_has_permissions` del rol refleja la selección guardada

#### Scenario: Nuevas funcionalidades aparecen en la matriz
- **WHEN** se crea una Functionality (y sus permisos) en el catálogo
- **THEN** esos permisos pasan a estar disponibles en el panel de edición de roles de
  plataforma (omitidos en universales si el Module es AdminOnly)
