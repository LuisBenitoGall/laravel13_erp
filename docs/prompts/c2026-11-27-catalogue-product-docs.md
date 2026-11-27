<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-27-catalogue-product-docs
Rule: 10-architecture
Summary: Añadir tab Documentos en la ficha Edit de Product (patrón UserDocs):
  tabla propia `product_docs`, upload, flag `public` (p. ej. fichas técnicas),
  disco privado y descarga autenticada. Sin featured; sin consumo Index.
Goals:
- Tab **Documentos** en `Catalog/Products/Edit` (`RecordEditLayout`), tras
  Imágenes, aplicable a productos **físicos y servicios**.
- Tabla/modelo **`product_docs`** (no mezclar con imágenes): `product_id`,
  **`company_id`**, path, metadatos de listado/descarga (nombre original,
  mime, tamaño — patrón UserDoc), **`public`**, sin `featured`. Soft delete /
  índices los cierra 10 alineado a UserDoc + product-images.
- Storage en **disco privado**; descarga solo por **rutas autenticadas**
  (nunca URL anónima `/storage/…`).
- UI como Usuarios docs: Dropzone (kit Form) + listado/cards; descargar;
  eliminar (confirm); **toggle `public`**. En Edit el actor autorizado ve
  **todos** los documentos del producto.
- Límites como User docs: pdf/doc/docx/xls/xlsx; **20 MB**/fichero; máx
  **30** por producto (y por subida según patrón User docs).
- Authz de gestión: permisos de producto ya existentes (mismo wiring que
  Product / Imágenes); **sin** Functionality nueva.
- Origen: **solo upload** (no Galería).
- Visibilidad fuera de Edit (catálogo proveedor→cliente u otras vistas
  ERP): solo documentos con **`public=true`**, y con la misma puerta a
  nivel producto ya cerrada en Imágenes: **Product Active ∧ `public`**
  (opción B). Flag ≠ internet anónimo.
- Gestión + ruta de descarga + regla editor vs solo-public listas para
  changes futuros de consumo; **no** montar esas vistas aquí.
- Tests Pest: upload/límites, public, perímetro `company_id`, descarga
  authz, R-TEN-05.
- Actualizar ficha `docs/modules/Catalog.md` / glosario si 10 lo requiere.
NonGoals:
- Tab / entidad **Imágenes** (`product_images`) — change aparte
  (`c2026-11-27-catalogue-product-images`); este change **asume** Imágenes
  ya propuesto/aplicado o al menos no reabre su diseño.
- Featured / portada (solo Imágenes).
- Thumbs / portada en Index; vista catálogo proveedor; consumo en
  presupuestos/pedidos/albaranes/ofertas.
- Share entre users (UserDocs tampoco lo tiene en v2 MVP).
- Traducciones, SKU, unidades, IVA, familias/tags, precios, tarifas,
  taxonomía.
- Functionality Spatie nueva solo para documentos.
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
- form-layout-polish
- testing-best-practices
Notes:
- Origen: `/opsx-explore` mismo hilo que Imágenes; secuencia acordada
  Imágenes → Documentos → (Index thumbs) → taxonomía….
- Referencia: `UserDoc` / `UserDocsPanel` / `user-docs`. Diferencia
  deliberada vs User: Product docs **sí** llevan `public` (fichas
  técnicas visibles a clientes del proveedor dentro del ERP).
- v1 `product_docs` unificado imagen+doc+featured: **descartado**; dos
  tablas v2.
- Decisiones Luis (2026-11-27): (1) tabla `product_docs` aparte; (2)
  `public` sí; (3) límites = User docs; (4) authz = producto; (5) solo
  upload; (6) físico y servicio; (7) `company_id`; (8) disco privado;
  (9) change propio tras Imágenes; (10) Index aparte.
- Elegibilidad fuera de Edit: **B** (Active ∧ public) — cerrada en design
  de `catalogue-product-images` (D3); este brief la reutiliza, no la
  reabre.
- Dependencia de orden: preferible apply Imágenes antes que Documentos
  (tab order + patrón Policy/path). Si el change Imágenes no está en
  `openspec/changes/`, re-proponer/aplicar Imágenes primero.
- Misión explore: **solo brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: Schema product_docs y modelo ProductDoc
El sistema SHALL persistir documentos de producto en `product_docs` con
`product_id`, `company_id`, path, metadatos de fichero (nombre original,
mime, tamaño) y `public`. Los ficheros SHALL guardarse en disco privado.
SHALL NOT usar disco público con URL anónima. SHALL NOT incluir `featured`.

#### Scenario: Alta de documento
- **WHEN** un actor autorizado sube un fichero válido a un producto de su
  company
- **THEN** se crea `ProductDoc` con el `company_id` del producto, path en
  storage privado, metadatos y `public` false por defecto

### Requirement: Tab Documentos en ficha Edit de Product
La ficha Edit de Product SHALL incluir el tab Documentos (físico y
servicio), tras Imágenes, con Dropzone, listado, descarga, eliminar y
toggle `public`. Authz: permisos de producto existentes. En Edit SHALL
listarse todos los documentos del producto.

#### Scenario: Listado en Edit
- **WHEN** el producto tiene documentos
- **THEN** se listan con descarga, eliminar y control `public`

#### Scenario: Validación formato y peso
- **WHEN** se sube un fichero fuera de pdf/doc/docx/xls/xlsx o mayor de
  20 MB
- **THEN** se rechaza con error de validación

#### Scenario: Límite 30
- **WHEN** la subida o el total del producto superaría 30 documentos
- **THEN** se rechaza la operación

#### Scenario: Ajeno sin permiso o fuera de company
- **WHEN** un actor no autorizado o de otra company fuerza mutaciones
- **THEN** recibe 403 o rechazo de perímetro

### Requirement: Descarga autenticada y visibilidad public
El sistema SHALL servir el fichero solo mediante rutas autenticadas. En
Edit, el actor autorizado a gestionar el producto SHALL poder descargar
todos los documentos. Fuera de Edit, SHALL ser elegibles solo documentos
con `public=true` **y** Product Active (misma regla B que Imágenes).
SHALL NOT montar aún vistas de consumo (catálogo proveedor, Index).

#### Scenario: Sin login no descarga
- **WHEN** un cliente no autenticado solicita la URL de descarga
- **THEN** no obtiene el binario

#### Scenario: Editor descarga no-público
- **WHEN** un actor autorizado a actualizar el producto descarga un
  documento `public=false`
- **THEN** recibe el fichero

#### Scenario: Fuera de Edit solo Active y public
- **WHEN** un contexto ERP distinto de la gestión de ficha solicita
  documentos del producto
- **THEN** solo los `public=true` de un producto Active son elegibles


## MODIFIED Requirements

### Requirement: Edit de Product con tabs (catalogue-products)
Los tabs de la ficha Edit de Product SHALL incluir Documentos operable
tras Imágenes (y General). SHALL NOT añadir stubs de fases futuras.

#### Scenario: Tab Documentos presente
- **WHEN** se abre la edición de un producto
- **THEN** existe el tab Documentos operable según este change
