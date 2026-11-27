# Spec Delta

## MODIFIED Requirements

### Requirement: Edit con tabs y tab General
La ficha de edición SHALL usar layout con tabs. SHALL existir el tab de
información genérica donde se editan al menos nombre y status
(`active`|`inactive`). SHALL existir además el tab Imágenes operable
según la capability `catalogue-product-images`. SHALL NOT añadir tab
Documentos ni tabs stub de otras fases futuras en este change.

#### Scenario: Activar desde Edit
- **WHEN** un actor autorizado pone status Active en el tab General y guarda
- **THEN** el producto queda Active sin alterar el tipo

#### Scenario: Tab Imágenes presente
- **WHEN** se abre la edición de un producto
- **THEN** existe el tab Imágenes operable además del tab General
