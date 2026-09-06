## ADDED Requirements

### Requirement: Calidad estática y CI
El sistema SHALL tener PHPStan (nivel 6, larastan) y Pint (preset `laravel`) en verde
sobre el código existente, tests de arquitectura iniciales según `tooling.md` §2, y un
pipeline de CI que ejecute lint/typecheck/tests en el orden de `tooling.md` §4.

#### Scenario: Calidad estática en verde
- **WHEN** se ejecuta `vendor/bin/phpstan analyse` y `vendor/bin/pint --test`
- **THEN** ambos terminan sin errores

#### Scenario: Pipeline de CI definido
- **WHEN** se revisa el repositorio
- **THEN** existe un fichero de workflow de CI que ejecuta, en orden, instalación →
  calidad estática → tests (unit/feature/arch) → build de frontend → auditoría de
  dependencias, según `tooling.md` §4

#### Scenario: Arch tests iniciales presentes
- **WHEN** se ejecuta la suite Pest incluyendo `tests/Architecture/`
- **THEN** corren las reglas mínimas de estructura/capas, tenancy, capas e higiene
  general definidas en `tooling.md` §2
