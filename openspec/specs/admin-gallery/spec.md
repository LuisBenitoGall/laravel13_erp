## Purpose

Librería de imágenes reutilizables (`MediaAsset`) bajo Configuración: subida,
metadatos name/alt, URL pública copiable y gestión en grid, con `company_id`
nullable y upload de empresa bloqueado en Fase 1; crop/resize como copia
(Fase 1b).

## Requirements

### Requirement: Schema MediaAsset
El sistema SHALL persistir `media_assets` con `company_id` nullable, `name`,
`alt`, path de fichero en disco `public`, metadatos de imagen útiles
(mime/bytes/dimensiones si se capturan), soft deletes, y SHALL NOT usar
`parent_id`. SHALL aceptar solo tipos imagen configurados (jpg/jpeg/png/gif/webp)
con límite de tamaño en config.

#### Scenario: Persistencia plataforma
- **WHEN** se crea un MediaAsset de plataforma
- **THEN** queda con `company_id` null y fichero bajo el disco public

### Requirement: Index Galería grid 6 columnas
El sistema SHALL exponer Index en `/admin/gallery` con layout grid de 6
columnas (excepción ServerTable) listando activos visibles al actor, con
miniatura, name, copiar URL, editar name/alt, eliminar y, si el actor puede
crear, acción para recortar/redimensionar (copia).

#### Scenario: SA ve grid
- **WHEN** Super Admin abre Galería
- **THEN** ve el grid de 6 columnas de imágenes

#### Scenario: Card con acción crop
- **WHEN** el actor tiene permiso de create en Galería
- **THEN** cada card expone la acción de recortar/redimensionar

### Requirement: Alta SA company_id null
El sistema SHALL permitir crear MediaAsset con `company_id` null vía upload
de imagen válida más name y alt. SHALL NOT permitir en Fase 1 el alta con
`company_id` de empresa (bloqueado).

#### Scenario: Subida válida
- **WHEN** un actor autorizado sube imagen válida con name y alt
- **THEN** se crea el activo null y obtiene URL copiable

#### Scenario: Alta con company bloqueada
- **WHEN** en Fase 1 se solicita create con `company_id` no null
- **THEN** el sistema rechaza el alta

### Requirement: Visibilidad y perímetro
Los activos con `company_id` null SHALL ser listables por Super Admin o por
actores con permiso de Galería (`gallery.view` / viewAny). Los activos con
`company_id` set SHALL quedar acotados al perímetro de companies del actor
(SA incluido: solo vinculadas). SHALL NOT filtrar con
`session('currentCompany')`.

#### Scenario: Aislamiento company B
- **WHEN** un actor solo vinculado a company A intenta mutar un MediaAsset
  de company B
- **THEN** recibe denegación (403) y no altera el registro

### Requirement: Update name/alt y delete
El sistema SHALL permitir actualizar `name` y `alt` y soft-delete del
MediaAsset a actores autorizados dentro de perímetro, invalidando el uso
futuro del path según la política de borrado de fichero del design.

#### Scenario: Editar alt
- **WHEN** el actor autorizado cambia el alt y guarda
- **THEN** el valor queda persistido

#### Scenario: Eliminar
- **WHEN** el actor autorizado elimina un activo
- **THEN** deja de listarse en el Index (soft delete)

### Requirement: Recortar/redimensionar como copia
El sistema SHALL permitir, desde un MediaAsset visible en el Index de
Galería, abrir un flujo de recorte/redimensión en cliente y, al confirmar,
crear un nuevo MediaAsset con el fichero resultante. SHALL NOT modificar el
fichero ni los metadatos del original en esa operación. SHALL NOT persistir
`parent_id` ni linaje.

#### Scenario: Guardar copia recortada
- **WHEN** el actor autoriza el recorte de un activo y confirma guardar
- **THEN** existe un nuevo MediaAsset con URL propia y el original
  sigue listado e intacto

### Requirement: Herencia de name y alt en copia por crop
La copia creada por recorte/redimensión SHALL inicializar `name` y `alt`
con los valores del MediaAsset origen.

#### Scenario: Metadatos de la copia
- **WHEN** se crea la copia desde un activo con name y alt dados
- **THEN** la copia nace con el mismo name y el mismo alt

### Requirement: Ayuda de uso en modal crop
El modal de recorte de Galería SHALL mostrar ayuda i18n que indique que
el actor mueve la imagen y usa el zoom, y que la zona clara es el área
que se guarda (sin redimensionar la rejilla con asas).

#### Scenario: Actor abre recortar
- **WHEN** el actor abre el modal de recorte
- **THEN** ve el texto de ayuda de pan + zoom

### Requirement: Selectores de aspect ratio en modal crop
El modal SHALL permitir elegir los ratios 1:1, 4:3, 16:9 y libre,
aplicando el `aspect` correspondiente en `react-easy-crop` sin cambiar
de librería. El default al abrir SHALL ser 4:3.

#### Scenario: Cambiar a 1:1
- **WHEN** el actor selecciona ratio 1:1
- **THEN** la zona de crop usa aspect 1:1

#### Scenario: Default 4:3
- **WHEN** el actor abre el modal de recorte
- **THEN** el ratio activo es 4:3

### Requirement: Listado JSON para selector Wysiwyg
El sistema SHALL exponer un endpoint JSON de activos MediaAsset visibles
al actor (mismo perímetro que el Index de Galería) pensado para alimentar
el selector embebido del Wysiwyg. SHALL exigir autorización de listado de
Galería (`viewAny` / `gallery.view`). Cada ítem SHALL incluir al menos
identificador, name, alt y URL pública. SHALL aceptar filtro opcional de
texto sobre name/alt y paginación por página con metadatos
(`current_page`, `last_page`, `per_page`, `total`). SHALL NOT obligar a
devolver todos los activos visibles en una sola respuesta.

#### Scenario: Actor autorizado obtiene lista
- **WHEN** un actor con permiso de ver Galería solicita el listado del
  selector
- **THEN** recibe JSON con activos de la página pedida, sus URL y meta
  de paginación

#### Scenario: Primera página con búsqueda
- **WHEN** el actor autorizado pide el picker con un texto de búsqueda y
  página 1
- **THEN** recibe solo activos coincidentes de esa página y meta de
  paginación

#### Scenario: Sin permiso
- **WHEN** un actor sin permiso de ver Galería solicita el listado del
  selector
- **THEN** recibe denegación (403)
