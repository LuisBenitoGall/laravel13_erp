# Proposal

## Why

El kebab del topbar es stub; el dashboard no muestra accesos rápidos. Hay
que portar el flujo v1 de «Mis opciones preferidas»: guardar la vista
actual y exponerla como CTA en el dashboard, con scope multi-empresa.

## What Changes

- Modelo `Preference`: `name`, `url` (path sin host), `user_id`,
  `company_id`, `module_id` nullable.
- Topbar: popover real (sustituye `FavoritesStub`) → alta con nombre +
  path actual; tope 20 por (user, company).
- Dashboard: sección CTAs (color/icon Module) + delete.
- Listado filtrado por user + company de sesión.
- Delta `shell-topbar`: favoritos dejan de ser stub; resto stubs igual.

## Capabilities

### New Capabilities

- `user-preferences-favorites`: Preference CRUD personal + CTAs
  dashboard.

### Modified Capabilities

- `shell-topbar`: kebab favoritos = formulario real (no empty stub).

## Impact

- Migración/modelo/Policy/Actions; rutas store/destroy.
- `FavoritesStub` → componente real; `Dashboard.tsx` lista props.
- Shared/Inertia: path actual en cliente (`usePage().url`).
- Tests Feature: alta, tope 20, filtro company, delete, ownership.
- Glosario + Admin.md / shell docs.
