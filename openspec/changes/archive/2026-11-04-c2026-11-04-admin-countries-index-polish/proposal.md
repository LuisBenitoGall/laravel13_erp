# Proposal

## Why

El Index de Países (y los de Provincias/Poblaciones) muestra Acciones con hueco
vacío, dos lápices idénticos para drill vs Edit, y columna Estado redundante con el
toggle. La causa de iconos/ancho está en el wrapper `ServerTable`, no solo en las
páginas geográficas.

## What Changes

- `ServerTable`: honorar `RowAction.icon` (Lucide); dejar de hardcodear Pencil en
  botones no-danger; ancho de Acciones ajustado al contenido (`widthGrow: 0` +
  width por nº de slots).
- Indexes Countries / Provinces / Towns: quitar columna Estado; drill con iconos
  `Map` (provincias) y `MapPin` (poblaciones); Edit con `Pencil`.
- Sin cambios de negocio, seeder, cascada ni menú.

## Capabilities

### New Capabilities
- (ninguna)

### Modified Capabilities
- `server-table-reference`: Acciones respetan icono declarado; ancho de columna
  Acciones coherente con controles (sin hueco muerto por grow).
- `admin-geography-catalog`: Indexes sin columna Estado; iconos drill Map/MapPin.

## Impact

- `resources/js/Components/Table/ServerTable.tsx` (efecto en todos los Indexes con
  `rowActions`).
- `Admin/Countries|Provinces|Towns/Index.tsx`.
- Tests feature/browser smoke opcionales; preferir assert de props/columnas o
  snapshot ligero si existe harness UI; E2E N/A.
