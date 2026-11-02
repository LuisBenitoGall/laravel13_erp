# Proposal

## Why

El Edit de Module sigue siendo un MVP plano (sin tabs, slug/level editables en UI,
formulario desalineado del Create/kit). Luis necesita el Edit al estilo v1 (pestañas
Módulo | Funcionalidades), con reglas de inmutabilidad correctas, Volver fijo al Index
y sin menú genérico de Funcionalidades.

## What Changes

- Rehacer **Edit** de Module con tabs de página: «Módulo» + «Funcionalidades»
  (estructura/placeholder; CRUD de functionalities en change posterior).
- Tab Módulo: editar `name` (único CI, excluyendo el propio), `icon`, `color`,
  `label`/`explanation` (LangTabs), `status` (toggle). Reutilizar kit Create.
- **No** mostrar ni editar `slug` (no regenerar al cambiar name). **No** editar `level`.
- **Volver** en Modules/Edit siempre a `/admin/modules` (no solo `navOrigin`).
- **BREAKING (menú):** quitar la entrada genérica Funcionalidades del menú Configuración
  (seed/`MenuBuilder`); el CRUD vivirá bajo Module/Edit.
- Ajustar `UpdateModuleRequest` (sin `level`/`slug` en update; unicidad name CI).

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `admin-module-catalog`: contrato Edit (campos, inmutables, tabs, unicidad name).
- `admin-ui-login`: Create/Edit de Module alineado (Edit con tabs; sin menú
  Functionalities genérico).
- `shell-action-bar`: permitir Volver con destino fijo declarado por la página
  (Modules/Edit → Index), además del origen de navegación genérico.
- `menu-core-pillars`: Configuración SA sin ítem Funcionalidades genérico (solo
  Módulos / Roles / Permisos según pilares).

## Impact

- Front: `Admin/Modules/Edit.tsx` (+ tabs), AppLayout/ViewActionBar (`backHref`
  opcional), menú.
- Backend: `UpdateModuleRequest`, tests update; seeder `functionalities` +
  `MenuBuilder::CONFIGURATION_SLUGS` / mapa de rutas.
- Posible retiro o desactivación de páginas Index/Create/Edit genéricas de
  Functionalities (menú fuera; rutas huérfanas a limpiar o dejar inaccesibles hasta
  el CRUD anidado).
- Specs: `admin-module-catalog`, `admin-ui-login`, `shell-action-bar`,
  `menu-core-pillars`.
- Fuera: CRUD Functionalities anidado; Create Module; Wysiwyg.
