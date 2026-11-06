## Purpose

CRUD Super Admin de roles Spatie con `company_id` NULL (universales vs genéricos de
plataforma): Create solo metadatos + Edit con toggles async (canónicos y custom),
Guardar=descripción, copy a companies del perímetro y layout v1; Index con CTA
Crear; protección Super Admin/manager/guest; materialización de universales al
crear Company.

## Requirements

### Requirement: Locales materializados nunca universales
Al materializar plantillas universales en una company, cada rol local creado o
asegurado SHALL quedar con `universal = false`.

#### Scenario: Materialización fuerza no universal
- **WHEN** se ejecuta la materialización de defaults para una company
- **THEN** los roles locales resultantes tienen `universal = false`

### Requirement: Create plataforma solo metadatos (sin permissions)
El sistema SHALL rechazar o impedir de forma estricta cualquier `permissions`
enviado en el Create/store de rol de plataforma; el alta solo persiste
metadatos y deja la matriz para Edit.

#### Scenario: POST Create con permissions
- **WHEN** un cliente envía `permissions` en el store de rol plataforma
- **THEN** esos permisos no quedan asignados por el Create (rechazo de
  validación o equivalente inequívoco) y el rol nace sin esa matriz

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

### Requirement: CTA Crear rol navega a Create
El Index de roles de plataforma SHALL mostrar un control Crear rol en la
ActionBar que navega al flujo Create. SHALL completar el alta vía ese flujo
(ya no stub).

#### Scenario: CTA Crear abre Create
- **WHEN** Super Admin pulsa Crear rol en el Index de roles de plataforma
- **THEN** navega a la pantalla Create de roles de plataforma

### Requirement: Formulario Create de rol de plataforma (metadatos)
El sistema SHALL exponer un formulario Create de rol de plataforma con nombre
obligatorio, descripción opcional e indicador universal (sí/no). SHALL NOT
mostrar la matriz de permisos en Create. SHALL NOT aceptar ni persistir
`permissions` en el alta. Tras un alta válida SHALL persistir el rol con
`company_id` NULL y redirigir a Edit para atribuir permisos.

#### Scenario: Create solo metadatos
- **WHEN** Super Admin abre Create de rol de plataforma
- **THEN** ve nombre, descripción y universal, y no ve la matriz de permisos

#### Scenario: Alta y redirect a Edit
- **WHEN** Super Admin guarda un rol nuevo válido (universal o genérico)
- **THEN** el rol queda persistido con `company_id` NULL y es redirigido a Edit

#### Scenario: Permisos se atribuyen en Edit
- **WHEN** Super Admin llega a Edit tras crear un rol
- **THEN** puede marcar y guardar permisos en la matriz (contrato Edit existente)

### Requirement: CRUD de roles de plataforma (Super Admin)
El sistema SHALL permitir a Super Admin listar, crear y editar roles Spatie con
`company_id` NULL, clasificados como universales (`universal = true`, usables por
tenants / defaults de empresa) o genéricos de plataforma (`universal = false`, no
usables por tenants). SHALL excluir el rol `Super Admin` del CRUD editable. SHALL
impedir borrar las plantillas universales `manager` y `guest`. SHALL permitir borrar
otros roles de plataforma solo si no tienen usuarios asignados. El alta Create
SHALL capturar metadatos (nombre, descripción, universal); la atribución de
permisos SHALL ocurrir en Edit.

#### Scenario: Listar roles de plataforma
- **WHEN** Super Admin abre el listado de roles de plataforma
- **THEN** ve roles con `company_id` NULL gestionables y no puede editar ni borrar el
  rol `Super Admin` vía este CRUD

#### Scenario: Crear rol universal o de plataforma
- **WHEN** Super Admin crea un rol indicando nombre, descripción opcional y si es
  universal o genérico de plataforma
- **THEN** el rol queda persistido con `company_id` NULL y la clasificación indicada
  (sin exigir permisos en el alta)

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
versionar el nombre del rol nuevo. Los permisos del rol local destino SHALL
ser la intersección de los del origen con el catálogo tenant del destino
(autorizados ∩ activos ∩ ¬AdminOnly).

#### Scenario: Copy con nombre libre
- **WHEN** se copia un rol no universal a una company del perímetro (distinta
  de la de sesión) sin rol homónimo
- **THEN** existe el rol local con ese nombre y los permisos del origen que
  pertenezcan al catálogo filtrado del destino

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

### Requirement: Materialización de roles universales al crear Company
El sistema SHALL, al crear una Company, materializar como roles locales
(`company_id` = esa company, mismo `name`, `universal = false`) todas las
plantillas con `company_id` NULL y `universal = true` distintas de
`Super Admin`. Para `manager`, los permisos del rol local SHALL quedar
gobernados por el sync de autorizaciones (no por copia de la matriz de la
plantilla). Para el resto de universales materializados, SHALL copiar los
permisos de la plantilla al rol local en el momento del alta. Universales
creados después del alta de una company SHALL NO materializarse
automáticamente en companies ya existentes (backfill solo vía comando
explícito).

#### Scenario: Alta materializa manager y guest locales
- **WHEN** se crea una Company nueva
- **THEN** existen roles locales `manager` y `guest` con `company_id` de esa
  company y `universal = false`

#### Scenario: Alta materializa otros universales existentes
- **WHEN** existe una plantilla universal adicional (p. ej. distinta de
  manager/guest) y se crea una Company nueva
- **THEN** existe el rol local homónimo con `universal = false` y permisos
  copiados desde la plantilla

#### Scenario: Universal nuevo no backfill automático
- **WHEN** Super Admin crea una plantilla universal nueva tras existir
  companies
- **THEN** esas companies existentes no reciben el rol local hasta un
  backfill explícito; las companies creadas después sí lo materializan al
  nacer
