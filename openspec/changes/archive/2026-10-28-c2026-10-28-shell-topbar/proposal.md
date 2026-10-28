## Why

El aside del shell ya está cerrado; el header de `AppLayout` sigue siendo mínimo y mezcla
chrome global con el título de vista. Hace falta el **bloque 1** del topbar (estructura
tipo v1, look v2) como chrome obligatorio del ERP, con stubs de notificaciones/notas/chat/
favoritos y empresa/avatar reales, sin meter aún el bloque 2 (título + ActionBar).

## What Changes

- Topbar global L→R: collapse aside (móvil colapsado por defecto), idioma, notificaciones
  stub, notas stub, chat stub, favoritos stub, empresa (nombre si 1 / selector si N),
  avatar con Perfil + Logout.
- Quitar el título de vista del header (bloque 1); título/acciones → change posterior.
- Página de perfil mínima (nombre/email lectura + “próximamente”).
- Componentes de catálogo previstos: `NotificationsPanel`, `CompanySwitcher`, más piezas
  de topbar coherentes con `frontend.md` / shadcn.
- **No** dominio real de notificaciones/chat/favoritos/notas; clicks stub = empty UI.

## Capabilities

### New Capabilities

- `shell-topbar`: chrome bloque 1 (collapse, idioma, stubs, empresa, avatar, perfil
  placeholder).

### Modified Capabilities

- `admin-ui-login`: `AppLayout` deja de mostrar título en header; logout vía menú avatar;
  topbar sustituye el chrome mínimo actual.
- `shell-navigation`: coordinación aside colapsable con el botón del topbar (estado
  compartido en layout); sin cambiar reglas de menú/Dashboard/Configuración.

## Impact

- `resources/js/Layouts/AppLayout.tsx` y páginas que pasan `title` (dejarán de verlo en
  topbar hasta bloque 2)
- Nuevos componentes under `resources/js/Components/` (topbar / NotificationsPanel /
  CompanySwitcher / UserMenu / stubs)
- Posibles primitivas shadcn (`dropdown-menu`, `dialog`/`sheet`) si faltan en `ui/`
- Ruta Inertia perfil (`auth` + `company`), i18n es/en/ca
- Tests Feature (perfil, logout, switch empresa en UI) y ajuste de aserciones de layout
- Docs: nota breve en ficha Admin o `frontend.md` solo si 10 lo deja en tasks (catálogo)

## Excepciones a standards

Ninguna. Stubs no sustituyen el catálogo futuro Reverb; no se inventan módulos SaaS.
Ficha de módulo de dominio: N/A (chrome transversal AppLayout, no Module/Functionality
nuevos). Vinculaciones V-01..V-20: N/A salvo V-15/i18n y menú shell ya existente.
