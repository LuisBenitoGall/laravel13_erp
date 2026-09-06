## Why

Sin el "arnés" transversal (tenancy, numeración, contrato de tablas, generador,
backstop de completitud, arch tests y CI) cualquier módulo de negocio se construiría a
mano, frágil y sin guardrails mecánicos. Es la Capa 0 de la Fase 0: prerrequisito del
change de Admin (`admin-catalog-permissions`) y de todo dominio posterior.

## What Changes

- Instalar Pest (`pestphp/pest` ^4 + `pest-plugin-laravel` + `pest-plugin-arch`),
  sustituyendo el PHPUnit puro del esqueleto.
- Implementar piezas de soporte de `templates-backend.md` §12:
  `BelongsToCompany`, `Auditable`, `CurrentCompany` (+ middleware `SetCurrentCompany`),
  `TableRequest`/`TableBuilder`, `NumberingService` (R-BD-05), middleware
  `EnsureModuleIsActive` (alias `module:`), helper Pest `withCurrentCompany()`.
- Implementar el generador `make:domain-entity` con stubs en `stubs/domain-entity/` y
  flags de `tooling.md` §1 (incluida auto-provisión Functionality↔permisos cuando los
  modelos existan).
- Implementar el backstop de completitud: `EntityCompletenessTest` +
  `artisan module:audit` (`tooling.md` §2).
- Añadir tests de arquitectura iniciales (estructura/capas, tenancy, capas, higiene).
- Configurar PHPStan (larastan, nivel 6) y Pint (preset `laravel`).
- Escribir el workflow de CI según `tooling.md` §4 (fichero en repo; ejecución remota
  fuera de alcance).

## Capabilities

### New Capabilities

- `tenancy-support-kernel`: mecanismos base de tenancy, auditoría, numeración, contrato
  de tablas y middleware de módulo activo, en `app/Support/` (y `NumberingService` en
  Admin según templates), verificados con fixtures — sin entidades de negocio reales.
- `domain-entity-generator`: comando `make:domain-entity` + stubs alineados a templates;
  genera artefactos "Siempre" del playbook; no sobreescribe sin `--force`.
- `entity-completeness-backstop`: detección de artefactos faltantes vía
  `EntityCompletenessTest` (CI) y `module:audit` (humano), incluida integridad
  Functionality↔permisos cuando existan.
- `static-quality-and-ci`: Pest + arch tests iniciales + PHPStan nivel 6 + Pint +
  workflow CI.

### Modified Capabilities

(ninguna — `frontend-bootstrap` no se toca)

## Impact

- Dependencias nuevas (dev): `pestphp/pest`, `pest-plugin-laravel`, `pest-plugin-arch`,
  `larastan/larastan` (canon en `libraries.md`). Pint ya instalado.
- Nuevos paths: `app/Support/**`, `app/Http/Middleware/{SetCurrentCompany,EnsureModuleIsActive}.php`,
  `app/Domains/Admin/Services/NumberingService.php` (+ migración mínima de
  `document_numbers`), comando/generador + `stubs/domain-entity/`,
  `tests/Architecture/**`, `tests/Pest.php`, `phpstan.neon`, `pint.json`,
  `.github/workflows/ci.yml` (o equivalente).
- Suite de tests pasa de PHPUnit puro a Pest.
- Ningún módulo de negocio, `Company`, Spatie Teams ni autenticación de producto.
- Sin ficha `docs/modules/*`: no es un módulo de dominio (Capa 0 de tooling).
