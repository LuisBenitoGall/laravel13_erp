## Purpose

Maestro Category del catálogo por Company: nombre llano, slug automático,
status, árbol N niveles (`parent_id`), CRUD y menú Categorías, sin
vincular aún a Product ni a Family.

## Requirements

### Requirement: Category por Company con árbol N
El sistema SHALL persistir `categories` con `company_id`, nombre llano,
slug, status Active/Inactive, `parent_id` nullable (árbol de N niveles) y
soft deletes. Listados y mutaciones SHALL estar aislados por tenant
(R-TEN-05). SHALL existir unicidad de slug por company entre registros
activos. SHALL NOT exigir traducciones JSON.

#### Scenario: Aislamiento por company
- **WHEN** un actor de la company A lista Categorías
- **THEN** solo ve categorías de A

#### Scenario: Alta raíz
- **WHEN** un actor autorizado crea una categoría sin padre
- **THEN** queda como raíz con slug auto y status según formulario
  (default Inactive)

#### Scenario: Alta hija
- **WHEN** un actor autorizado crea una categoría con padre válido de la
  misma company
- **THEN** queda como hija de ese padre (profundidad N permitida)

#### Scenario: Rechazo de ciclo
- **WHEN** un actor intenta asignar como padre el propio nodo o un
  descendiente suyo
- **THEN** la operación se rechaza con error de validación

### Requirement: CRUD Category y delete con hijos
El sistema SHALL exponer Index (`ServerTable`), Create y Edit de Category.
Soft delete SHALL requerir `categories.delete` y perímetro company. SHALL
NOT soft-delete una categoría que aún tenga hijos no borrados.

#### Scenario: Acceso al Index
- **WHEN** un actor autorizado navega a `/products/categories`
- **THEN** obtiene el ServerTable de categorías de su company

#### Scenario: Delete bloqueado con hijos
- **WHEN** un actor intenta eliminar una categoría con hijos activos
- **THEN** la operación se rechaza y los hijos permanecen

#### Scenario: Delete hoja
- **WHEN** un actor autorizado elimina una categoría sin hijos
- **THEN** el registro queda soft-deleted

### Requirement: Functionality categories y menú
El sistema SHALL registrar Functionality slug `categories` bajo Module
`products`, permisos `categories.{view,viewAny,create,update,delete}`, e
ítem de menú Categorías bajo Catálogo, visible con capas del módulo y
`categories.view`.

#### Scenario: Entrada de menú Categorías
- **WHEN** un actor con Module `products` disponible y `categories.view`
  obtiene el menú
- **THEN** ve Categorías bajo Catálogo apuntando al listado de categories

### Requirement: Sin vínculo a Product ni Family
Este change SHALL NOT añadir pivote Product↔Category, ni Family↔Category,
ni UI de clasificación en la ficha Edit de Product.

#### Scenario: Product Edit sin categorías
- **WHEN** se complete este change
- **THEN** la ficha Edit de Product no muestra selector de categorías
