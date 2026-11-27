# Spec Delta

## MODIFIED Requirements

### Requirement: Edit con tabs y tab General
La ficha de edición SHALL usar layout con tabs. SHALL existir el tab de
información genérica donde se editan al menos nombre, status
(`active`|`inactive`) y la clasificación opcional Family 0..1, Categories
0..N y Tags 0..N (incl. alta in situ de tags según
`catalogue-product-classification`). SHALL existir el tab Imágenes
operable según la capability `catalogue-product-images`. SHALL existir el
tab Documentos operable según la capability `catalogue-product-docs`, tras
Imágenes. SHALL NOT añadir un tab Clasificación dedicado.

#### Scenario: Activar desde Edit
- **WHEN** un actor autorizado pone status Active en el tab General y guarda
- **THEN** el producto queda Active sin alterar el tipo

#### Scenario: Tab Imágenes presente
- **WHEN** se abre la edición de un producto
- **THEN** existe el tab Imágenes operable además del tab General

#### Scenario: Tab Documentos presente
- **WHEN** se abre la edición de un producto
- **THEN** existe el tab Documentos operable tras Imágenes

#### Scenario: Guardar clasificación en General
- **WHEN** un actor actualiza familia/categorías/tags en General y guarda
- **THEN** los vínculos quedan persistidos sin cambiar el tipo del
  producto
