# Proposal

## Why

El menú mezcla Modules reales con pilares hardcode (`dashboard`, `empresas`,
`configuracion`) y Functionalities bajo slugs `admin`/`workspace` que no
coinciden con el mapa canónico (PDF MENU_ERP). Hay que alinear catálogo +
builder + permisos: raíces solo desde `modules`, fusión de slugs, y regla AND
módulo∩funcionalidad (corrige el OR del kernel actual).

## What Changes

- Sembrar/migrar Modules mínimos: `dashboard`, `cuenta`, `usuarios`, `empresas`,
  `configuracion`; retirar `admin` y `workspace`.
- Reasignar Functionalities existentes a esos Modules; menú 100 % data-driven.
- Dashboard = Module hoja clicable (sin Functionalities).
- Quitar hardcode MenuBuilder (`CONFIGURATION_SLUGS`, bloques sintéticos,
  `FUNCTIONALITY_GROUPS`).
- Rutas `/admin/...` planas; middleware `module:{slug}` actualizado.
- **BREAKING (authz):** permiso `{module}.access` **AND** permiso de la
  Functionality; actualizar `kernel.md` (+ adaptadores) y tests.
- Cache del catálogo menú (Laravel Cache + invalidación en Actions).
- Specs `menu-visibility` / `menu-core-pillars` al nuevo contrato.

## Capabilities

### New Capabilities

- (ninguna — remodela menú/visibilidad existentes)

### Modified Capabilities

- `menu-visibility`: AND Spatie; raíces desde Modules; Dashboard hoja; cache;
  excepción shell Configuración SA sin hardcode de raíz.
- `menu-core-pillars`: pilares = Modules `empresas`/`usuarios`/`configuracion`/
  `dashboard`/`cuenta`; ítems Configuración bajo Module `configuracion`.

## Impact

- `MenuBuilder`, seeders AdminCatalog, middleware `module:*`, EnsureModuleIsActive
  (slugs), policies/menú tests, possibly sync manager comodines.
- `docs/standards/kernel.md` (+ `.cursor/rules/01-kernel.mdc` si duplica OR).
- Front aside: soporte Module con `href` sin hijos.
- E2E: N/A salvo que 10 marque flujo e2e.md §4 — no aplica (feature tests).
- Sin sembrar Optional del PDF; sin pantallas nuevas.
