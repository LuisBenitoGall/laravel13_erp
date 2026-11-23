# Tasks

## 1. Assert opción B

- [x] 1.1 Reescribir `AssertEmployeeNoOverlap`: intervalos vs
  `scheduled`+`active`, `newEnd` opcional, `exceptIds`; verificar unit/feature
  con dos scheduled solapados (caso ACME) → ValidationException
- [x] 1.2 Cablear Create (excluir actives si nuevo nace active) y Renew
  (`exceptEmployeeId` del current) + FormRequests con end_date; verificar Renew
  indefinido D−1 y Create→active no se autobloquean
- [x] 1.3 Tests Pest: día siguiente OK; cross-company OK; actualizar test opción
  A obsoleto — verde
- [x] 1.4 Ajustar i18n del mensaje de solape si el copy habla solo de “activo”;
  Pint dirty — verde

## Workflow follow-up

- Auditoría 25-architecture-review tras `/opsx-apply`.
- E2E rol 40: N/A (design).
- 30-testing + `/opsx-archive` cuando DoD OK.
