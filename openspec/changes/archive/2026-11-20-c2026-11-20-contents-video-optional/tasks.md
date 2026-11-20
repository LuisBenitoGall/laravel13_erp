# Tasks

## 1. Validación y tests

- [x] 1.1 Quitar el override `video` → `required|array` en Article en
  `UpdateContentRequest`; verificar que las reglas base
  sometimes/nullable/array + `video.*` url se mantienen
- [x] 1.2 Feature: guardar Article con `video: []` (o sin URLs) responde
  redirect OK y persiste `[]`; verificar test verde (regresión del
  mensaje «obligatorio»)

## 2. Spec / ficha / DoD

- [x] 2.1 Confirmar delta `contents-edit` alineado; actualizar ficha
  Admin/Contents si afirma lo contrario; V-01..V-20 no; E2E N/A
- [x] 2.2 Pint dirty + PHPStan + tests Content afectados en verde

## Workflow follow-up

- Revisión Luis → `/opsx:apply` → 25 → 30 archive.
