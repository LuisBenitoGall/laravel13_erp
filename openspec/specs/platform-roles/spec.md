## Purpose

CRUD Super Admin de roles Spatie con `company_id` NULL (universales vs genéricos de plataforma), matriz de permisos, protección de Super Admin/manager/guest y materialización de universales al crear Company.

## Requirements

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
`viewAny`/`view`/`create`/`update`/`delete`) para sincronizarlos al rol según las
reglas de editabilidad. En roles universales SHALL omitir módulos AdminOnly. SHALL
NO usar la matriz de la plantilla universal `manager` como techo que recorte
funcionalidades de un módulo ya autorizado y activado para una empresa.

#### Scenario: Universales sin AdminOnly en la matriz
- **WHEN** Super Admin edita un rol universal
- **THEN** no se muestran módulos AdminOnly en la matriz de permisos

#### Scenario: Genérico de plataforma puede incluir AdminOnly
- **WHEN** Super Admin edita un rol genérico de plataforma (`universal = false`)
- **THEN** la matriz puede incluir módulos AdminOnly y guardar esos permisos en el rol

#### Scenario: Plantilla manager solo lectura en matriz
- **WHEN** Super Admin abre la edición de la plantilla universal `manager`
- **THEN** la matriz de permisos de negocio no es editable como techo (solo lectura o
  equivalente i18n); el sync del manager local sigue gobernando los permisos efectivos

#### Scenario: Guardar permisos en rol genérico o universal distinto de manager
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
