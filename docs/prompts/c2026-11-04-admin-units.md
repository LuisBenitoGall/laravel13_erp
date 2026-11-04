<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-04-admin-units
Rule: 10-architecture
Summary: Portar a v2 el maestro de unidades de v1 (Unit) como catálogo global bajo
  Configuración / Unidades, mismo patrón que monedas (modales, ServerTable,
  Active-first, seeder), dominio Admin.
Goals:
- Proponer change espejo de currencies (catálogo plano).
- Dominio **Admin**; Functionality `units` bajo módulo SaaS `admin`; menú
  **Configuración → Unidades** (Luis: son functionalities de Configuración; no
  abrir dominio Catalog/Units aparte para este maestro).
- Esquema: name (unique), slug, symbol (max 4 como v1), soft delete, status string
  Active/Inactive. **Omitir** columna `translations` de v1.
- UI: Index ServerTable + Create/Edit **modales**; Acciones toggle + Edit +
  Delete; sin columna Estado; default sort **Active-first** + name (sin sort
  cliente).
- Permisos Spatie `units.*`.
- Seeder desde `units.json` / dump v1; **IDs preservados**; status **A)** del
  JSON; documentar en README § Seeders (fuera de DatabaseSeeder).
- Sin import automático al Index; sin jsonUnits().
NonGoals:
- Campo `translations`.
- ProductUnits / pivots producto (consumidores Catalog).
- Tenancy / `company_id`.
- Módulo SaaS nuevo.
- Livewire / DataTables / Blade.
Skills:
- openspec-propose
- tenancy-rbac (catálogo global, Functionality, menú Configuración)
- v1-reference (`UnitController`, migration, `Unit::import`, `storage/json/units.json`)
- inertia-react-development (patrón Currencies)
Notes:
- Origen: `/opsx-explore`; ruta v1 `/admin/units`.
- Respuestas: (1) Configuración/Admin como monedas — no Catalog ni dominio Units;
  (2) omitir translations; (3) status del JSON.
- module-map menciona unidades en Catalog como entidad de producto a futuro; este
  change solo el maestro Configuración en Admin (10 alinea mapa/glosario sin
  mover el CRUD a Catalog).
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Catálogo global Unit
El sistema SHALL persistir unidades como catálogo global sin `company_id`, con
soft delete y status Active/Inactive (string + enum PHP). Campos: name (único
entre no borrados), slug, symbol nullable max 4. SHALL NOT persistir
`translations`.

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

### Requirement: CRUD Admin Configuración / Unidades
El sistema SHALL exponer bajo Configuración → Unidades un Index ServerTable con
Acciones (toggle, Edit modal, Delete confirm) y Create/Edit por modal.
Autorización vía Functionality `units` y `units.*`. Index SHALL NOT mostrar
columna Estado. Sin sorts del cliente: Active-first y luego name.

#### Scenario: SA abre Unidades
- **WHEN** un usuario autorizado abre Configuración → Unidades
- **THEN** ve el Index ServerTable de units

#### Scenario: Default Active-first
- **WHEN** se solicita el `table` de units sin sorts
- **THEN** Active primero y, dentro de cada grupo, por name

#### Scenario: Sin permiso
- **WHEN** un usuario sin `units.viewAny` fuerza la URL del Index
- **THEN** recibe 403

### Requirement: Seeder unidades v1 con status del JSON
El sistema SHALL incluir un seeder que cargue unidades desde datos v1, preserve
IDs, respete status del origen, documentado en README § Seeders, sin ejecutarse
al abrir el Index.

#### Scenario: Seed en entorno limpio
- **WHEN** se ejecuta el seeder de unidades en base limpia
- **THEN** existen los registros con los mismos IDs y el status del JSON


## MODIFIED Requirements

### Requirement: Ítem Unidades en Configuración
El menú Configuración SHALL incluir Unidades (Functionality `units`) hacia el
Index del catálogo.

#### Scenario: SA ve Unidades en Configuración
- **WHEN** un Super Admin obtiene el menú Configuración
- **THEN** ve la entrada Unidades hacia el Index de units
