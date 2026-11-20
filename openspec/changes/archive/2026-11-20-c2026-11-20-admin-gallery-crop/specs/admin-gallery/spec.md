# Spec Delta: admin-gallery

## ADDED Requirements

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

## MODIFIED Requirements

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
