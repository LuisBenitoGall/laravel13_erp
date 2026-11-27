<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-26-catalogue-product-crud
Rule: 10-architecture
Summary: Arrancar el dominio Catalog con el CRUD mínimo de Product por
  Company: alta solo con nombre y tipo (físico|servicio), status Inactive
  por defecto; ficha Edit con tabs (tab General en este change); tipo
  inmutable en lectura junto a auditoría; listado en `/admin/catalogue`.
Goals:
- Dominio PHP **`Catalog`** (`App\Domains\Catalog`) con entidad **Product**
  por Company (`BelongsToCompany` / perímetro tenant).
- CRUD usable: Index, Create, Edit (y delete si 10 lo incluye al cerrar
  el CRUD; no fijado en explore).
- **Create**: únicamente **nombre** (string llano) + **tipo**
  (`physical` | `service` / producto físico | servicio). **Status =
  inactive** asignado en servidor; no se elige en el form de alta.
- **Edit**: montada con **`RecordEditLayout` + tabs**. En este change solo
  el tab de información genérica (p. ej. General): nombre editable +
  status Active/Inactive. **Tipo inmutable**: no en el form; aparece de
  **lectura** en la info genérica (`RecordSummary`) junto a fechas de
  creación/actualización y demás auditoría.
- Rutas del listado y recurso bajo **`/admin/catalogue`** (p. ej. index
  `GET /admin/catalogue`, create/edit análogos).
- Module/Functionality SaaS + menú para acceder al catálogo; naming
  inglés normativo lo fija 10, alineado a la URL `catalogue` (evitar
  desalineación slug/href).
- Glosario + ficha módulo Catalog + actualización module-map si hace
  falta; tests Pest (CRUD, default Inactive, tipo inmutable, R-TEN-05).
NonGoals:
- Traducciones / nombre i18n JSON (nombre llano ahora; traducciones en
  change posterior).
- Unidades, IVA, cuentas contables, MOQ, código/SKU, fulltext.
- Familias, categorías, tags, atributos.
- Precios versionados (`ProductPrice`), tarifas tipológicas A/B/C.
- Catálogo proveedor (`ProductProvider`).
- Imágenes / documentos de producto.
- Consumo desde presupuestos, pedidos, albaranes, inventarios, ofertas
  (solo dejar el maestro listo para enganchar después).
- Tabs vacíos/stub de fases futuras (cada change añadirá su tab con
  contenido real).
- Soft-delete vs hard-delete, unicidad de nombre, columnas exactas del
  Index: **no debatidos** → decide 10 (o A/B a Luis si es negocio).
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
- form-layout-polish
- testing-best-practices
Notes:
- Origen: `/opsx-explore` post aparcar calendarios/ausencias. Pipeline
  de producto: catálogo por fases; **cada fase = un change** pulido.
  Esta es la **fase 1** (CRUD mínimo).
- Visión §2.6 / module-map ya nombran Catalog (Product, precios,
  familias…); este change **no** implementa todo el mapa — solo Product
  mínimo.
- Decisiones Luis (2026-11-26): (1) tipo **inmutable**; RO en summary con
  auditoría; (2) nombre llano, traducciones después; (3) ruta listado
  **`/admin/catalogue`**; Create = nombre+tipo; status inicial Inactive;
  Edit con tabs.
- Dominio código `Catalog` vs URL `catalogue` (grafía UK): URL manda en
  rutas; 10 alinea Module/Functionality/pages.
- Enum labels UI («Producto físico» / «Servicio») vs valores
  `physical`/`service`: 10 fija naming canónico.
- Misión explore: **solo brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: Dominio Catalog con Product por Company
El sistema SHALL introducir el dominio `Catalog` con la entidad Product
perteneciente a la Company de sesión. Los productos SHALL estar aislados
por tenant (R-TEN-05).

#### Scenario: Aislamiento por company
- **WHEN** un actor de la company A lista el catálogo
- **THEN** solo ve productos de A (no los de otra company)

### Requirement: Alta mínima de producto
El sistema SHALL permitir crear un producto con únicamente nombre y tipo
(físico o servicio). El status inicial SHALL ser Inactive y SHALL NOT
exigirse ni elegirse en el formulario de creación.

#### Scenario: Create solo nombre y tipo
- **WHEN** un actor autorizado crea un producto con nombre y tipo servicio
- **THEN** el producto queda persistido con status Inactive y ese tipo

### Requirement: Tipo inmutable y visible en summary
Tras el alta, el tipo del producto SHALL ser inmutable. En la ficha de
edición el tipo SHALL mostrarse en modo lectura en la información
genérica (junto a fechas/auditoría), no como campo editable del
formulario.

#### Scenario: Edit no cambia el tipo
- **WHEN** un actor abre la edición de un producto físico
- **THEN** ve el tipo como dato de lectura en el summary y no puede
  cambiarlo al guardar el formulario

### Requirement: Edit con tabs y tab General
La ficha de edición SHALL usar layout con tabs. En este change SHALL
existir el tab de información genérica donde se editan al menos nombre y
status (Active/Inactive). SHALL NOT añadir tabs stub de fases futuras.

#### Scenario: Activar desde Edit
- **WHEN** un actor autorizado pone status Active en el tab General y
  guarda
- **THEN** el producto queda Active sin alterar el tipo

### Requirement: Listado en /admin/catalogue
El listado del catálogo de productos SHALL estar expuesto en la ruta
`/admin/catalogue` (y el resto del recurso CRUD bajo ese prefijo).

#### Scenario: Acceso al Index
- **WHEN** un actor autorizado navega a `/admin/catalogue`
- **THEN** obtiene el listado de productos de su company


## MODIFIED Requirements

### Requirement: Mapa de dominios Catalog (module-map)
El mapa SHALL reflejar que el dominio Catalog arranca con el CRUD de
Product (sin pretender que precios/familias/tarifas ya existan).

#### Scenario: Catalog documentado como iniciado
- **WHEN** se consulte module-map / ficha tras el change
- **THEN** Catalog aparece con Product operativo y el resto de
  capacidades del mapa siguen como pendientes de changes posteriores
