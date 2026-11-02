# Proposal

## Why

El Index de Módulos ya es la referencia `ServerTable`, pero la UI sigue mostrando
valores crudos (icono texto, nivel código) y una columna Estado redundante con el
toggle. Además, Admin/Workspace ocultan el toggle y el backend rechaza
desactivarlos. El toggle del listado queda solo para módulos optativos: básico y
exclusivo de Super Admin no lo muestran.

## What Changes

- Renderizar el icono Lucide en la columna Icono; el nombre del icono en `title`.
- Mostrar Nivel con etiqueta i18n (no `admin_only` / `basic` crudos).
- **BREAKING (contrato de columnas):** quitar la columna Estado del Index. El toggle
  de Acciones es la señal de activo/inactivo solo en módulos `optional`.
- Mostrar el toggle de estado solo en módulos de nivel `optional`. En `basic` y
  `admin_only` el toggle no se renderiza. Edit permanece en todas las filas.
- Permitir persistir Inactive también en módulos shell (eliminar el rechazo en
  `UpdateModule` / invertir el test que lo prohíbe).
- Reutilizar el toggle/`patchJson` ya existente; Edit permanece; sin Delete.

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `server-table-reference`: columnas del Index de Módulos (sin `status`); icono
  dibujado + `title`; Nivel traducido; toggle solo en nivel `optional`.
- `admin-module-catalog`: el catálogo SHALL permitir cambiar `status` también en
  módulos shell (`admin` / `workspace`); ya no hay bloqueo de desactivación.
- `admin-ui-login`: alinear el escenario del Index de Module con columnas e
  Acciones actualizadas (sin columna Estado; toggle solo en `optional`).

## Impact

- Front: `resources/js/Pages/Admin/Modules/Index.tsx` (+ reuso de
  `resolveMenuIcon` / `menuIcons.ts` si aplica).
- Backend: `UpdateModule` (quitar guard `isShellCritical` al desactivar);
  posible limpieza de `Module::isShellCritical` / `SHELL_SLUGS` si quedan sin
  callers; i18n `modulo_shell_no_desactivable` si deja de usarse.
- Tests: `tests/Feature/Admin/ModuleTableReferenceTest.php` (invertir caso shell;
  aserciones de columnas / UI si las hay).
- Specs globales al archivar: `server-table-reference`, `admin-module-catalog`,
  `admin-ui-login`.
- Fuera de alcance: otros Index; flujos nuevos de “acceso solo admin en pruebas”
  más allá del status de catálogo.
