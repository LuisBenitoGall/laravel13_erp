<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-27-catalogue-product-tags
Rule: 10-architecture
Summary: Tercer change fino de taxonomía Catalog: maestro **Tag** por
  Company (nombre llano, slug auto, status), CRUD ServerTable y menú bajo
  Catálogo. Sin vínculo a Product (change de clasificación posterior).
Goals:
- Entidad **`Tag`** en dominio `Catalog`, por Company (`company_id` /
  `BelongsToCompany` / R-TEN-05). Entidad real (tabla `tags`); **no** CSV
  por locale como v1.
- Campos: **nombre** (llano, sin i18n JSON), **slug** auto, **status**
  Active/Inactive, **`company_id`**. Sin árbol (`parent_id`).
- CRUD usable: Index `<ServerTable>`, Create, Edit (y delete si 10 lo
  cierra), alineado al patrón Family.
- Menú: ítem **Tags** / Etiquetas bajo el grupo **Catálogo** (hermanado
  con Artículos, Familias, Categorías). Functionality + permisos Spatie
  propios; naming EN = segmento ruta bajo Module `products`.
- Glosario / ficha Catalog / module-map; tests Pest (CRUD, slug, R-TEN-05,
  menú/V-21).
NonGoals:
- Vínculo Product ↔ Tag (0..N opcional) — **change de clasificación en
  ficha**, tras este maestro.
- Family ↔ Category N:N; atributos; precios; Index thumbs.
- Traducciones JSON; jerarquía de tags.
- Soft-delete vs hard-delete, unicidad slug/nombre, status default en
  Create: **no debatidos en detalle** → decide 10 (o A/B a Luis),
  preferible parity con Family.
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
- form-layout-polish
- testing-best-practices
Notes:
- Origen: `/opsx-explore` post Family + Category operativos. Plan:
  Family ✓ → Category ✓ → **Tag** → clasificación ficha →
  Family↔Category N:N.
- Decisiones Luis: mismos campos que Family (nombre + slug auto + status
  + `company_id`); plano; menú bajo Catálogo; relación con productos
  **fuera** de este change.
- v1: tags = CSV en `product_tags` por locale — **descartado**.
- Label UI es: 10 elige «Tags» vs «Etiquetas» (v1 menú no listaba Tags;
  en v2 sí hay ítem).
- Misión explore: **solo brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: Tag por Company
El sistema SHALL persistir tags de catálogo por Company (`company_id`)
con nombre llano, slug automático y status Active/Inactive. Listados y
mutaciones SHALL estar aislados por tenant (R-TEN-05). SHALL NOT exigir
traducciones JSON ni modelar tags como CSV.

#### Scenario: Aislamiento por company
- **WHEN** un actor de la company A lista Tags
- **THEN** solo ve tags de A (no los de otra company)

#### Scenario: Alta con nombre
- **WHEN** un actor autorizado crea un tag con un nombre válido
- **THEN** queda persistido con slug auto y status según contrato 10

### Requirement: CRUD Tag y menú
El sistema SHALL exponer CRUD de Tag (Index ServerTable, Create, Edit) y
un ítem de menú bajo el grupo Catálogo, visible según capas Module +
permiso de la Functionality que 10 asigne.

#### Scenario: Acceso al Index
- **WHEN** un actor autorizado navega al listado de Tags
- **THEN** obtiene el ServerTable de tags de su company

#### Scenario: Menú
- **WHEN** un actor con acceso al Module productos/catálogo y permiso de
  Tags obtiene el menú
- **THEN** ve el ítem de Tags/Etiquetas bajo Catálogo junto a Artículos,
  Familias y Categorías

### Requirement: Sin vínculo a Product en este change
Este change SHALL NOT añadir pivote Product↔Tag ni UI de tags en la ficha
Edit de Product.

#### Scenario: Product Edit sin tags
- **WHEN** se complete este change
- **THEN** la ficha Edit de Product no muestra aún selector de tags


## MODIFIED Requirements

### Requirement: Menú Catálogo (catalogue-module / menú)
El grupo Catálogo del Module `products` SHALL incluir el subítem de Tags
además de Artículos, Familias y Categorías.

#### Scenario: Catálogo agrupa Tags
- **WHEN** el actor autorizado abre el menú Catálogo
- **THEN** puede ir a Tags además de Artículos, Familias y Categorías
