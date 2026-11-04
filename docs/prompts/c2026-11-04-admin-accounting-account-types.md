<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-04-admin-accounting-account-types
Rule: 10-architecture
Summary: Portar a v2 Grupos Contables (v1 AccountingAccountType / PGC España) bajo
  Configuración: catálogo jerárquico de referencia para asistir la creación futura
  de cuentas; seeder v1; Index CRUD + vista show asistente; sin status.
Goals:
- Dominio **Admin** (Functionality Configuración); entidad `AccountingAccountType`;
  menú **Configuración → Grupos contables**; Functionality
  `accounting-account-types`; permisos Spatie asociados.
- Esquema alineado a v1: `autoreference` (padre; 0=raíz), `code`, `name`, `mode`
  nullable, softDeletes. **Sin** status Active/Inactive.
- UI: Index ServerTable (code, name, level como v1); Create/Edit **modales**;
  Edit solo name (code inmutable tras alta, como v1 update); Acciones Edit +
  Delete confirm; **incluir show** (vista asistente/referencia PGC).
- Seeder: portar `AccountingAccountTypesTableSeeder.php` (estructura + lógica
  niveles); nombres vía i18n `fiscal.*` como en v1 (`trans` / claves equivalentes
  en es/en/ca); documentar en README § Seeders.
- Soft delete. Sin company_id.
NonGoals:
- Endpoint cascada `select` por autoreference (va con alta de AccountingAccount).
- Generación / CRUD de cuentas contables.
- status Active/Inactive.
- Livewire / DataTables / Blade.
Skills:
- openspec-propose
- tenancy-rbac
- v1-reference (`AccountingAccountTypeController`, seeder, show/index,
  `lang/*/fiscal.php`)
- inertia-react-development
Notes:
- Origen: `/opsx-explore`; ruta v1 `/admin/accounting-account-types`.
- Respuestas: (1) modales; (2) sin status + soft delete; (3) show sí; (4) select
  después con cuentas; (5) i18n fiscal como seeder.
- v1 `show.blade.php` duplica el árbol con `trans('fiscal.*')` (no lee BD). 10
  decide si show v2 es árbol desde BD o árbol i18n estático; el seeder sí escribe BD.
- `mode` / `group` del seeder: conservar semántica v1.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Catálogo global AccountingAccountType (PGC)
El sistema SHALL persistir grupos contables como catálogo global sin
`company_id`, con soft delete, campos autoreference, code, name y mode nullable.
SHALL NOT usar status Active/Inactive.

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

### Requirement: CRUD Configuración / Grupos contables
El sistema SHALL exponer bajo Configuración → Grupos contables un Index
ServerTable (code, name, level) con Acciones Edit modal y Delete confirm, y
Create por modal. En Edit, code SHALL ser inmutable. Autorización vía
Functionality `accounting-account-types`.

#### Scenario: SA abre Grupos contables
- **WHEN** un usuario autorizado abre Configuración → Grupos contables
- **THEN** ve el Index ServerTable

#### Scenario: Edit no cambia code
- **WHEN** el usuario edita un grupo existente
- **THEN** puede cambiar name pero no code

#### Scenario: Sin permiso
- **WHEN** un usuario sin viewAny fuerza el Index
- **THEN** recibe 403

### Requirement: Vista show asistente PGC
El sistema SHALL exponer una vista show de referencia/asistente del plan de
grupos contables (equivalente funcional a v1 show), accesible desde el flujo de
Configuración / Grupos contables.

#### Scenario: SA abre show
- **WHEN** un usuario autorizado abre la vista show de grupos contables
- **THEN** ve la jerarquía de referencia del PGC

### Requirement: Seeder PGC con i18n fiscal
El sistema SHALL incluir un seeder portado del v1 que construye la jerarquía PGC
España y resuelve nombres vía claves i18n fiscal (como el seeder v1), documentado
en README § Seeders.

#### Scenario: Seed en entorno limpio
- **WHEN** se ejecuta el seeder de accounting account types
- **THEN** existen los grupos jerárquicos del PGC con codes/names coherentes


## MODIFIED Requirements

### Requirement: Ítem Grupos contables en Configuración
El menú Configuración SHALL incluir Grupos contables hacia el Index del catálogo.

#### Scenario: SA ve Grupos contables
- **WHEN** un Super Admin obtiene el menú Configuración
- **THEN** ve la entrada Grupos contables
