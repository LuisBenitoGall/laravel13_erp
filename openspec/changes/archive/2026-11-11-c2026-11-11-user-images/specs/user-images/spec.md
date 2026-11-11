# Spec Delta

## Purpose

Imágenes del User en la ficha: upload con límites, grid/galería, featured
como avatar, flag public condicionado a is_public, storage en disco public.

## ADDED Requirements

### Requirement: Schema user_images y modelo UserImage
El sistema SHALL persistir `user_images` según la migration aportada (soft
deletes, unique user+image+deleted_at, un featured por user vía columna
generada). Los ficheros SHALL guardarse en disco `public` bajo
`users/{id}/images/…`.

#### Scenario: Alta de imagen
- **WHEN** un actor autorizado sube una imagen válida
- **THEN** se crea `UserImage` con path en storage y `featured`/`public`
  false por defecto

#### Scenario: Un solo featured
- **WHEN** se marca una imagen como featured
- **THEN** queda como única featured de ese user

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
