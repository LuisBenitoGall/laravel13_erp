# Spec Delta

## MODIFIED Requirements

### Requirement: Menú Catálogo
El sistema SHALL exponer el Module `products` en el menú como grupo
**Catálogo** (label UI) con al menos dos subítems: **Artículos**
(Functionality `catalogue` → listado de productos) y **Familias**
(Functionality `families` → listado de families). La visibilidad de cada
subítem SHALL exigir capas del Module `products` satisfechas y el
permiso `.view` de su Functionality. SHALL NOT permanecer como una única
hoja solo al listado de productos cuando exista Familias.

#### Scenario: Entrada de menú
- **WHEN** un actor con `products` disponible y `catalogue.view` obtiene
  el menú
- **THEN** ve Artículos bajo Catálogo apuntando al listado de productos

#### Scenario: Catálogo agrupa Familias
- **WHEN** el actor autorizado abre el menú Catálogo y tiene
  `families.view`
- **THEN** puede ir a Familias además del listado de Artículos

#### Scenario: Sin permiso Familias
- **WHEN** el actor tiene acceso a Artículos pero carece de
  `families.view`
- **THEN** no ve el subítem Familias (o recibe 403 al forzar la URL)
