# Proposal

## Why

El Index Mis Empresas (`Partners/Companies`) no cumple el contrato ServerTable
canónico: columnas crudas, sin fecha de alta, sin Acciones tipadas, y el
enlace SA de Autorizaciones vive en una columna HTML suelta.

## What Changes

- Alinear Index al estándar ServerTable (`ColumnDef`, header filters,
  `persistKey`, `exportable`, Acciones).
- Columna fecha de alta = `companies.created_at` (formato/alineación date).
- Datos: nombre, nombre comercial, nif, fecha alta; sin slug ni columna
  Estado suelta.
- Acciones: toggle status + Edit + Delete (confirm) + Autorizaciones (SA)
  dentro de Acciones.
- Backend: exponer `created_at`; filtros/sort whitelist; endpoint de status
  para el toggle (enum CompanyStatus + transiciones).
- Entidad Company / perímetro `forUser` sin mezclar con ficha User.

## Capabilities

### New Capabilities

- `mis-empresas-index`: Index Mis Empresas ServerTable canónico (Company).

### Modified Capabilities

- `company-tenancy`: el listado/table de companies sigue el perímetro de
  vínculo y se consume desde el Index canónico (fecha alta + Acciones).

## Impact

- `Partners/Companies/Index.tsx`; `CompanyResource`; `CompanyTableRequest`.
- Ruta/Action status (o equivalente) + Policy update.
- Tests Feature Partners; ficha `docs/modules/Partners.md`.
