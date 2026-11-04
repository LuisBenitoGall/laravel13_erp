<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-04-admin-countries
Rule: 10-architecture
Summary: Portar a v2 el maestro geográfico de v1 (Country → Province → Town) como
  catálogo global Admin bajo Configuración / Países: mismas entidades y lógica de
  negocio, stack Inertia/ServerTable/Policies, sin company_id, con seeder desde datos
  v1 y endpoints de cascada.
Goals:
- Proponer change nuevo con ficha/artefactos para **Country + Province + Town**
  (alcance completo, opción A).
- Catálogo **global** (sin `company_id` / sin BelongsToCompany); dominio **Admin**;
  Functionality `countries` bajo módulo `admin`; menú **Configuración → Países**.
- Esquema alineado a v1 (countries / provinces / towns + soft delete) con **status**
  como Modules: enum PHP + columna string Active/Inactive (opción C), no integer 0/1.
- Naming código como v1: `Country`, `Province`, `Town` (UI “población” vía i18n).
- UI **como v1** (sin reinventar navegación): Index países → drill provincias por
  país → drill poblaciones por provincia; Create/Edit (modales o equivalente v2);
  Acciones con toggle status, enlace al nivel hijo, Edit, Delete confirm — adaptado
  a ServerTable + kit formularios / estilos v2 (contrato Tabulator, i18n, Policies).
- Permisos Spatie `countries.*` cubren también provincias y poblaciones (como v1).
- Campo `flag` **nullable** (sin upload obligatorio en este change).
- Endpoints de cascada listos: país→provincias, provincia→poblaciones (equivalente
  v1 `country_provinces` / `province-towns`).
- Seeder con los **datos de v1** (países, provincias, poblaciones), mapeando status
  1/0 → Active/Inactive.
NonGoals:
- Dependencia de tenant / `company_id`.
- Módulo SaaS nuevo distinto de `admin`.
- Renombrar `Town` a Population/Municipality.
- Upload/gestión avanzada de banderas.
- Consumidores futuros (Partners workplaces, obras, etc.) más allá de dejar los
  endpoints de cascada.
- Livewire / DataTables / clonar Blade v1.
Skills:
- openspec-propose (change nuevo + ficha módulo si aplica + artefactos)
- tenancy-rbac (catálogo global, Functionality bajo admin, menú Configuración)
- v1-reference (solo lectura: Country/Province/Town controllers, migraciones, seeders)
- inertia-react-development (Indexes ServerTable + Acciones + modales)
- laravel-best-practices / new-module o playbook § catálogos `--global`
Notes:
- Origen: `/opsx-explore`; Luis: replicar módulo países v1→v2 con stack v2;
  tablas/registros/lógica de v1; Configuración/Países; maestro sin company_id.
- Referencia v1: `CountryController`, `ProvinceController`, `TownController`;
  migraciones `countries`/`provinces`/`towns`; seeders (Towns muy grande);
  rutas anidadas provincias/towns; AJAX cascada en `AdminController`.
- Status: explícitamente **C** (soft delete + Active/Inactive tipo Modules). El
  resto de columnas semánticas como v1 (name, slug, code, alfa2, alfa3, flag,
  FKs country_id / province_id).
- UI: “tal como está en v1” = flujo drill + modales + acciones; **no** tabs estilo
  Functionalities dentro de Edit de Module. Stack visual = v2.
- Seeder: preparar con datos v1; 10 decide en design si IDs se preservan (recomendable
  anotar A/B si afecta FKs futuras) — Luis pidió seeder con datos, no fijó IDs.
- Idioma del agente: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Catálogo global Country / Province / Town
El sistema SHALL persistir países, provincias y poblaciones (`Town`) como catálogo
global sin `company_id`, con soft delete y status Active/Inactive (string + enum
PHP). Province SHALL pertenecer a Country; Town SHALL pertenecer a Province.
Campos de Country alineados a v1 (name, slug, code, alfa2, alfa3, flag nullable).

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

#### Scenario: Jerarquía
- **WHEN** se listan provincias de un país o poblaciones de una provincia
- **THEN** solo aparecen las hijas de ese padre (no soft-deleted por defecto)

### Requirement: CRUD Admin Configuración / Países (flujo v1)
El sistema SHALL exponer bajo Configuración → Países un Index ServerTable de países
con Acciones (toggle status, ir a provincias, Edit, Delete confirm) y alta/edición
según el flujo v1 adaptado a Inertia/React. Desde un país SHALL abrirse el Index de
provincias de ese país; desde una provincia, el Index de poblaciones (`Town`).
Autorización vía Functionality `countries` y permisos `countries.*` (también para
provincias y poblaciones).

#### Scenario: SA abre Países
- **WHEN** un usuario autorizado abre Configuración → Países
- **THEN** ve el Index ServerTable de países

#### Scenario: Drill a provincias y poblaciones
- **WHEN** el usuario elige provincias en Acciones de un país y luego poblaciones
  en Acciones de una provincia
- **THEN** navega a los Indexes anidados correspondientes

#### Scenario: Sin permiso
- **WHEN** un usuario sin `countries.viewAny` fuerza la URL del Index
- **THEN** recibe 403

### Requirement: Endpoints de cascada geográficos
El sistema SHALL exponer endpoints (JSON) para listar provincias de un país y
poblaciones de una provincia, consumibles por formularios futuros.

#### Scenario: Provincias por país
- **WHEN** se solicita la cascada de un Country existente
- **THEN** se devuelven sus provincias activas (o el criterio de filtro que fije
  design alineado a v1)

#### Scenario: Poblaciones por provincia
- **WHEN** se solicita la cascada de una Province existente
- **THEN** se devuelven sus towns según el mismo criterio

### Requirement: Seeder con datos v1
El sistema SHALL incluir un seeder que cargue países, provincias y poblaciones
desde los datos de v1, con status mapeado a Active/Inactive.

#### Scenario: Seed idempotente o documentado
- **WHEN** se ejecuta el seeder en entorno limpio
- **THEN** existen los registros geográficos portados desde v1


## MODIFIED Requirements

### Requirement: Ítem de menú Países en Configuración
(Relacionado con menú Configuración SA / pilares.) El menú Configuración SHALL
incluir Países apuntando al Index del catálogo geográfico (Functionality
`countries` bajo módulo `admin`).

#### Scenario: SA ve Países en Configuración
- **WHEN** un Super Admin obtiene el menú Configuración
- **THEN** ve la entrada Países hacia el Index de countries
