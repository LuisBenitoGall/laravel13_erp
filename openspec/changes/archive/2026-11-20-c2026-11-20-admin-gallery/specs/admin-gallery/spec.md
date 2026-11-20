# Spec Delta: admin-gallery

## Purpose

Librería de imágenes reutilizables (`MediaAsset`) bajo Configuración: subida,
metadatos name/alt, URL pública copiable y gestión en grid, con `company_id`
nullable y upload de empresa bloqueado en Fase 1.

## ADDED Requirements

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
miniatura, name, copiar URL, editar name/alt y eliminar.

#### Scenario: SA ve grid
- **WHEN** Super Admin abre Galería
- **THEN** ve el grid de 6 columnas de imágenes

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
