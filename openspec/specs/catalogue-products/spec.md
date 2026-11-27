## Purpose

CRUD de Product por Company: alta con nombre y tipo, status Inactive por
defecto, tipo inmutable en summary, Edit con tabs (General con
clasificación opcional, Imágenes, Documentos) y listado en `/products`.

## Requirements

### Requirement: Product por Company
El sistema SHALL persistir productos pertenecientes a la Company de sesión
(`company_id`). Los listados y mutaciones SHALL estar aislados por tenant
(R-TEN-05).

#### Scenario: Aislamiento por company
- **WHEN** un actor de la company A lista el catálogo
- **THEN** solo ve productos de A (no los de otra company)

#### Scenario: No edita producto ajeno
- **WHEN** un actor de la company A intenta actualizar un producto de B
- **THEN** la operación se rechaza (404/403 según contrato de tenancy)

### Requirement: Alta mínima nombre y tipo
El sistema SHALL permitir crear un producto con únicamente nombre (string
llano) y tipo (`physical` o `service`). El status inicial SHALL ser
`inactive` asignado en servidor. SHALL NOT exigir ni ofrecer status en el
formulario de creación.

#### Scenario: Create servicio Inactive
- **WHEN** un actor autorizado crea un producto con nombre y tipo service
- **THEN** el producto queda persistido con status Inactive y ese tipo

### Requirement: Tipo inmutable visible en summary
Tras el alta, el tipo SHALL ser inmutable. En la ficha de edición el tipo
SHALL mostrarse en modo lectura en la información genérica junto a fechas
y auditoría. SHALL NOT aceptar cambio de tipo en el update.

#### Scenario: Edit muestra tipo RO
- **WHEN** un actor abre la edición de un producto physical
- **THEN** ve el tipo como dato de lectura en el summary y no puede
  cambiarlo al guardar

#### Scenario: Update ignora o rechaza type
- **WHEN** un actor envía un update con un `type` distinto al persistido
- **THEN** el tipo almacenado no cambia (payload ignorado o validación
  rechazada)

### Requirement: Edit con tabs y tab General
La ficha de edición SHALL usar layout con tabs. SHALL existir el tab de
información genérica donde se editan al menos nombre, status
(`active`|`inactive`) y la clasificación opcional Family 0..1, Categories
0..N y Tags 0..N (incl. alta in situ de tags según
`catalogue-product-classification`). SHALL existir el tab Imágenes
operable según la capability `catalogue-product-images`. SHALL existir el
tab Documentos operable según la capability `catalogue-product-docs`, tras
Imágenes. SHALL NOT añadir un tab Clasificación dedicado.

#### Scenario: Activar desde Edit
- **WHEN** un actor autorizado pone status Active en el tab General y guarda
- **THEN** el producto queda Active sin alterar el tipo

#### Scenario: Tab Imágenes presente
- **WHEN** se abre la edición de un producto
- **THEN** existe el tab Imágenes operable además del tab General

#### Scenario: Tab Documentos presente
- **WHEN** se abre la edición de un producto
- **THEN** existe el tab Documentos operable tras Imágenes

#### Scenario: Guardar clasificación en General
- **WHEN** un actor actualiza familia/categorías/tags en General y guarda
- **THEN** los vínculos quedan persistidos sin cambiar el tipo del
  producto

### Requirement: Listado y rutas /products
El listado SHALL estar en `GET /products` y el resto del recurso CRUD bajo
ese prefijo (create/edit/update/destroy y endpoint de tabla). Las rutas
SHALL exigir Module `products` activo y permisos `catalogue.*` (+ comodín
`products.access` según kernel).

#### Scenario: Acceso al Index
- **WHEN** un actor autorizado navega a `/products`
- **THEN** obtiene el listado ServerTable de productos de su company

### Requirement: Soft delete de Product
El sistema SHALL permitir eliminar un producto con soft delete cuando el
actor tenga permiso `catalogue.delete` y el registro pertenezca a su
company.

#### Scenario: Destroy soft
- **WHEN** un actor autorizado elimina un producto propio
- **THEN** el registro queda soft-deleted y deja de aparecer en el Index
  por defecto

### Requirement: Index con filtros avanzados y presets
El Index de productos SHALL montar el kit de filtros avanzados
(toolbar + modal + presets) con campos al menos para nombre, tipo,
estado, familia, categoría y etiqueta, usando el canal `filter` en modo
`merge`. El endpoint table SHALL aceptar esos campos en la whitelist
filterable. Un actor autorizado SHALL poder limpiar, aplicar y
guardar/cargar presets según `table-filter-presets`.

#### Scenario: Products abre filtros
- **WHEN** un actor autorizado abre el Index de productos y el modal de
  filtros avanzados
- **THEN** ve los campos declarados y puede limpiar, aplicar y gestionar
  presets

#### Scenario: Filtro por taxonomía en table
- **WHEN** el actor aplica filtros avanzados de familia, categoría o
  etiqueta
- **THEN** el listado remoto solo incluye productos que cumplan esos
  filtros dentro de la company de sesión
