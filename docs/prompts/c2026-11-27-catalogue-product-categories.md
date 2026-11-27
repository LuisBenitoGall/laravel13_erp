<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-27-catalogue-product-categories
Rule: 10-architecture
Summary: Segundo change fino de taxonomía Catalog: maestro **Category** por
  Company (nombre llano, slug auto, status, árbol N niveles vía
  `parent_id`), CRUD y menú Categorías bajo Catálogo. Sin vínculo a
  Product ni a Family.
Goals:
- Entidad **`Category`** en dominio `Catalog`, por Company (`company_id` /
  `BelongsToCompany` / R-TEN-05).
- Campos: **nombre** (llano, sin i18n JSON), **slug** auto, **status**
  Active/Inactive, **`company_id`**, **`parent_id`** nullable (árbol de
  **N niveles**; no limitado a 2 como v1).
- CRUD usable: Index (ServerTable y/o presentación de árbol — lo cierra
  10), Create, Edit (y delete si 10 lo incluye).
- Menú: ítem **Categorías** bajo el grupo **Catálogo** (hermanado con
  Artículos y Familias ya existentes), estilo v1. Functionality +
  permisos Spatie propios; naming EN = segmento ruta (p. ej. under
  Module `products`).
- Glosario / ficha Catalog / module-map; tests Pest (CRUD, slug, árbol /
  parent, R-TEN-05, menú/V-21).
NonGoals:
- Vínculo Product ↔ Category (0..N opcional) — **change de clasificación
  en ficha**, tras Tag también.
- Family ↔ Category N:N — **change futuro** (ya anotado).
- Tag; atributos; tarifas; precios; Index thumbs.
- Traducciones JSON del nombre.
- Soft-delete vs hard-delete, unicidad slug/nombre, status default en
  Create, política al borrar nodo con hijos, ciclos/`parent` inválido,
  UI exacta del árbol en Index: **no debatidos en detalle** → decide 10
  (o A/B a Luis si es negocio).
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
- form-layout-polish
- testing-best-practices
Notes:
- Origen: `/opsx-explore` post Family archivado/operativo. Plan:
  Family ✓ → **Category** → Tag → clasificación ficha → Family↔Category.
- Decisiones Luis: mismos campos que Family (**nombre + slug auto +
  status + company_id**) **más `parent_id`**; árbol **N niveles**;
  relación con productos **fuera** de este change; Family↔Category N:N
  más tarde.
- Family ya abrió el menú grupo Catálogo (Artículos + Familias); este
  change añade Categorías.
- v1 Category era 2 niveles (`subcategory`); v2 = adjacency list N
  niveles (`parent_id`).
- Misión explore: **solo brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: Category por Company con árbol N
El sistema SHALL persistir categorías de catálogo por Company
(`company_id`) con nombre llano, slug automático, status Active/Inactive
y `parent_id` nullable formando un árbol de N niveles. Listados y
mutaciones SHALL estar aislados por tenant (R-TEN-05). SHALL NOT exigir
traducciones JSON en este change.

#### Scenario: Aislamiento por company
- **WHEN** un actor de la company A lista Categorías
- **THEN** solo ve categorías de A (no las de otra company)

#### Scenario: Alta raíz
- **WHEN** un actor autorizado crea una categoría sin padre
- **THEN** queda como nodo raíz con slug auto y status según contrato 10

#### Scenario: Alta hija
- **WHEN** un actor autorizado crea una categoría con un padre válido de
  la misma company
- **THEN** queda como hija de ese padre (profundidad N permitida)

### Requirement: CRUD Category y menú Categorías
El sistema SHALL exponer CRUD de Category y un ítem de menú **Categorías**
bajo el grupo Catálogo, visible según capas Module + permiso de la
Functionality que 10 asigne.

#### Scenario: Acceso al Index
- **WHEN** un actor autorizado navega al listado de Categorías
- **THEN** obtiene el listado/árbol de categorías de su company

#### Scenario: Menú
- **WHEN** un actor con acceso al Module productos/catálogo y permiso de
  Categorías obtiene el menú
- **THEN** ve Categorías bajo Catálogo junto a Artículos y Familias

### Requirement: Sin vínculo a Product ni Family en este change
Este change SHALL NOT añadir pivote Product↔Category, ni Family↔Category,
ni UI de clasificación en la ficha de producto.

#### Scenario: Product Edit sin categorías
- **WHEN** se complete este change
- **THEN** la ficha Edit de Product no muestra aún selector de categorías


## MODIFIED Requirements

### Requirement: Menú Catálogo (catalogue-module / menú)
El grupo Catálogo del Module `products` SHALL incluir el subítem
**Categorías** además de Artículos y Familias.

#### Scenario: Catálogo agrupa Categorías
- **WHEN** el actor autorizado abre el menú Catálogo
- **THEN** puede ir a Categorías además de Artículos y Familias
