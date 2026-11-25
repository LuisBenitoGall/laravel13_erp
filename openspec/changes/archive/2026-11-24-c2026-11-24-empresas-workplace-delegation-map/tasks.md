# Tasks

## 1. Datos y dominio

- [x] 1.1 Migration pivote workplace↔company (delegación) + `is_preferred`;
  unique (workplace_id, company_id); verificar migrate OK
  (`workplace_delegation`; migrate + `db:table` verificados)
- [x] 1.2 Relations Eloquent + Action sync (replace set, validar parent_id /
  sesión, preferida ≤1); verificar unit/feature perímetro y preferida
  (`Workplace::delegations()`, `Company::workplacesViaMap()`, `SyncWorkplaceDelegations`)
- [x] 1.3 Glosario: término del pivote / “delegación preferida de sede”;
  verificar entrada en glossary.md

## 2. UI y cierre

- [x] 2.1 Edit Workplace: multi-select delegaciones hijas + preferida; i18n;
  verificar persistencia round-trip
  (panel `WorkplaceDelegationsPanel`, `PUT admin/workplaces/{workplace}/delegations`)
- [x] 2.2 (Opcional) Panel RO en Edit Delegación: workplaces de la matriz que
  la tienen vinculada — solo si no alarga el change
  **OMITIDA** (opcional según design §5; el write path vive en Edit Workplace.
  La lectura inversa `Company::workplacesViaMap()` queda expuesta y testeada.)
- [x] 2.3 Pint dirty + tests del change en verde

## Workflow follow-up

- Auditoría 25-architecture-review tras `/opsx-apply`.
- E2E rol 40: N/A (design).
- 30-testing + `/opsx-archive` cuando DoD OK.
- Siguiente oleada (fuera de este change): sociedad en pedidos + listados grupo.
