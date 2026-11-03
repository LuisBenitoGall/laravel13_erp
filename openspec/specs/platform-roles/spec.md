## Purpose

CRUD Super Admin de roles Spatie con `company_id` NULL (universales vs genéricos de plataforma), matriz de permisos, protección de Super Admin/manager/guest y materialización de universales al crear Company.

## Requirements

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

### Requirement: CRUD de roles de plataforma (Super Admin)
El sistema SHALL permitir a Super Admin listar, crear y editar roles Spatie con
`company_id` NULL, clasificados como universales (`universal = true`, usables por
tenants / defaults de empresa) o genéricos de plataforma (`universal = false`, no
usables por tenants). SHALL excluir el rol `Super Admin` del CRUD editable. SHALL
impedir borrar las plantillas universales `manager` y `guest`. SHALL permitir borrar
otros roles de plataforma solo si no tienen usuarios asignados.

#### Scenario: Listar roles de plataforma
- **WHEN** Super Admin abre el listado de roles de plataforma
- **THEN** ve roles con `company_id` NULL gestionables y no puede editar ni borrar el
  rol `Super Admin` vía este CRUD

#### Scenario: Crear rol universal o de plataforma
- **WHEN** Super Admin crea un rol indicando nombre, descripción opcional y si es
  universal o genérico de plataforma
- **THEN** el rol queda persistido con `company_id` NULL y la clasificación indicada

#### Scenario: Manager y guest no borrables
- **WHEN** Super Admin intenta eliminar la plantilla universal `manager` o `guest`
- **THEN** la operación se rechaza

#### Scenario: Super Admin inmutable
- **WHEN** cualquier actor intenta modificar o borrar el rol `Super Admin` vía el CRUD
  de roles de plataforma
- **THEN** la operación no está permitida

#### Scenario: Borrar rol no protegido sin asignaciones
- **WHEN** Super Admin elimina un rol de plataforma que no es `Super Admin`, `manager`
  ni `guest` y no tiene usuarios asignados
- **THEN** el rol se elimina

#### Scenario: No borrar rol con usuarios asignados
- **WHEN** Super Admin intenta eliminar un rol de plataforma no protegido que tiene
  usuarios en `model_has_roles`
- **THEN** la operación se rechaza

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

### Requirement: Materialización de roles universales al crear Company
El sistema SHALL, al crear una Company, materializar como roles locales
(`company_id` = esa company, mismo `name`) todas las plantillas con `company_id` NULL
y `universal = true` distintas de `Super Admin`. Para `manager`, los permisos del rol
local SHALL quedar gobernados por el sync de autorizaciones (no por copia de la
matriz de la plantilla). Para el resto de universales materializados, SHALL copiar
los permisos de la plantilla al rol local en el momento del alta. Universales creados
después del alta de una company SHALL NO materializarse automáticamente en companies
ya existentes (backfill solo vía comando explícito).

#### Scenario: Alta materializa manager y guest locales
- **WHEN** se crea una Company nueva
- **THEN** existen roles locales `manager` y `guest` con `company_id` de esa company

#### Scenario: Alta materializa otros universales existentes
- **WHEN** existe una plantilla universal adicional (p. ej. distinta de manager/guest) y
  se crea una Company nueva
- **THEN** existe el rol local homónimo con permisos copiados desde la plantilla

#### Scenario: Universal nuevo no backfill automático
- **WHEN** Super Admin crea una plantilla universal nueva tras existir companies
- **THEN** esas companies existentes no reciben el rol local hasta un backfill
  explícito; las companies creadas después sí lo materializan al nacer
