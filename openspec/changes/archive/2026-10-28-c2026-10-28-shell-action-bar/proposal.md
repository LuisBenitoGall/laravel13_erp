## Why

Tras topbar y breadcrumb, falta el **bloque 3**: la barra de acciones de vista (Volver,
Crear, Eliminar, …) con estructura fija alineada a la izquierda, para coherencia en todo
el ERP. Hoy los CTA viven sueltos en el contenido (p. ej. Crear en Index).

## What Changes

- `ViewActionBar` (bloque 3 bajo breadcrumb): array declarativo extensible; CTAs con
  icono; Volver solo si hay URL de origen; Delete (trash + confirm) siempre último con
  espacio mínimo.
- Migrar páginas `AppLayout` (alcance B); subir acciones existentes (Crear, etc.).
- Botones de formulario **fuera** de este bloque (dentro del form / tabs).
- Contrato abierto a futuros tipos (p. ej. dropdown); sin implementar dropdowns ahora.
- Catálogo `frontend.md`: distinguir ActionBar de **vista** vs FormActions / ActionBar de
  ciclo de vida de documento.

## Capabilities

### New Capabilities

- `shell-action-bar`: chrome bloque 3, contrato de acciones, Volver/Delete/CTAs, migración.

### Modified Capabilities

- `shell-breadcrumb`: el breadcrumb ya no “prohíbe” ActionBar; la frontera es breadcrumb
  luego ActionBar bajo él.
- `admin-ui-login` (si aplica): AppLayout incluye bloque 3 cuando hay acciones.

## Impact

- `AppLayout.tsx`, nuevo componente ViewActionBar (+ ConfirmDialog si falta)
- Hook/util de URL de origen (navegación Inertia)
- Páginas `resources/js/Pages/**` con AppLayout
- i18n; `docs/standards/frontend.md`
- Tests Feature/smoke

## Excepciones a standards

Ninguna. Ficha módulo SaaS: N/A.
