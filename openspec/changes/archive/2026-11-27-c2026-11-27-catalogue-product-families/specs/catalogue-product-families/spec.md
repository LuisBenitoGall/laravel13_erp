# Spec Delta

## Purpose

Maestro Family del catálogo por Company: nombre llano, slug automático,
status, CRUD ServerTable y menú Familias bajo Catálogo, sin vincular aún
a Product.

## ADDED Requirements

### Requirement: Family por Company
El sistema SHALL persistir `families` con `company_id`, nombre llano,
slug, status Active/Inactive y soft deletes. Listados y mutaciones SHALL
estar aislados por tenant (R-TEN-05). SHALL existir unicidad de slug por
company entre registros activos.

#### Scenario: Aislamiento por company
- **WHEN** un actor de la company A lista Familias
- **THEN** solo ve familias de A (no las de otra company)

#### Scenario: Alta con slug auto
- **WHEN** un actor autorizado crea una familia con un nombre válido
- **THEN** queda persistida con slug generado, status según el formulario
  (default Inactive) y `company_id` de sesión

### Requirement: CRUD Family
El sistema SHALL exponer Index (`ServerTable`), Create y Edit de Family,
y soft delete cuando el actor tenga `families.delete` y el registro sea
de su company. Authz vía permisos `families.*` y perímetro company.

#### Scenario: Acceso al Index
- **WHEN** un actor autorizado navega a `/products/families`
- **THEN** obtiene el ServerTable de familias de su company

#### Scenario: Update no exige traducciones
- **WHEN** un actor actualiza nombre y/o status
- **THEN** no se exigen campos i18n JSON

#### Scenario: Ajeno o sin permiso
- **WHEN** un actor no autorizado o de otra company fuerza mutaciones
- **THEN** recibe 403 o rechazo de perímetro

### Requirement: Functionality families y menú
El sistema SHALL registrar Functionality slug `families` bajo Module
`products`, con permisos `families.{view,viewAny,create,update,delete}`,
y un ítem de menú Familias bajo el grupo Catálogo, visible con capas del
módulo satisfechas y `families.view`.

#### Scenario: Entrada de menú Familias
- **WHEN** un actor con Module `products` disponible y `families.view`
  obtiene el menú
- **THEN** ve Familias bajo Catálogo apuntando al listado de families

### Requirement: Sin vínculo a Product
Este change SHALL NOT añadir relación Product↔Family ni UI de familia en
la ficha Edit de Product.

#### Scenario: Product Edit sin familia
- **WHEN** se complete este change
- **THEN** la ficha Edit de Product no muestra selector de familia
