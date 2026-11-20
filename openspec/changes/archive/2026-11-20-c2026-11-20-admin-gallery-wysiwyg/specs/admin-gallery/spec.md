# Spec Delta

## ADDED Requirements

### Requirement: Listado JSON para selector Wysiwyg
El sistema SHALL exponer un endpoint JSON de activos MediaAsset visibles
al actor (mismo perímetro que el Index de Galería) pensado para alimentar
el selector embebido del Wysiwyg. SHALL exigir autorización de listado de
Galería (`viewAny` / `gallery.view`). Cada ítem SHALL incluir al menos
identificador, name, alt y URL pública.

#### Scenario: Actor autorizado obtiene lista
- **WHEN** un actor con permiso de ver Galería solicita el listado del
  selector
- **THEN** recibe JSON con los activos visibles y sus URL

#### Scenario: Sin permiso
- **WHEN** un actor sin permiso de ver Galería solicita el listado del
  selector
- **THEN** recibe denegación (403)
