<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-27-catalogue-product-images
Rule: 10-architecture
Summary: Añadir tab Imágenes en la ficha Edit de Product (patrón UserImages):
  tabla propia `product_images`, upload, featured (portada), flag `public`,
  disco privado y entrega autenticada. Sin thumbs en Index (change posterior).
Goals:
- Tab **Imágenes** en `Catalog/Products/Edit` (`RecordEditLayout`), aplicable
  a productos **físicos y servicios**.
- Tabla/modelo **`product_images`** (no mezclar con documentos): `product_id`,
  **`company_id`** (denormalizado para facilitar perímetro/queries), path,
  **`featured`** (una portada por producto), **`public`**, patrón alineado a
  `UserImage` en lo que 10 cierre (soft delete, índices, unique featured…).
- Storage en **disco privado** (no URL anónima `/storage/…`); entrega del
  binario solo por **rutas autenticadas** de la app.
- UI como Usuarios: Dropzone (kit Form ya existente) + grid/lightbox;
  eliminar; marcar featured; toggle `public`. En la ficha Edit el actor
  autorizado ve **todas** las imágenes del producto.
- Límites como User imágenes: jpg/jpeg/png/gif/webp; **1 MB**/fichero;
  máx **20** por producto (y por subida según patrón User).
- Authz de gestión: permisos de producto ya existentes (`products.*`);
  **sin** Functionality nueva.
- Origen: **solo upload**. Galería `MediaAsset` / `MediaPicker` = Contents;
  no usar como fuente de imágenes de producto.
- Regla de visibilidad fuera de la ficha Edit (otros contextos del ERP:
  catálogo proveedor→cliente, otras vistas): **solo imágenes con
  `public=true`**. El flag no significa internet anónimo; significa
  visible para quien no gestiona la ficha (p. ej. cliente del proveedor
  dentro del ERP).
- Este change deja lista la **gestión + ruta de entrega + regla
  dueño/editor vs solo-public**, para que changes futuros (Index/thumbs,
  presupuestos, vista catálogo proveedor) enganchen sin rediseñar storage.
- Tests Pest: upload/límites, featured único, public, perímetro
  company/`company_id`, entrega authz, R-TEN-05.
- Actualizar ficha `docs/modules/Catalog.md` / glosario si 10 lo requiere.
NonGoals:
- Tab / entidad **Documentos** de producto (`product_docs`) — **change
  siguiente** aparte (misma oleada mental; no este).
- Thumbs / portada en **Index** (dashboard/listado) — **change propio**
  más adelante.
- Montar la vista “catálogo del proveedor” para clientes.
- Consumo en presupuestos, pedidos, albaranes, ofertas.
- Elegir imágenes desde Galería (`MediaPicker` / `MediaAsset`).
- Traducciones nombre, SKU, unidades, IVA, familias/tags, precios,
  tarifas, proveedor.
- Taxonomía (Family/Category/Tag) — sigue después de Imágenes/Documentos
  en el plan de catálogo.
- Functionality Spatie nueva solo para imágenes.
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
- form-layout-polish
- testing-best-practices
Notes:
- Origen: `/opsx-explore` post CRUD Product archivado
  (`c2026-11-26-catalogue-product-crud`). Plan catálogo por fases; antes
  de taxonomía se insertan Imágenes y luego Documentos.
- Referencia UI/dominio: `UserImage` / `UserImagesPanel` / specs
  `user-images` (tras foundation-hardening: disco **privado**). v1 tenía
  `product_docs` unificado con mime+featured — **descartado** a favor de
  dos tablas (como User en v2).
- Decisiones Luis (2026-11-27): (1) featured sí; (2) `public` sí en
  imágenes (y en Documentos del change siguiente); (3) límites = User;
  (4) authz = `products.*`; (5) solo upload; (6) físico y servicio;
  (7) incluir `company_id`; (8) disco privado; (9) dos changes
  Imágenes→Documentos; (10) Index/thumbs change aparte.
- Visibilidad: dentro del ERP autenticado. En Edit: todas. Fuera de
  Edit (cliente viendo catálogo de proveedor u otras vistas): solo
  `public=true`.
