# Tasks

## 1. Orden Active-first

- [x] 1.1 Helper compartido (Support/Table o equivalente) que, dado un query +
      status column/enum Active, aplica CASE Active-first y opcionalmente
      secundario `name`; documentar uso solo cuando sorts vacío
- [x] 1.2 Modules `table`: si sorts vacío → Active-first (sin secundario)
- [x] 1.3 Countries / Provinces / Towns `table`: si sorts vacío → Active-first +
      `name` (sustituye el `orderBy('name')` solo)
- [x] 1.4 Currencies `table`: igual que Countries

## 2. Tests

- [x] 2.1 Feature Modules: sin sorts, Active antes que Inactive; con sort
      cliente, no impone Active-first
- [x] 2.2 Feature Countries (smoke Provinces/Towns o parametrizado): sin sorts
      Active+name; con sort no prefija
- [x] 2.3 Feature Currencies: idem Countries

## 3. Calidad

- [x] 3.1 Pint + PHPStan en PHP tocado; tests afectados verdes

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40).
