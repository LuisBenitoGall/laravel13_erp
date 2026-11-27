# Spec Delta

## Purpose

Imágenes adjuntas de Product en la ficha Edit: upload con límites, portada
featured, flag public, disco privado y entrega autenticada para consumo
futuro dentro del ERP.

## ADDED Requirements

### Requirement: Schema product_images y modelo ProductImage
El sistema SHALL persistir `product_images` con `product_id`, `company_id`,
path de fichero, `featured`, `public` y soft deletes. Los ficheros SHALL
guardarse en disco privado bajo un path por producto. La URL expuesta al
cliente SHALL ser la ruta autenticada de entrega, no una URL estática de
disco público. SHALL existir como máximo una imagen featured activa por
producto.

#### Scenario: Alta de imagen
- **WHEN** un actor autorizado sube una imagen válida a un producto de su
  company
- **THEN** se crea `ProductImage` con el `company_id` del producto, path en
  storage privado y `featured`/`public` en false por defecto

#### Scenario: Un solo featured
- **WHEN** se marca una imagen como featured
- **THEN** queda como única featured de ese producto

#### Scenario: URL no es estática pública
- **WHEN** el sistema expone la URL de una imagen al cliente (grid, lightbox)
- **THEN** esa URL no es un path anónimo bajo disco público (`/storage/…`)

### Requirement: Tab Imágenes en ficha Edit de Product
La ficha Edit de Product SHALL incluir el tab Imágenes para productos
físicos y de servicio, con Dropzone, grid/lightbox, eliminar, marcar
featured y toggle `public`. Authz de gestión: mismos permisos Spatie que
actualizan el Product (sin Functionality nueva). En Edit SHALL listarse
todas las imágenes del producto.

#### Scenario: Listado en Edit
- **WHEN** el producto tiene imágenes
- **THEN** se muestran en grid con eliminar, featured, public y preview

#### Scenario: Validación formato y peso
- **WHEN** se sube un fichero fuera de jpg/jpeg/png/gif/webp o mayor de 1 MB
- **THEN** se rechaza con error de validación

#### Scenario: Límite 20
- **WHEN** la subida o el total del producto superaría 20 imágenes
- **THEN** se rechaza la operación

#### Scenario: Ajeno sin permiso o fuera de company
- **WHEN** un actor no autorizado o de otra company fuerza mutaciones
- **THEN** recibe 403 o rechazo de perímetro

### Requirement: Entrega autenticada de imágenes de producto
El sistema SHALL servir el binario solo mediante rutas autenticadas. Un
actor autorizado a actualizar el Product en su company SHALL poder obtener
cualquier imagen de ese producto (contexto gestión/Edit). SHALL NOT
exponer el fichero por URL estática anónima.

#### Scenario: Sin login no obtiene binario
- **WHEN** un cliente no autenticado solicita la URL de entrega
- **THEN** no obtiene el binario (401/403/404 o redirección a login)

#### Scenario: Editor obtiene no-pública
- **WHEN** un actor autorizado a actualizar el producto solicita el binario
  de una imagen `public=false`
- **THEN** recibe el binario

### Requirement: Elegibilidad public fuera de gestión de ficha
Fuera del contexto de gestión de la ficha Edit, una imagen SHALL ser
elegible para mostrarse en otros contextos del ERP solo si
`public=true` **y** el Product tiene status Active. Este change SHALL
definir y testear esa regla de elegibilidad; SHALL NOT montar Index thumbs
ni la vista catálogo proveedor→cliente.

#### Scenario: Inactive no elegible fuera de Edit
- **WHEN** el producto está Inactive aunque la imagen tenga `public=true`
- **THEN** la imagen no es elegible para contextos distintos de la gestión
  de ficha

#### Scenario: No public no elegible fuera de Edit
- **WHEN** el producto está Active y la imagen tiene `public=false`
- **THEN** la imagen no es elegible para contextos distintos de la gestión
  de ficha

#### Scenario: Active y public sí elegible
- **WHEN** el producto está Active y la imagen tiene `public=true`
- **THEN** la imagen es elegible para esos contextos futuros

### Requirement: Solo upload (sin Galería)
El origen de las imágenes de producto SHALL ser upload directo. SHALL NOT
usar `MediaAsset` / `MediaPicker` como fuente en este change.

#### Scenario: Sin picker de galería
- **WHEN** el actor usa el tab Imágenes
- **THEN** solo dispone de Dropzone de upload (no selector de Galería)

### Requirement: Sin thumbs en Index en este change
Este change SHALL NOT exigir mostrar la imagen featured en el Index de
productos. SHALL dejar modelo y entrega listos para un change posterior.

#### Scenario: Index sin thumb obligatorio
- **WHEN** se complete este change
- **THEN** el Index de productos no requiere mostrar aún la imagen featured
