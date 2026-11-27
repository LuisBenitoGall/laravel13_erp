# Proposal

## Why

Los maestros Family, Category y Tag ya existen. Falta vincularlos al
Product en la ficha Edit (tab General), todo opcional, con alta in situ
de tags (Active) cuando el actor tenga `tags.create`, para clasificar sin
salir del flujo de edición.

## What Changes

- Persistencia: Product **0..1 Family**, **0..N Categories**, **0..N Tags**
  (misma company); sin exigir ninguno.
- UI en tab **General** de Edit: select familia; multi categorías; multi
  tags + crear tag in situ (no Family/Category in situ).
- Authz: update producto para asignar; **`tags.create`** para alta in situ
  (tag nuevo Active).
- Sin tab Clasificación; sin Family↔Category; Create sigue mínimo
  (nombre+tipo).
- Docs + tests (vínculos, in situ, R-TEN-05).

## Capabilities

### New Capabilities

- `catalogue-product-classification`: vínculos Product↔taxonomía y UI/
  authz de clasificación en Edit General (incl. tags in situ).

### Modified Capabilities

- `catalogue-products`: tab General incluye clasificación opcional.

## Impact

- Migraciones FK/pivotes; `UpdateProduct` + Actions tag quick-create;
  Edit/ProductForm props; posible `Form/MultiSelect` (creatable).
- Sin Module/Functionality nueva. E2E §4: **N/A**.
- Siguiente backlog: Family↔Category N:N; filtros Index (fuera).
