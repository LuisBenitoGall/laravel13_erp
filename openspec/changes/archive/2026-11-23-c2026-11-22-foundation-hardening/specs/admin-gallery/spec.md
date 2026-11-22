# Spec Delta

## ADDED Requirements

### Requirement: Entrega autenticada de MediaAsset
Los ficheros de galería SHALL servirse solo mediante rutas de la aplicación
con autenticación y autorización de Galería / perímetro. SHALL NOT ser
descargables de forma anónima vía URL estática de disco público.

#### Scenario: Sin login no descarga media de galería
- **WHEN** un cliente no autenticado solicita la URL de entrega de un
  MediaAsset
- **THEN** no obtiene el binario (401/403/404 o redirección a login; no 200
  anónimo del fichero)

#### Scenario: Actor autorizado obtiene binario
- **WHEN** un actor autenticado autorizado solicita la URL de entrega de un
  MediaAsset visible en su perímetro
- **THEN** recibe el binario

## MODIFIED Requirements

### Requirement: Schema MediaAsset
El sistema SHALL persistir `media_assets` con `company_id` nullable, `name`,
`alt`, path de fichero en disco privado (no público anónimo), metadatos de
imagen útiles (mime/bytes/dimensiones si se capturan), soft deletes, y
SHALL NOT usar `parent_id`. SHALL aceptar solo tipos imagen configurados
(jpg/jpeg/png/gif/webp) con límite de tamaño en config.

#### Scenario: Persistencia plataforma
- **WHEN** se crea un MediaAsset de plataforma
- **THEN** queda con `company_id` null y fichero bajo disco privado

### Requirement: Alta SA company_id null
El sistema SHALL permitir crear MediaAsset con `company_id` null vía upload
de imagen válida más name y alt. SHALL NOT permitir en Fase 1 el alta con
`company_id` de empresa (bloqueado). La URL copiable SHALL ser la de
entrega autenticada (no URL estática anónima de disco público).

#### Scenario: Subida válida
- **WHEN** un actor autorizado sube imagen válida con name y alt
- **THEN** se crea el activo null y obtiene URL copiable de entrega
  autenticada

#### Scenario: Alta con company bloqueada
- **WHEN** en Fase 1 se solicita create con `company_id` no null
- **THEN** el sistema rechaza el alta

### Requirement: Listado JSON para selector Wysiwyg
El sistema SHALL exponer un endpoint JSON de activos MediaAsset visibles
al actor (mismo perímetro que el Index de Galería) pensado para alimentar
el selector embebido del Wysiwyg. SHALL exigir autorización de listado de
Galería (`viewAny` / `gallery.view`). Cada ítem SHALL incluir al menos
identificador, name, alt y URL de entrega autenticada. SHALL aceptar filtro
opcional de texto sobre name/alt y paginación por página con metadatos
(`current_page`, `last_page`, `per_page`, `total`). SHALL NOT obligar a
devolver todos los activos visibles en una sola respuesta.

#### Scenario: Actor autorizado obtiene lista
- **WHEN** un actor con permiso de ver Galería solicita el listado del
  selector
- **THEN** recibe JSON con activos de la página pedida, sus URL de entrega
  autenticada y meta de paginación

#### Scenario: Primera página con búsqueda
- **WHEN** el actor autorizado pide el picker con un texto de búsqueda y
  página 1
- **THEN** recibe solo activos coincidentes de esa página y meta de
  paginación

#### Scenario: Sin permiso
- **WHEN** un actor sin permiso de ver Galería solicita el listado del
  selector
- **THEN** recibe denegación (403)
