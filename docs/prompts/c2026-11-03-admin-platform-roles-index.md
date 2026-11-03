<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-03-admin-platform-roles-index
Rule: 10-architecture
Summary: Dashboard (Index ServerTable) de roles globales de plataforma bajo
  Configuración: listado para Super Admin, columna universal, Acciones tipo Modules
  con Delete, botón Crear visible pero Create/matriz fuera de alcance. Super Admin
  fuera del Index.
Goals:
- Proponer change centrado en el **Index** de roles de plataforma (`company_id` NULL)
  en Configuración (`/admin/roles`), como listado simple ServerTable (sin KPIs ni
  cards).
- Columnas: al menos nombre, descripción, **universal** (mantener), Acciones.
- Acciones al estilo Modules: incluir **Delete** con confirmación (reglas de
  protección ya conocidas: no borrar plantillas `manager`/`guest`; no tocar Super
  Admin).
- Botón **Crear rol** visible en ActionBar (opción B): Create + matriz de permisos
  **fuera de este change** (stub / no completar CRUD Create-Edit aquí).
- Rol **Super Admin** permanece **fuera del Index** (no fila visible).
NonGoals:
- Completar Create / Edit / matriz de permisos (siguiente change).
- Roles tenant (`company_id` set) ni UI bajo Mis Empresas / `company-roles`.
- Compartir componente React con tenant (se pasa condiciones cuando toque tenant).
- Asignación User↔rol.
- Reabrir catálogo de permisos Spatie ni el hueco custom↔matriz×5.
- Mostrar Super Admin en el listado (ni siquiera readonly).
Skills:
- openspec-propose (change nuevo + artefactos)
- tenancy-rbac (roles plataforma vs tenant, Super Admin, Spatie teams)
- inertia-react-development (Index ServerTable + Acciones + ActionBar)
Notes:
- Origen: `/opsx-explore` tras DAFO de permisos; Luis: primero dashboard roles
  globales; tenant después; Create-Edit CRUD completo después.
- **Ya existe** backend/UI de platform roles (Index/Create/Edit/Delete,
  `platform-roles` spec, `PlatformRoleController`, tests). Este change **no**
  reinventar el dominio: ajustar el Index al contrato de UI acordado (Acciones
  Modules-like + Delete; CTA Crear stub B; Super Admin excluido). 10 decide qué
  reutilizar vs qué retirar/ocultar del Index actual (hoy hay link Editar en
  columna).
- Opción B Crear: CTA visible; Create/matriz no se “completan” en este change.
  10 deja explícito en design si el botón está disabled, sin `href` útil, o
  similar — sin implementar el flujo Create aquí.
- Delete en Acciones: sí, operativo en este change (no stub), sujeto a
  protecciones existentes (`manager`/`guest`; Super Admin ni aparece).
- Edit en Acciones: **no** entra (Edit completo = NonGoal); quitar el link Editar
  actual de la columna si sigue ahí.
- Dos rutas futuras (admin vs company) con UI parecida: solo anotar; no diseñar
  tenant ahora.
- Idioma del agente: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Index ServerTable de roles de plataforma
El sistema SHALL exponer bajo Configuración un Index ServerTable de roles globales
(`company_id` NULL) gestionables, con columnas al menos nombre, descripción,
universal y Acciones. El rol `Super Admin` SHALL NOT aparecer en ese listado.

#### Scenario: SA ve listado sin Super Admin
- **WHEN** Super Admin abre Roles en Configuración
- **THEN** ve el ServerTable de roles de plataforma y no ve una fila del rol
  `Super Admin`

#### Scenario: Columna universal
- **WHEN** el Index carga filas
- **THEN** cada fila muestra si el rol es universal (sí/no o equivalente i18n)

### Requirement: Acciones Delete en Index (patrón Modules)
El Index SHALL ofrecer Acciones al estilo Modules incluyendo Delete con
confirmación. SHALL permitir borrar roles de plataforma no protegidos. SHALL NOT
permitir borrar las plantillas `manager`/`guest`. SHALL NOT exponer Edit en
Acciones en este change.

#### Scenario: Delete de rol no protegido
- **WHEN** el usuario confirma Eliminar en Acciones de un rol de plataforma no
  protegido (y elegible según reglas ya existentes de asignaciones, si aplican)
- **THEN** el rol se elimina y deja de aparecer en el listado

#### Scenario: Delete de manager/guest rechazado
- **WHEN** el usuario intenta eliminar la plantilla universal `manager` o `guest`
- **THEN** la operación se rechaza (sin Delete usable o con error de negocio)

### Requirement: Botón Crear rol visible sin completar Create
El Index SHALL mostrar un botón/acción Crear rol en la ActionBar. Este change
SHALL NOT completar el flujo Create ni la matriz de permisos (quedan para un
change posterior).

#### Scenario: CTA Crear visible
- **WHEN** Super Admin está en el Index de roles de plataforma
- **THEN** ve el control Crear rol en la ActionBar

#### Scenario: Create/matriz fuera de alcance
- **WHEN** se revisa el alcance de este change
- **THEN** no se exige entregar Create/Edit/matriz funcionales como parte del DoD
  de este change


## MODIFIED Requirements

<!-- Ninguno obligatorio desde explore: el spec `platform-roles` ya cubre CRUD
     completo; este change acota UI del Index. 10 ajusta delta solo si el Index
     actual (link Editar en columna, Create vivo) debe reflejarse como cambio de
     requisito temporal o de presentación. -->
