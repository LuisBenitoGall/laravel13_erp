## Why

El topbar (bloque 1) ya no lleva el título de vista; hace falta el **bloque breadcrumb**
bajo el topbar (option + suboption vía i18n, como el `$header` de v1), obligatorio en las
pantallas `AppLayout`, sin meter aún la ActionBar.

## What Changes

- Componente/bloque breadcrumb: título principal (`option`) + secundario (`suboption`).
- Contrato A: claves i18n + `optionHref` al index; Index usa suboption `dashboard`.
- C1: solo `option` es enlace; `suboption` texto.
- Migrar todas las páginas actuales con `AppLayout`.
- Sin `module`/`slug` del header v1; sin ActionBar.

## Capabilities

### New Capabilities

- `shell-breadcrumb`: bloque option/suboption bajo el topbar, i18n, option clicable.

### Modified Capabilities

- `shell-topbar`: el título de contexto de vista vive en breadcrumb (bloque 2), no en
  topbar; documentar la frontera bloque 1 / bloque 2.
- `admin-ui-login` (si aplica): AppLayout expone breadcrumb en lugar de depender del
  title como cabecera visual.

## Impact

- `AppLayout.tsx`, nuevo `PageBreadcrumb` (o nombre de catálogo)
- Páginas under `resources/js/Pages/**` que usan `AppLayout`
- Claves i18n es/en/ca (option keys alineadas a menú / pantallas existentes)
- `docs/standards/frontend.md` catálogo (fila PageBreadcrumb)
- Tests Feature/smoke de layout si existen

## Excepciones a standards

Ninguna. Ficha módulo SaaS: N/A (chrome transversal).
