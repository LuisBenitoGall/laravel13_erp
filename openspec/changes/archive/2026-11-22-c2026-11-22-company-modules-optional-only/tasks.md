# Tasks

> Sin entidad BD nueva. E2E §4: no aplica. Actualiza kernel normativo en el
> mismo change (corrección de contrato acordada).

## 1. Contrato normativo

- [x] 1.1 Actualizar `docs/standards/kernel.md` §5: Mis módulos / toggle =
  Optional; Basic siempre on si Active+autorizado; AdminOnly fuera de Mis
  módulos (SA); verificar que el texto ya no diga que Basic se enciende/
  apaga en company-modules

## 2. Backend capas

- [x] 2.1 `CompanyModuleActivationChecker`: Basic Active+autorizado → true
  sin exigir `company_modules`; Optional sin cambio de triple condición;
  verificar tests
- [x] 2.2 `MenuBuilder`: misma regla Basic siempre on; verificar que Basic
  siguen en menú tras ausencia de fila enabled
- [x] 2.3 `EnableModuleForCompany` / `DisableModuleForCompany`: solo
  Optional; rechazo Basic/AdminOnly; verificar Pest
- [x] 2.4 `CompanyModuleController@index`: filtrar Optional+Active; enable/
  disable heredan rechazo; verificar payload Index

## 3. Frontend y tests

- [x] 3.1 Ajustar copy UI Mis módulos si hace falta (solo Optional); verificar
  que no se renderizan Basic/AdminOnly
- [x] 3.2 Actualizar/ampliar `CompanyModuleLayersTest` (y afines) a la nueva
  semántica; suite filtrada verde + Pint PHP dirty

## Workflow follow-up

- Auditoría 25 → 30-testing (sin 40) → `/opsx-archive`
- Ejecutar `/opsx:apply` para implementar
