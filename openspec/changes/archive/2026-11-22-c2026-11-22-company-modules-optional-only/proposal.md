# Proposal

## Why

Mis módulos (`/admin/company-modules`) lista Basic y AdminOnly y permite
Apagar Basic. El producto acordado: esa pantalla solo gestiona Optional
Active globales; Basic siempre disponibles sin toggle; AdminOnly solo Super
Admin fuera de esta UI.

## What Changes

- Rejilla Mis módulos: solo Modules `Optional` + `Active` global; sin Basic
  ni AdminOnly; Inactive global ocultos.
- Optional Active no autorizados: visibles en gris / no encendibles.
- Encender/Apagar (Actions + rutas) solo para Optional; rechazo si Basic o
  AdminOnly.
- Basic Active + autorizados: «siempre on» para menú/acceso de la company
  (sin depender de toggle en Mis módulos).
- AdminOnly: fuera de Mis módulos; disponibilidad SA vía circuito menú
  existente.
- Super Admin: Mis módulos solo company de sesión vinculada; catálogo global
  separado.
- Alinear `docs/standards/kernel.md` §5, spec `company-module-layers`, UI,
  tests.

## Capabilities

### New Capabilities
- _(ninguna)_

### Modified Capabilities
- `company-module-layers`: semántica Mis módulos Optional-only; Basic siempre
  on; AdminOnly fuera de esa UI.

## Impact

- `CompanyModuleController` Index/enable/disable; Actions Enable/Disable.
- `MenuBuilder` (y middleware de módulo si aplica) para Basic siempre on.
- `Admin/CompanyModules/Index.tsx`.
- `docs/standards/kernel.md` §5; tests `CompanyModuleLayersTest` y afines.
- Sin migraciones. E2E §4: no aplica.
- Elegibilidad SA (`company-module-authorizations`): sin rediseño salvo
  coherencia mínima.
