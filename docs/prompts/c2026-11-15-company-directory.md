<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-15-company-directory
Rule: 10-architecture
Summary: Directorio de empresas bajo el menú Empresas (layout tipo
  e-commerce: aside multi-sector OR + grid ordenable). Universo: companies
  Active con NIF y ≥1 sector. Acceso práctico solo con `empresas.access`.
  Card → Show informativo (sin CTA contactar/cliente-proveedor).
Goals:
- Ítem de menú bajo Module `empresas`: Directorio →
  `/admin/company-directory` (Functionality `company-directory`).
- Authz: en la práctica basta `empresas.access` (Basic); Functionality
  con `.view` sembrado a roles básicos para cumplir menú sin fricción
  extra de permiso.
- Layout: aside izquierdo con checkboxes de sectores Active (filtro
  **OR**: al menos uno de los marcados); derecha grid de cards con
  ordenación habitual (nombre A→Z / Z→A, más recientes) y paginación.
- Card: logo, name, datos útiles; click → ficha Show **solo informativa**.
- Universo listado: `status=active`, `nif` no vacío, pertenece a ≥1
  sector. Sin `is_public` en Company.
- Excepción a ServerTable: superficie de descubrimiento (grid), no Index
  Tabulator; declarar en design.
NonGoals:
- Botón contactar / alta como cliente o proveedor (change siguiente).
- Flag `is_public` en Company.
- CRUD Mis empresas; catálogo Sector; tab company-sectors.
- Draft/closed en el directorio.
- AND en filtros multi-sector.
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore` tras sectores + atribución company.
- Pivot `company_sector` / `whereHas('sectors')`; sin modelo
  CompanySector.
- Sort acordado como “habituales”: name asc/desc + más recientes
  (created_at desc).
- Show: sin CTA contactar en este change.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Directorio de empresas en menú Empresas
El sistema SHALL exponer bajo Module `empresas` un Directorio en
`/admin/company-directory` (Functionality `company-directory`). El acceso
de menú SHALL quedar usable con `empresas.access` (Basic), sin exigir un
permiso Spatie adicional que frene a roles básicos (`.view` sembrado a
esos roles).

#### Scenario: Entrada de menú
- **WHEN** un usuario con `empresas.access` (sesión con company) obtiene
  el menú
- **THEN** ve Directorio bajo Empresas y puede abrir
  `/admin/company-directory`

### Requirement: Filtro por sectores (OR) y grid
El Directorio SHALL mostrar un aside con checkboxes de sectores Active y
un grid de resultados a la derecha. Con N sectores marcados, SHALL
devolver companies que tengan **al menos uno** de ellos (OR). SHALL
permitir ordenar por nombre (asc/desc) y más recientes, con paginación.

#### Scenario: Filtro OR
- **WHEN** el actor marca dos sectores
- **THEN** el grid incluye companies Active con NIF que tengan cualquiera
  de esos sectores

#### Scenario: Sin filtro de sector
- **WHEN** no hay sectores marcados
- **THEN** el grid lista el universo elegible (Active + NIF + ≥1 sector)

### Requirement: Universo Active + NIF + sector
El Directorio SHALL incluir solo companies con status Active, NIF
presente y al menos un sector vinculado. SHALL NOT usar `is_public`.

#### Scenario: Excluye sin NIF o sin sector o no Active
- **WHEN** una company es draft, o sin NIF, o sin sectores
- **THEN** no aparece en el Directorio

### Requirement: Card a ficha informativa
Cada card SHALL navegar a una ficha Show informativa de la company.
SHALL NOT mostrar en esa ficha (ni en el card) botón de contactar ni de
incluir como cliente/proveedor en este change.

#### Scenario: Abrir ficha
- **WHEN** el actor hace click en una card
- **THEN** ve la ficha informativa de esa company sin CTAs de
  contacto/cliente/proveedor


## MODIFIED Requirements

### Requirement: Menú Empresas con Mis empresas y Directorio
(El pilar `empresas` con Mis empresas.) SHALL añadir el subítem Directorio
junto a Mis empresas, sin sustituir el Index de Mis empresas.

#### Scenario: Dos entradas
- **WHEN** el usuario ve el menú Empresas
- **THEN** tiene Mis empresas y Directorio como entradas distintas
