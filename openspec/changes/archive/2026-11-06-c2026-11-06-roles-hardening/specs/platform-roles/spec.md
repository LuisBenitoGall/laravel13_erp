## ADDED Requirements

### Requirement: Create plataforma solo metadatos (sin permissions)
El sistema SHALL rechazar o impedir de forma estricta cualquier `permissions`
enviado en el Create/store de rol de plataforma; el alta solo persiste
metadatos y deja la matriz para Edit.

#### Scenario: POST Create con permissions
- **WHEN** un cliente envía `permissions` en el store de rol plataforma
- **THEN** esos permisos no quedan asignados por el Create (rechazo de
  validación o equivalente inequívoco) y el rol nace sin esa matriz

### Requirement: Locales materializados nunca universales
Al materializar plantillas universales en una company, cada rol local creado o
asegurado SHALL quedar con `universal = false`.

#### Scenario: Materialización fuerza no universal
- **WHEN** se ejecuta la materialización de defaults para una company
- **THEN** los roles locales resultantes tienen `universal = false`

## MODIFIED Requirements

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
