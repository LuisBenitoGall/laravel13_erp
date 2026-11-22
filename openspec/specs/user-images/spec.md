## Purpose

Imágenes del User en la ficha: upload con límites, grid/galería, featured
como avatar, flag public condicionado a is_public, storage en disco privado
con entrega autenticada.

## Requirements

### Requirement: Schema user_images y modelo UserImage
El sistema SHALL persistir `user_images` según la migration aportada (soft
deletes, unique user+image+deleted_at, un featured por user vía columna
generada). Los ficheros SHALL guardarse en disco privado (no público
anónimo) bajo `users/{id}/images/…`. La URL expuesta al cliente SHALL ser
la ruta autenticada de entrega, no una URL estática de disco público.

#### Scenario: Alta de imagen
- **WHEN** un actor autorizado sube una imagen válida
- **THEN** se crea `UserImage` con path en storage privado y
  `featured`/`public` false por defecto

#### Scenario: Un solo featured
- **WHEN** se marca una imagen como featured
- **THEN** queda como única featured de ese user

#### Scenario: URL no es estática pública
- **WHEN** el sistema expone la URL de una imagen al cliente (grid, avatar,
  lightbox)
- **THEN** esa URL no es un path anónimo bajo disco público (`/storage/…`)

### Requirement: Entrega autenticada de imágenes de usuario
El sistema SHALL servir el binario de cada `UserImage` solo mediante rutas
de la aplicación autenticadas. La authz SHALL ser equivalente a la de la
ficha del user en ese contexto (self en perfil; `company-users` + perímetro
en Usuarios empresa). SHALL NOT exponer el fichero por URL estática anónima
de disco público.

#### Scenario: Sin login no descarga imagen
- **WHEN** un cliente no autenticado solicita la URL de entrega de una
  imagen de user
- **THEN** no obtiene el binario (401/403/404 o redirección a login; no 200
  anónimo del fichero)

#### Scenario: Actor autorizado obtiene binario
- **WHEN** un actor autenticado autorizado solicita la URL de entrega de
  una imagen de ese user
- **THEN** recibe el binario de la imagen

### Requirement: Tab Imágenes en ficha de usuario
El Edit SHALL incluir el tab Imágenes tras Emails y teléfonos, con Dropzone,
grid/galería, eliminar, marcar featured y toggle public. Authz: self o
`company-users.update` + perímetro.

#### Scenario: Listado y galería
- **WHEN** el user tiene imágenes
- **THEN** se muestran en grid con eliminar, featured, public y preview
  lightbox

#### Scenario: Validación formato y peso
- **WHEN** se sube un fichero fuera de jpg/jpeg/png/gif/webp o mayor de 1 MB
- **THEN** se rechaza con error de validación

#### Scenario: Límite 20
- **WHEN** la subida o el total del user superaría 20 imágenes
- **THEN** se rechaza la operación

#### Scenario: Ajeno sin permiso
- **WHEN** un actor no autorizado fuerza mutaciones de imágenes
- **THEN** recibe 403 o rechazo de perímetro

### Requirement: Visibilidad pública de imágenes
Una imagen SHALL exponerse en contextos públicos solo si `public=true` y el
User tiene `is_public=true`. El avatar de ficha/sesión del actor no cuenta
como contexto público.

#### Scenario: Perfil no público
- **WHEN** `user.is_public` es false
- **THEN** ninguna imagen se considera públicamente visible aunque
  `public=true`

#### Scenario: Imagen no public
- **WHEN** `public=false` y el perfil es público
- **THEN** esa imagen no se expone públicamente

### Requirement: Featured como avatar
La imagen featured SHALL usarse como avatar en el readout de la ficha y en
el topbar/menú del usuario en sesión. Sin featured, SHALL usarse el
placeholder (icono/iniciales).

#### Scenario: Marcar featured
- **WHEN** el actor marca una imagen como featured
- **THEN** readout (y topbar si es self) reflejan esa imagen como avatar