- **Abierto para 10 / A-B a Luis:** además de `adjunto.public`, ¿hace
  falta puerta a nivel producto? Explore planteó A (solo flag adjunto),
  **B** (Product Active + `public`), C (`product.is_public` + `public`).
  Luis no cerró A/B/C; explore recomienda **B**. No inventar en spec sin
  cerrarlo.
- Documentos: misma línea (`product_docs`, `public`, sin featured,
  límites docs User); **no** implementar aquí — solo contexto de
  secuencia.
- Misión explore: **solo brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: Schema product_images y modelo ProductImage
El sistema SHALL persistir imágenes de producto en `product_images` con
`product_id`, `company_id`, path de fichero, `featured` y `public`. Los
ficheros SHALL guardarse en disco privado bajo un path por producto (10
fija el path canónico). SHALL NOT usar disco público con URL anónima.

#### Scenario: Alta de imagen
- **WHEN** un actor autorizado sube una imagen válida a un producto de su
  company
- **THEN** se crea `ProductImage` con `company_id` del producto, path en
  storage privado y `featured`/`public` en false por defecto

#### Scenario: Un solo featured
- **WHEN** se marca una imagen como featured
- **THEN** queda como única featured de ese producto

### Requirement: Tab Imágenes en ficha Edit de Product
La ficha Edit de Product SHALL incluir el tab Imágenes (físico y
servicio) con Dropzone, grid/lightbox, eliminar, marcar featured y
toggle `public`. Authz: permisos de producto existentes. En Edit SHALL
listarse todas las imágenes del producto (públicas y no).

#### Scenario: Listado en Edit
- **WHEN** el producto tiene imágenes
- **THEN** se muestran en grid con eliminar, featured, public y preview

#### Scenario: Validación formato y peso
- **WHEN** se sube un fichero fuera de jpg/jpeg/png/gif/webp o mayor de
  1 MB
- **THEN** se rechaza con error de validación

#### Scenario: Límite 20
- **WHEN** la subida o el total del producto superaría 20 imágenes
- **THEN** se rechaza la operación

#### Scenario: Ajeno sin permiso o fuera de company
- **WHEN** un actor no autorizado o de otra company fuerza mutaciones
- **THEN** recibe 403 o rechazo de perímetro

### Requirement: Entrega autenticada y visibilidad public
El sistema SHALL servir el binario solo mediante rutas autenticadas. En
la ficha Edit, el actor autorizado a gestionar el producto SHALL poder
ver todas las imágenes. En otros contextos del ERP (p. ej. cliente
accediendo al catálogo de un proveedor u otras vistas) SHALL exponerse
solo imágenes con `public=true` (detalle de authz cross-company lo fija
10; sin montar aún esas vistas de consumo).

#### Scenario: Sin login no obtiene binario
- **WHEN** un cliente no autenticado solicita la URL de entrega
- **THEN** no obtiene el binario (no 200 anónimo de fichero)

#### Scenario: Editor ve no-pública
- **WHEN** un actor autorizado abre Edit y el producto tiene una imagen
  `public=false`
- **THEN** la ve y puede servirla en ese contexto de gestión

#### Scenario: Fuera de Edit solo públicas
- **WHEN** un contexto ERP distinto de la gestión de ficha solicita
  imágenes del producto para mostrarlas
- **THEN** solo las `public=true` son elegibles

### Requirement: Sin consumo Index en este change
Este change SHALL NOT añadir la portada/thumbs al Index de productos.
SHALL dejar la entrega y el modelo listos para un change posterior de
Index/dashboard.

#### Scenario: Index sin thumb obligatorio
- **WHEN** se complete este change
- **THEN** el Index de productos no requiere mostrar aún la imagen
  featured


## MODIFIED Requirements

### Requirement: Edit de Product con tabs (catalogue-products)
Los tabs de la ficha Edit de Product SHALL incluir Imágenes operable
además del tab General ya existente. SHALL NOT añadir tab Documentos ni
stubs de fases futuras en este change.

#### Scenario: Tab Imágenes presente
- **WHEN** se abre la edición de un producto
- **THEN** existe el tab Imágenes operable según este change
