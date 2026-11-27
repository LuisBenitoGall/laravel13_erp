# Spec Delta

## MODIFIED Requirements

### Requirement: Module products Optional
El sistema SHALL exponer el Module con slug `products`, level Optional y
status Active en catálogo global, con Functionalities hijas `catalogue`,
`families`, `categories` y `tags`.

#### Scenario: Module en catálogo
- **WHEN** un Super Admin consulta el catálogo de módulos
- **THEN** existe Module `products` Optional Active con Functionalities
  `catalogue`, `families`, `categories` y `tags`

### Requirement: Functionality catalogue y families
El sistema SHALL registrar permisos Spatie `catalogue.view`,
`catalogue.viewAny`, `catalogue.create`, `catalogue.update`,
`catalogue.delete`, `families.view`, `families.viewAny`, `families.create`,
`families.update`, `families.delete`, `categories.view`,
`categories.viewAny`, `categories.create`, `categories.update`,
`categories.delete`, `tags.view`, `tags.viewAny`, `tags.create`,
`tags.update`, `tags.delete`, más el comodín de módulo `products.access`.

#### Scenario: Permisos sembrados
- **WHEN** se ejecuta el seed/realign de menú y módulos
- **THEN** existen los permisos `catalogue.*`, `families.*`,
  `categories.*`, `tags.*` y `products.access`

### Requirement: Menú Catálogo
El sistema SHALL mostrar un grupo de menú «Catálogo» con ítems «Artículos»
(`catalogue`, ruta `/products`), «Familias» (`families`, ruta
`/products/families`), «Categorías» (`categories`, ruta
`/products/categories`) y «Etiquetas» (`tags`, ruta `/products/tags`)
cuando el Module `products` esté Active, autorizado y encendido para la
company, y el usuario tenga el `.view` correspondiente (más el comodín
`products.access` según kernel).

#### Scenario: Ítems visibles con permiso
- **WHEN** un usuario con `catalogue.view`, `families.view`,
  `categories.view` y `tags.view` (módulo on) carga el shell
- **THEN** ve Catálogo → Artículos, Familias, Categorías y Etiquetas

#### Scenario: Sin families.view
- **WHEN** un usuario tiene `catalogue.view` pero no `families.view`
- **THEN** ve Artículos y no ve Familias

#### Scenario: Sin categories.view
- **WHEN** un usuario tiene `catalogue.view` pero no `categories.view`
- **THEN** ve Artículos y no ve Categorías

#### Scenario: Sin tags.view
- **WHEN** un usuario tiene `catalogue.view` pero no `tags.view`
- **THEN** ve Artículos y no ve Etiquetas

### Requirement: Module Inactive bloquea acceso
Si el Module `products` está Inactive en catálogo global, el sistema SHALL
bloquear rutas bajo `/products*` y ocultar el menú Catálogo, con
independencia de autorizaciones o `company_modules`.

#### Scenario: Module Inactive
- **WHEN** Module `products` está Inactive y un usuario autorizado intenta
  `/products`, `/products/families`, `/products/categories` o
  `/products/tags`
- **THEN** el acceso se deniega y el menú Catálogo no aparece
