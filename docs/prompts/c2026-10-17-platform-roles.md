<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-17-platform-roles
Rule: 10-architecture
Summary: CRUD de roles de plataforma para Super Admin (listar, crear, editar) con matriz
de permisos, distinguiendo roles universales (uso tenant / por defecto por empresa) y
roles genéricos solo de plataforma. `Super Admin` inmutable; `manager` y `guest`
universales no borrables. El techo de acceso tenant es por módulo contratado (todas las
funcionalidades), no por checkbox suelto en la plantilla universal.
Goals:
- UI Super Admin: listar, crear y editar roles de plataforma (referencia funcional v1
  Configuración → Roles: pestañas por módulo, checkboxes por funcionalidad/acción).
- Al crear/editar, indicar si el rol es **universal** (usable por tenants / roles por
  defecto de empresa) o **genérico de plataforma** (no usable por tenants).
- Roles por defecto actuales: `manager` e `invitado`/`guest`. Se materializan en cada
  Company al crearla (ya existe); en el futuro podrán existir más defaults con la misma
  mecánica.
- `manager` y `guest` nacen como universales y **no se pueden borrar**, ni siquiera por
  Super Admin.
- Rol `Super Admin` **inmutable**: fuera del CRUD; nunca se toca.
- Matriz de permisos: a medida que existan módulos/funcionalidades, sus permisos
  aparecen en el panel para añadir/quitar en el rol.
- En roles **universales**, la matriz **no muestra** módulos AdminOnly.
- Techo tenant: si Super Admin habilita un módulo y la empresa lo activa/contrata, el
  acceso a las funcionalidades de ese módulo es **total**. Super Admin **no** recorta
  acciones sueltas (p. ej. no quita `facturas.delete` del acceso de empresa). El sync
  del `manager` local sigue dando todos los permisos de módulos Basic/Optional
  autorizados (sin AdminOnly). El recorte fino de permisos es del manager al crear
  roles custom de empresa (change posterior).
NonGoals:
- Asignación User↔rol en UI (va en change posterior). Excepción ya vigente: onboarding
  asigna `manager` al creador de la empresa.
- Roles custom creados por el manager del tenant (change posterior).
- Consola Platform ancha (tenants, billing, planes de pago).
- Cambiar las tres capas de módulos ni el bypass de Super Admin.
- Cambiar el sync actual del manager local (sigue siendo “todo lo autorizado
  Basic/Optional, sin AdminOnly”).
Skills:
- tenancy-rbac
- v1-reference
- laravel-specialist
- laravel-patterns
Notes:
- Prerrequisitos archivados: company-roles, manager-permission-sync, admin-catalog,
  auth-onboarding, i18n.
- Dos entornos de roles (acordado): (1) generales/plataforma → solo Super Admin;
  (2) por tenant → manager, con módulos activos de su empresa.
- Referencia v1: `RoleController` Admin, flag `universal`, `company_id` NULL en
  universales, pantalla edit con módulos/funcionalidades. En v2: Spatie Teams +
  nombres `manager`/`guest` (glosario); no clonar Livewire/Blade ni los 7 permisos v1
  (`viewAny/view/create/update/delete`).
- Implicación acordada: la matriz sobre plantillas universales `manager`/`guest` **no**
  actúa como techo de permisos de negocio del tenant; el techo es módulo habilitado +
  activado. La matriz editable con sentido pleno aplica sobre todo a roles **genéricos
  de plataforma**. Detalle de UX (matriz de universales solo lectura vs oculta vs
  edición sin efecto en sync) lo delimita 10-architecture sin contradecir el techo.
- Si Super Admin crea un universal nuevo: ¿se materializa solo en companies futuras, o
  también en las existentes? No cerrado en explore → decide 10 (o pregunta a Luis).
- Borrado de roles que no sean `Super Admin` / `manager` / `guest`: el debate pidió
  CRUD (listar/crear/editar); 10 delimita si destroy entra para el resto.


## ADDED Requirements

### Requirement: CRUD de roles de plataforma (Super Admin)
El sistema SHALL permitir a Super Admin listar, crear y editar roles de plataforma,
marcando cada uno como universal (uso tenant / por defecto de empresa) o genérico de
plataforma (no usable por tenants). SHALL dejar el rol `Super Admin` fuera de este CRUD
(inmutable). SHALL impedir borrar los roles universales `manager` y `guest`.

#### Scenario: Listar roles de plataforma
- **WHEN** Super Admin abre el apartado de roles de plataforma
- **THEN** ve los roles gestionables en ese entorno (sin poder editar/borrar `Super Admin`)

#### Scenario: Crear rol universal o de plataforma
- **WHEN** Super Admin crea un rol e indica si es universal o genérico de plataforma
- **THEN** el rol queda persistido con esa clasificación

#### Scenario: Manager y guest no borrables
- **WHEN** Super Admin intenta eliminar el rol universal `manager` o `guest`
- **THEN** la operación se rechaza

#### Scenario: Super Admin inmutable
- **WHEN** cualquier actor intenta modificar o borrar el rol `Super Admin`
- **THEN** la operación no está permitida (no forma parte del CRUD)

---

### Requirement: Matriz de permisos en edición de rol de plataforma
El sistema SHALL mostrar en la edición de un rol de plataforma los permisos de las
Functionalities existentes (agrupados por módulo, patrón visual tipo v1) para
añadirlos o quitarlos del rol. En roles universales SHALL omitir módulos AdminOnly.
SHALL NO usar esa matriz sobre roles universales como techo que recorte funcionalidades
de un módulo ya habilitado/activado para una empresa.

#### Scenario: Universales sin AdminOnly en la matriz
- **WHEN** Super Admin edita un rol universal
- **THEN** no se muestran módulos AdminOnly en la matriz de permisos

#### Scenario: Techo por módulo, no por acción suelta
- **WHEN** una empresa tiene un módulo autorizado y activado
- **THEN** tiene acceso a todas las funcionalidades/permisos de ese módulo; Super Admin
  no recorta acciones individuales vía la plantilla universal

#### Scenario: Nuevas funcionalidades aparecen en la matriz
- **WHEN** se crea una Functionality (y sus permisos) en el catálogo
- **THEN** esos permisos pasan a estar disponibles en el panel de edición de roles de
  plataforma (sujeto a la regla AdminOnly en universales)


## MODIFIED Requirements

### Requirement: Roles manager y guest por empresa
Sin cambiar la materialización al crear Company ni la asignación de `manager` al
creador en onboarding: se aclara que `manager` y `guest` son los roles universales /
por defecto de serie (no borrables) y que el sync del manager local no queda limitado
por checkboxes de la plantilla universal.

#### Scenario: Alta de company materializa defaults
- **WHEN** se crea una Company nueva
- **THEN** siguen existiendo los roles locales `manager` y `guest` de esa company; el
  creador (onboarding) recibe `manager`

#### Scenario: Sync manager intacto respecto al techo modular
- **WHEN** se autoriza o desautoriza un módulo Basic/Optional para una company
- **THEN** el sync del `manager` local sigue añadiendo/quitando todos los permisos de
  las Functionalities de ese módulo (sin AdminOnly), sin un recorte adicional desde la
  matriz de la plantilla universal
