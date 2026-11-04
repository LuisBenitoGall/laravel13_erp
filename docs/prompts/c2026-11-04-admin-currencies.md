<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-04-admin-currencies
Rule: 10-architecture
Summary: Portar a v2 el maestro de monedas de v1 (Currency) como catálogo global
  Admin bajo Configuración / Monedas, mismo patrón que countries: stack
  Inertia/ServerTable/Policies, seeder desde datos v1, sin company_id.
Goals:
- Proponer change nuevo espejo de geografía/countries (catálogo plano, sin drill).
- Dominio **Admin**; Functionality `currencies` bajo módulo `admin`; menú
  **Configuración → Monedas**.
- Esquema alineado a v1 (`name`, `slug`, `code`, `number`, `symbol`, soft delete)
  con **status** string Active/Inactive (enum PHP). Columna `symbol` **max 8**
  (no char(2) de v1).
- UI como v1 adaptada a v2: Index ServerTable (name, code, number, symbol,
  Acciones: toggle + Edit modal + Delete); Create modal con campos de alta;
  **sin columna Estado** (toggle basta, polish vigente).
- Edit como v1: **code y number inmutables**; editables name (→ slug) y symbol
  (y status vía toggle/form según patrón countries).
- Permisos Spatie `currencies.*`.
- Seeder con datos v1 (JSON/seeder v1); **IDs preservados**; status: **Active**
  para monedas de uso habitual (10 define el set mínimo EUR/USD/… en design) y
  Inactive el resto (v1 importaba casi todo a 0).
- Documentar el seeder en `README.md` § Seeders (norma ya acordada).
NonGoals:
- Tenancy / `company_id`.
- Módulo SaaS nuevo.
- Drill anidado (no aplica).
- Consumidores (CompanySettings, facturas, etc.) más allá del catálogo + menú.
- Import automático al abrir Index (v1 `Currency::import()` en index); en v2 el
  seed es explícito vía Artisan/README.
- Livewire / DataTables / Blade.
Skills:
- openspec-propose
- tenancy-rbac (catálogo global, Functionality, menú Configuración)
- v1-reference (`CurrencyController`, migration, `CurrenciesTableSeeder` /
  `storage/json/currencies.json`)
- inertia-react-development (Index ServerTable + modales; patrón Countries)
Notes:
- Origen: `/opsx-explore` tras countries; Luis: mismo enfoque, Configuración/
  Monedas, guiarse por v1.
- Respuestas: (1) Edit code/number inmutables como v1; (2) Active solo habituales;
  (3) symbol max 8.
- Reutilizar patrones Countries (Policies, Actions, soft delete, Acciones, i18n,
  MenuBuilder CONFIGURATION_SLUGS + route).
- 10 decide set “habituales” Active (mínimo razonable EUR, USD; ampliar si v1/seed
  lo deja claro) y dejarlo explícito en design/seeder.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Catálogo global Currency
El sistema SHALL persistir monedas como catálogo global sin `company_id`, con soft
delete y status Active/Inactive (string + enum PHP). Campos: name, slug, code
(nullable, único entre no borrados), number (nullable, único entre no borrados),
symbol (nullable, max 8).

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

### Requirement: CRUD Admin Configuración / Monedas
El sistema SHALL exponer bajo Configuración → Monedas un Index ServerTable con
Acciones (toggle status, Edit, Delete confirm) y Create/Edit por modal (o
equivalente v2). Edit SHALL NOT permitir cambiar code ni number. Autorización vía
Functionality `currencies` y permisos `currencies.*`. Index SHALL NOT mostrar
columna Estado textual si existe toggle en Acciones.

#### Scenario: SA abre Monedas
- **WHEN** un usuario autorizado abre Configuración → Monedas
- **THEN** ve el Index ServerTable de currencies

#### Scenario: Code/number inmutables en Edit
- **WHEN** el usuario edita una moneda existente
- **THEN** puede cambiar name/symbol (y status vía el flujo acordado) pero no
  code ni number

#### Scenario: Sin permiso
- **WHEN** un usuario sin `currencies.viewAny` fuerza la URL del Index
- **THEN** recibe 403

### Requirement: Seeder monedas v1 con habituales Active
El sistema SHALL incluir un seeder que cargue monedas desde datos v1, preserve
IDs, marque como Active las de uso habitual y Inactive el resto, y quede
documentado en README § Seeders.

#### Scenario: Seed en entorno limpio
- **WHEN** se ejecuta el seeder de monedas en base limpia
- **THEN** existen los registros portados; las habituales están Active


## MODIFIED Requirements

### Requirement: Ítem Monedas en Configuración
El menú Configuración SHALL incluir Monedas (Functionality `currencies`) hacia el
Index del catálogo.

#### Scenario: SA ve Monedas en Configuración
- **WHEN** un Super Admin obtiene el menú Configuración
- **THEN** ve la entrada Monedas hacia el Index de currencies
