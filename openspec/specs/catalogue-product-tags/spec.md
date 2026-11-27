## Purpose

Maestro Tag del catálogo por Company: nombre llano, slug automático,
status, CRUD ServerTable y menú Etiquetas, sin vincular aún a Product ni
usar CSV por locale.

## Requirements

### Requirement: Tag por Company
El sistema SHALL persistir `tags` con `company_id`, nombre llano, slug,
status Active/Inactive y soft deletes. Listados y mutaciones SHALL estar
aislados por tenant (R-TEN-05). SHALL existir unicidad de slug por company
entre registros activos. SHALL NOT exigir traducciones JSON ni modelar
tags como CSV multilocal.

#### Scenario: Aislamiento por company
- **WHEN** un actor de la company A lista Etiquetas
- **THEN** solo ve tags de A

#### Scenario: Alta con slug auto
- **WHEN** un actor autorizado crea un tag con un nombre válido
- **THEN** queda persistido con slug generado, status según formulario
  (default Inactive) y `company_id` de sesión

### Requirement: CRUD Tag
El sistema SHALL exponer Index (`ServerTable`), Create y Edit de Tag, y
soft delete cuando el actor tenga `tags.delete` y el registro sea de su
company. Authz vía permisos `tags.*` y perímetro company.

#### Scenario: Acceso al Index
- **WHEN** un actor autorizado navega a `/products/tags`
- **THEN** obtiene el ServerTable de tags de su company

#### Scenario: Update no exige traducciones
- **WHEN** un actor actualiza nombre y/o status
- **THEN** no se exigen campos i18n JSON

#### Scenario: Ajeno o sin permiso
- **WHEN** un actor no autorizado o de otra company fuerza mutaciones
- **THEN** recibe 403 o rechazo de perímetro

### Requirement: Functionality tags y menú
El sistema SHALL registrar Functionality slug `tags` bajo Module
`products`, permisos `tags.{view,viewAny,create,update,delete}`, e ítem
de menú Etiquetas bajo Catálogo, visible con capas del módulo y
`tags.view`.

#### Scenario: Entrada de menú Etiquetas
- **WHEN** un actor con Module `products` disponible y `tags.view`
  obtiene el menú
- **THEN** ve Etiquetas bajo Catálogo apuntando al listado de tags

### Requirement: Sin vínculo a Product
Este change SHALL NOT añadir pivote Product↔Tag ni UI de tags en la ficha
Edit de Product.

#### Scenario: Product Edit sin tags
- **WHEN** se complete este change
- **THEN** la ficha Edit de Product no muestra selector de tags
