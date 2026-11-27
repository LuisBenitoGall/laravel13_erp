<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-27-catalogue-product-families
Rule: 10-architecture
Summary: Primer change fino de taxonomía Catalog: maestro **Family** por
  Company (nombre llano, slug auto, status), listado ServerTable y menú
  bajo Catálogo al estilo v1. Sin vínculo a Product (change posterior).
Goals:
- Entidad **`Family`** en dominio `Catalog`, por Company (`company_id` /
  `BelongsToCompany` / R-TEN-05).
- Campos de este change: **nombre** (string llano, sin i18n JSON),
  **slug** generado automáticamente, **status** Active/Inactive,
  **`company_id`**.
- CRUD usable: Index `<ServerTable>`, Create, Edit (y delete si 10 lo
  cierra al completar el CRUD).
- Menú: ítem **Familias** bajo el pilar/grupo **Catálogo**, como en v1
  (captura: Catálogo → Artículos, Familias, Categorías, …). Hoy Catálogo
  es hoja a productos; 10 reorganiza el menú para que Catálogo agrupe al
  menos Artículos/Productos + Familias (Categorías/Tags/… en changes
  siguientes). Naming Functionality/rutas en inglés lo fija 10 (criterio
  Module/Functionality EN = segmento ruta).
- Functionality + permisos Spatie propios del maestro (o el corte que 10
  alinee al kernel Module≠Functionality); sin inventar Module SaaS nuevo
  si cabe bajo `products`.
- Glosario / ficha `Catalog.md` / module-map si hace falta; tests Pest
  (CRUD, slug, status, R-TEN-05).
NonGoals:
- Vínculo Product ↔ Family (0..1 opcional) — **change de clasificación
  en ficha**, después de los tres maestros.
- Category, Tag, atributos, tarifas, productos patrón, productos
  contables (otros ítems del menú v1).
- Family ↔ Category N:N — **change futuro** explícito (anotado en
  explore; no este).
- Traducciones JSON del nombre; árbol (Family es plana).
- Imágenes/docs de familia; Index thumbs de producto; precios.
- Soft-delete vs hard-delete, unicidad nombre/slug por company, status
  por defecto en Create: **no debatidos** → decide 10 (o A/B a Luis).
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
- form-layout-polish
- testing-best-practices
Notes:
- Origen: `/opsx-explore` post Imágenes/Documentos. Plan taxonomía:
  **3 changes finos** Family → Category (árbol N niveles) → Tag; luego
  clasificación en ficha Product (familia 0..1, categorías 0..N, tags
  0..N; **todo opcional**); más tarde Family↔Category N:N.
- Decisiones Luis (2026-11-27): (1) ficha producto = como máx. **una**
  familia, no obligatoria; (2) Category N niveles; (3) Family↔Category
  N:N después; (4) 3 changes maestros; (5) nombres llanos; (6) Family
  campos = **B** nombre + status + slug auto + `company_id`; (7) relación
  con productos **fuera** de este change; (8) menú v1 como referencia
  visual (Familias hermano de Artículos bajo Catálogo).
- Captura v1 menú Catálogo: Artículos, Familias, Categorías, Tarifas,
  Productos patrones, Atributos, Productos contables. Este change solo
  materializa **Familias** (+ abrir grupo Catálogo si aún es hoja).
- Tags no aparecen en ese menú v1; en v2 Tag será entidad (change 3);
  ubicación menú la fija 10 cuando toque.
- Misión explore: **solo brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: Family por Company
El sistema SHALL persistir familias de catálogo pertenecientes a la
Company de sesión (`company_id`). Listados y mutaciones SHALL estar
aislados por tenant (R-TEN-05).

#### Scenario: Aislamiento por company
- **WHEN** un actor de la company A lista Familias
- **THEN** solo ve familias de A (no las de otra company)

### Requirement: Campos mínimos Family
Una Family SHALL tener nombre (string llano), slug generado
automáticamente y status Active/Inactive, más `company_id`. SHALL NOT
exigir traducciones JSON en este change.

#### Scenario: Alta con nombre
- **WHEN** un actor autorizado crea una familia con un nombre válido
- **THEN** queda persistida con slug auto y un status Active o Inactive
  según el contrato que cierre 10

### Requirement: CRUD Family y menú Familias
El sistema SHALL exponer CRUD de Family (Index ServerTable, Create, Edit)
y un ítem de menú **Familias** bajo el grupo Catálogo (hermanado con el
acceso a productos/artículos), visible según capas Module + permisos de
la Functionality que 10 asigne.

#### Scenario: Acceso al Index
- **WHEN** un actor autorizado navega al listado de Familias
- **THEN** obtiene el ServerTable de familias de su company

#### Scenario: Menú
- **WHEN** un actor con acceso al módulo de productos/catálogo y permiso
  de Familias obtiene el menú
- **THEN** ve Familias bajo Catálogo (no solo una hoja única a productos)

### Requirement: Sin vínculo a Product en este change
Este change SHALL NOT añadir `family_id` ni pivote Product↔Family ni UI
de clasificación en la ficha de producto.

#### Scenario: Product Edit sin familia
- **WHEN** se complete este change
- **THEN** la ficha Edit de Product no exige ni muestra aún selector de
  familia


## MODIFIED Requirements

### Requirement: Menú Catálogo (catalogue-module / menú)
El menú del Module de productos/catálogo SHALL pasar de (o dejar de ser
solo) una hoja única al listado de productos, a un grupo Catálogo que
incluya al menos el acceso a productos y el ítem Familias, alineado a la
organización v1 mostrada en captura.

#### Scenario: Catálogo agrupa Familias
- **WHEN** el actor autorizado abre el menú Catálogo
- **THEN** puede ir a Familias además del listado de productos/artículos
