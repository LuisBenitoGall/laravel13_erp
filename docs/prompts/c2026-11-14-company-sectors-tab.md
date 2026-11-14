<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-14-company-sectors-tab
Rule: 10-architecture
Summary: En el Edit de Company, mover la atribución de sectores a un tab
  propio «Sectores» (solo Active). Functionality `company-sectors` bajo
  `empresas`: ver tab con `.view`, guardar sync con `.update`. Sin modelo
  Eloquent `CompanySector` (basta el pivot `company_sector` existente).
Goals:
- Tab `sectors` en `/admin/companies/{id}/edit` con el panel de checkboxes
  (sectores Active); sacar `CompanySectorsPanel` del tab Info.
- Functionality `company-sectors` (Module `empresas`): permisos canónicos;
  tab visible con `company-sectors.view`; sync PUT exige
  `company-sectors.update` (además de perímetro vínculo company).
- Reutilizar Action/ruta/pivot actuales; no crear modelo `CompanySector`.
- Create sin cambios (sin atribución de sectores).
NonGoals:
- Modelo Eloquent `CompanySector` / metadatos en el pivot.
- CRUD catálogo `sectors` (Configuración).
- Directorio de empresas / búsqueda por sector (change futuro).
- Brand; seeder NACE (aparte).
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore`; Luis corrigió a `company-sectors.view` +
  `.update` (no `company-sector.access`).
- Estado: panel ya en Info; authz sync = `companies.update`; pivot
  `company_sector` + `SyncCompanySectors` existen.
- Directorio futuro: `whereHas('sectors')` sin pivot model.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Tab Sectores en Edit Company
El Edit de Company SHALL incluir un tab «Sectores» donde el actor marca
cero o más sectores Active del catálogo. SHALL NOT mostrar ese panel en
el tab Info ni en Create.

#### Scenario: Tab Sectores
- **WHEN** un actor con permiso de ver sectores de company abre el Edit
- **THEN** ve el tab Sectores con la lista de sectores Active y los
  actualmente vinculados

#### Scenario: Sin panel en Info/Create
- **WHEN** se abre Info del Edit o el Create
- **THEN** no está el panel de atribución de sectores en esas pantallas

### Requirement: Permisos company-sectors.view y .update
La visibilidad del tab Sectores SHALL exigir `company-sectors.view` (y el
comodín de módulo `empresas` según menú/acceso de ficha). Guardar la
sincronización de sectores SHALL exigir `company-sectors.update`. SHALL
mantener el perímetro de vínculo a la company.

#### Scenario: Ver tab sin update
- **WHEN** el actor tiene `company-sectors.view` pero no `.update`
- **THEN** puede ver el tab/lectura pero no persistir cambios de sync

#### Scenario: Sync con update
- **WHEN** el actor con `company-sectors.update` guarda sector_ids válidos
  Active en una company vinculada
- **THEN** el pivot refleja el conjunto guardado

#### Scenario: Sync sin update
- **WHEN** un actor sin `company-sectors.update` llama al sync
- **THEN** la operación se rechaza


## MODIFIED Requirements

### Requirement: Company atribuye N sectores
(Authz del spec `admin-sectors-catalog` / company-edit.) La atribución
SHALL vivir en el tab Sectores del Edit y SHALL usar
`company-sectors.view` / `company-sectors.update` en lugar de (o además
de restringir) el mero `companies.update` para el sync.

#### Scenario: Sync sectores
- **WHEN** un actor autorizado con `company-sectors.update` guarda un
  conjunto de sector ids Active en el Edit de una company vinculada
- **THEN** el pivot refleja exactamente ese conjunto
