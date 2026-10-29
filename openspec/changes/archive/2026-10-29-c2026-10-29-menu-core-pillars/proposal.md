## Why

El borrador MENU_ERP orienta el producto, pero no hay que sembrarlo entero. Hace falta
exponer en el menú solo los **pilares** Empresas / Usuarios / Configuración (SA), con
labels claros, reutilizando pantallas existentes (sin CRUDs nuevos).

## What Changes

- Agrupación/labels: Empresas → Mis empresas (perímetro vinculado; solo label).
- Usuarios → Usuarios empresa: entrada de menú sin pantalla nueva; destino temporal a
  Members de la company de sesión (decisión 10; análogo a Permisos→Roles).
- Configuración AdminOnly solo SA: subítems visibles prioritarios Módulos, Roles,
  Permisos; Permisos enlaza a la UI de Roles / matriz (opción C).
- Sin pantallas nuevas; sin catálogo completo del PDF.

## Capabilities

### New Capabilities

- `menu-core-pillars`: labels/agrupación y hrefs temporales de los tres pilares.

### Modified Capabilities

- `shell-navigation`: Configuración SA y agrupación cuenta/empresa alineadas a pilares.
- `menu-visibility`: si hace falta aclarar labels/ítems temporales sin CRUD propio.

## Impact

- `MenuBuilder` (grupos, labels i18n, `hrefFor`, filtro/orden Configuración)
- Seed labels de Functionalities (companies → Mis empresas; alta sintética permisos /
  usuarios-empresa si aplica)
- `lang/*`
- Tests Feature de menú SA / no-SA

## Excepciones a standards

Ninguna. CRUD permisos custom y listado usuarios global = changes futuros (anotados).
