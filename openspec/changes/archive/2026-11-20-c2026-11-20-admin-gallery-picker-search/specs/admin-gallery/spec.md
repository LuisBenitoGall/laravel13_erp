# Spec Delta

## MODIFIED Requirements

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
