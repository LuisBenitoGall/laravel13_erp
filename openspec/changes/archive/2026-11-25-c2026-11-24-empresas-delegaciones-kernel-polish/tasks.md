# Tasks

## 1. Estándar (constitución)

- [x] 1.1 Enmendar `docs/standards/kernel.md`: sección grupo societario (matriz,
  Delegación, Workplace matriz vs hija) + bloque “Invariantes aplazadas —
  documentos comerciales” (Goals §5–6 del brief); verificar texto citable y
  marcado como no implementado en este change
- [x] 1.2 Añadir R-TEN-* en `docs/standards/architecture.md` (Delegación ≠
  sesión vía switcher; `company_accounts` / gate anclado a matriz) con
  remisión al kernel; verificar número libre (p. ej. tras R-TEN-10)
- [x] 1.3 Alinear glosario si falta matiz “Workplace de Delegación vs mapa”;
  `.cursor/rules/01-kernel.mdc` (+ `CLAUDE.md` solo si contradice); verificar
  always-on sin “toda Company = sesión” plano

## 2. Panel RO Edit Delegación

- [x] 2.1 Prop Inertia en edit: workplaces de la matriz vinculados a la hija
  (nombre + is_preferred); perímetro parent_id; verificar Pest / assert
  Inertia
- [x] 2.2 Componente panel RO + copy distintivo i18n (empty state); verificar
  UI con vínculo preferido y sin vínculos
- [x] 2.3 Pint dirty + tests del change en verde

## Workflow follow-up

- Auditoría 25-architecture-review tras `/opsx-apply`.
- E2E rol 40: N/A (design).
- 30-testing + `/opsx-archive` cuando DoD OK.
- Documentos comerciales (dropdown sociedad, etc.): change futuro; citar kernel.
