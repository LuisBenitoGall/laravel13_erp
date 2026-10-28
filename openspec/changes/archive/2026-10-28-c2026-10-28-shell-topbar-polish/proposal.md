## Why

El primer `shell-topbar` dejó el colapso ocultando el aside en md+ (`hidden`), el topbar
con idioma/stubs a la izquierda junto al collapse, y notas en Dialog centrado frente al
Popover de notificaciones. Hay que alinear chrome a la referencia v1 (mini-rail +
acciones a la derecha + paneles bajo el icono) y persistir la preferencia de colapso.

## What Changes

- Desktop/tablet colapsado → **mini-aside** (rail de iconos por punto principal), no
  ocultar; logos reducidos; flyout B1 para grupos con hijos.
- Persistencia colapsado/expandido entre recargas y navegación Inertia.
- Móvil: off-canvas (sin mini-rail), como ahora en espíritu.
- Topbar: collapse izquierda; resto (idioma → avatar) a la **derecha**.
- Notas (y stubs con panel) → Popover bajo el icono, mismo patrón que notificaciones.
- **No** dominio real de stubs; **no** bloque 2.

## Capabilities

### New Capabilities

- _(ninguna)_ — correcciones sobre chrome existente.

### Modified Capabilities

- `shell-navigation`: colapso = mini-aside md+ + persistencia + flyout + logos
  reducidos; móvil off-canvas.
- `shell-topbar`: alineación izq/der; stubs unificados como popover bajo icono (notas
  deja de ser modal centrado).

## Impact

- `AppLayout.tsx`, `AsideNav.tsx`, `AppTopbar.tsx`, `NotesStub.tsx` (+ helpers icon/map
  lucide si hace falta)
- localStorage (clave estable documentada en design)
- Tests Feature/smoke de layout si existen aserciones de collapse
- Specs delta `shell-navigation` / `shell-topbar`

## Excepciones a standards

Ninguna.
