## Purpose

Module SaaS `products` (Optional) con Functionalities `catalogue` (Artículos),
`families` (Familias) y `categories` (Categorías), menú grupo Catálogo, y
permisos Spatie `catalogue.*` / `families.*` / `categories.*` + comodín
`products.access`.

## Requirements

### Requirement: Module products Optional
El sistema SHALL exponer el Module con slug `products`, level Optional y
status Active en catálogo global, con Functionalities hijas `catalogue`,
`families` y `categories`.

#### Scenario: Module en catálogo
- **WHEN** un Super Admin consulta el catálogo de módulos
- **THEN** existe Module `products` Optional Active con Functionalities
  `catalogue`, `families` y `categories`

### Requirement: Functionality catalogue y families
El sistema SHALL registrar permisos Spatie `catalogue.view`,
`catalogue.viewAny`, `catalogue.create`, `catalogue.update`,
`catalogue.delete`, `families.view`, `families.viewAny`, `families.create`,
`families.update`, `families.delete`, `categories.view`,
`categories.viewAny`, `categories.create`, `categories.update`,
`categories.delete`, más el comodín de módulo `products.access`.

#### Scenario: Permisos sembrados
- **WHEN** se ejecuta el seed/realign de menú y módulos
- **THEN** existen los permisos `catalogue.*`, `families.*`,
  `categories.*` y `products.access`

### Requirement: Menú Catálogo
El sistema SHALL mostrar un grupo de menú «Catálogo» con ítems «Artículos»
(`catalogue`, ruta `/products`), «Familias» (`families`, ruta
`/products/families`) y «Categorías» (`categories`, ruta
`/products/categories`) cuando el Module `products` esté Active,
autorizado y encendido para la company, y el usuario tenga el `.view`
correspondiente (más el comodín `products.access` según kernel).

#### Scenario: Ítems visibles con permiso
- **WHEN** un usuario con `catalogue.view`, `families.view` y
  `categories.view` (módulo on) carga el shell
- **THEN** ve Catálogo → Artículos, Familias y Categorías

#### Scenario: Sin families.view
- **WHEN** un usuario tiene `catalogue.view` pero no `families.view`
- **THEN** ve Artículos y no ve Familias

#### Scenario: Sin categories.view
- **WHEN** un usuario tiene `catalogue.view` pero no `categories.view`
- **THEN** ve Artículos y no ve Categorías

### Requirement: Module Inactive bloquea acceso
Si el Module `products` está Inactive en catálogo global, el sistema SHALL
bloquear rutas bajo `/products*` y ocultar el menú Catálogo, con
independencia de autorizaciones o `company_modules`.

#### Scenario: Module Inactive
- **WHEN** Module `products` está Inactive y un usuario autorizado intenta
  `/products`, `/products/families` o `/products/categories`
- **THEN** el acceso se deniega y el menú Catálogo no aparece
