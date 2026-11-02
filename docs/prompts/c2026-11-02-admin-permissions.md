<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-02-admin-permissions
Rule: 10-architecture
Summary: CRUD Admin de permisos Spatie (catálogo), desacoplado de Roles: Index + Create
  custom vinculados a Functionality + Delete, con canónicos auto-provisionados
  protegidos. Sustituye el enlace temporal Permisos→Roles.
Goals:
- Proponer un **change nuevo** de CRUD de permisos Spatie bajo Admin/Configuración,
  tomando como referencia funcional v1 `/admin/permissions` y el patrón UI de Modules
  (Inertia + ServerTable + modal Create; sin Livewire).
- Index: listado ServerTable de permisos (nombre Spatie, funcionalidad derivada del
  prefijo, Acciones).
- Create: permisos **custom** además de los auto-generados; siempre vinculados a una
  Functionality; nombre final `{functionality.slug}.{sufijo}` **único**.
- Delete: permitido solo para custom; los canónicos
  `{slug}.viewAny|view|create|update|delete` y el comodín `{module}.access` **no** se
  borran desde esta UI (opción B acordada).
- Sufijos reservados en Create: bloquear `viewAny|view|create|update|delete` y
  `access` (canónicos van por el generador / CreateFunctionality, no a mano).
- Menú Configuración → ítem **Permisos** deja de apuntar a Roles y apunta a este Index.
- Actualizar specs que hablen de “×7 acciones” o del enlace temporal Permisos→Roles:
  el contrato vigente es **5 acciones + access**.
NonGoals:
- Asignación de permisos a Roles (matriz) — change posterior.
- Permisos directos a User (pantalla v1 user-permissions) — fuera de alcance.
- Regenerar/recrear lote canónico desde esta UI.
- Página Edit de permiso (v1 tampoco edita el name).
- CRUD de Roles (siguiente hilo tras este catálogo).
Skills:
- openspec-propose (change nuevo + artefactos)
- tenancy-rbac (Module ≠ Functionality, 5+access, menú, Spatie)
- inertia-react-development (Index ServerTable + modal Create)
- v1-reference (solo lectura: laravel8 `/admin/permissions`)
Notes:
- Origen: `/opsx-explore` tras cerrar Modules; Luis prefiere Permisos antes que Roles
  porque el catálogo puede existir desacoplado de la asignación.
- Auto-provision al crear Functionality **ya existe** (`CreateFunctionality` → 5
  permisos + access); este change **no** lo reabre, solo convive (Create custom +
  listado + delete custom).
- Vínculo Functionality↔Permission: convención de nombre (sin FK), como v1.
- `menu-core-pillars` hoy: ítem Permisos temporal → Roles y *SHALL NOT* CRUD custom;
  este change **sustituye** ese requisito. `Admin.md` ya marcaba “CRUD permisos =
  futuro”.
- Gate de autorización (¿Functionality `permissions` vs solo Super Admin como resto
  de Configuración plataforma): no se cerró en explore — 10 alinea con el patrón
  existente de Modules/Roles y lo deja explícito en design.
- Hard delete Spatie (no soft delete de Permission): planteado en explore alineado a
  v1; 10 lo confirma en design si no hay contradicción con el kernel.
- Idioma del agente: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Index Admin de permisos Spatie
El sistema SHALL exponer un Index Admin de permisos Spatie (ServerTable) con al menos
el nombre del permiso, la funcionalidad asociada (vía prefijo del name) y Acciones.
El ítem de menú Configuración → Permisos SHALL navegar a este Index (ya no a Roles).

#### Scenario: SA abre Permisos
- **WHEN** un Super Admin abre Permisos desde el menú Configuración
- **THEN** llega al Index de permisos Spatie (no a la UI de Roles)

#### Scenario: Listado muestra vínculo a Functionality
- **WHEN** el Index carga filas de permisos
- **THEN** cada fila muestra el name Spatie y la funcionalidad derivada del prefijo
  `{functionalitySlug}.*`

### Requirement: Create de permiso custom vinculado a Functionality
El sistema SHALL permitir crear permisos Spatie **adicionales** a los auto-provisionados.
Cada permiso nuevo SHALL vincularse a una Functionality existente mediante el name
`{functionality.slug}.{sufijo}`, y ese name SHALL ser único. SHALL NOT aceptar sufijos
reservados `viewAny`, `view`, `create`, `update`, `delete` ni `access`.

#### Scenario: Alta custom válida
- **WHEN** el usuario elige una Functionality y un sufijo no reservado cuyo name
  completo no exista
- **THEN** se crea el permiso Spatie `{slug}.{sufijo}` (guard web)

#### Scenario: Sufijo reservado rechazado
- **WHEN** el usuario intenta crear un permiso con sufijo canónico o `access`
- **THEN** la validación lo rechaza (los canónicos siguen saliendo solo del
  auto-provision)

#### Scenario: Name duplicado rechazado
- **WHEN** el name `{slug}.{sufijo}` ya existe
- **THEN** la validación lo rechaza por unicidad

### Requirement: Delete solo de permisos custom
El sistema SHALL permitir eliminar permisos Spatie custom desde Acciones del Index.
SHALL NOT eliminar desde esta UI los canónicos
`{functionalitySlug}.viewAny|view|create|update|delete` ni los comodines
`{moduleSlug}.access`.

#### Scenario: Delete custom
- **WHEN** el usuario confirma Eliminar en un permiso custom
- **THEN** el permiso Spatie se elimina

#### Scenario: Canónico protegido
- **WHEN** el usuario intenta eliminar un permiso canónico o un `*.access`
- **THEN** la operación se deniega (no se borra)


## MODIFIED Requirements

### Requirement: Ítem Permisos temporal hacia Roles
(De `menu-core-pillars`.) El ítem Permisos deja de ser temporal hacia Roles: pasa a
enlazar el CRUD de catálogo de permisos. Se retira el *SHALL NOT* de CRUD custom.
La referencia a “funcionalidades×7 acciones” se alinea al contrato vigente:
**5 acciones (`viewAny`/`view`/`create`/`update`/`delete`) + comodín `{module}.access`**.

#### Scenario: Permisos ya no abre Roles
- **WHEN** Super Admin abre Permisos desde el menú
- **THEN** navega al Index de permisos Spatie, no a Roles

#### Scenario: Contrato de acciones canónicas
- **WHEN** se documenta o se valida el set auto-provisionado por Functionality
- **THEN** son exactamente las 5 acciones anteriores más el comodín de módulo
  `access` (no un set de 7)
