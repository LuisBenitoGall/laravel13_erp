## Purpose

Documentos adjuntos de Product en la ficha Edit: upload con límites, flag
public (p. ej. fichas técnicas), disco privado y descarga autenticada para
consumo futuro dentro del ERP.

## Requirements

### Requirement: Schema product_docs y modelo ProductDoc
El sistema SHALL persistir `product_docs` con `product_id`, `company_id`,
path, nombre original, mime, tamaño en bytes, `public` y soft deletes. Los
ficheros SHALL guardarse en disco privado bajo un path por producto. La
URL de descarga SHALL ser autenticada, no estática de disco público. SHALL
NOT incluir `featured`.

#### Scenario: Alta de documento
- **WHEN** un actor autorizado sube un fichero válido a un producto de su
  company
- **THEN** se crea `ProductDoc` con el `company_id` del producto, path en
  storage privado, metadatos y `public` false por defecto

#### Scenario: URL no es estática pública
- **WHEN** el sistema expone la URL de descarga al cliente
- **THEN** esa URL no es un path anónimo bajo disco público (`/storage/…`)

### Requirement: Tab Documentos en ficha Edit de Product
La ficha Edit de Product SHALL incluir el tab Documentos (físico y
servicio), tras Imágenes, con Dropzone, listado, descarga, eliminar y
toggle `public`. Authz de gestión: mismos permisos Spatie que actualizan
el Product (sin Functionality nueva). En Edit SHALL listarse todos los
documentos del producto.

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

### Requirement: Descarga autenticada de documentos de producto
El sistema SHALL servir el fichero solo mediante rutas autenticadas. Un
actor autorizado a actualizar el Product en su company SHALL poder
descargar cualquier documento de ese producto (contexto gestión/Edit).
SHALL NOT exponer URL pública permanente del fichero.

#### Scenario: Sin login no descarga
- **WHEN** un cliente no autenticado solicita la URL de descarga
- **THEN** no obtiene el binario (401/403/404 o redirección a login)

#### Scenario: Editor descarga no-público
- **WHEN** un actor autorizado a actualizar el producto descarga un
  documento `public=false`
- **THEN** recibe el fichero

### Requirement: Elegibilidad public fuera de gestión de ficha
Fuera del contexto de gestión de la ficha Edit, un documento SHALL ser
elegible solo si `public=true` **y** el Product tiene status Active. Este
change SHALL definir y testear esa regla; SHALL NOT montar Index ni la
vista catálogo proveedor→cliente.

#### Scenario: Inactive no elegible fuera de Edit
- **WHEN** el producto está Inactive aunque el documento tenga `public=true`
- **THEN** el documento no es elegible para contextos distintos de la
  gestión de ficha

#### Scenario: No public no elegible fuera de Edit
- **WHEN** el producto está Active y el documento tiene `public=false`
- **THEN** el documento no es elegible para contextos distintos de la
  gestión de ficha

#### Scenario: Active y public sí elegible
- **WHEN** el producto está Active y el documento tiene `public=true`
- **THEN** el documento es elegible para esos contextos futuros

### Requirement: Solo upload (sin Galería)
El origen de los documentos de producto SHALL ser upload directo. SHALL
NOT usar `MediaAsset` / `MediaPicker` como fuente en este change.

#### Scenario: Sin picker de galería
- **WHEN** el actor usa el tab Documentos
- **THEN** solo dispone de Dropzone de upload
