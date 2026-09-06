## 1. Tooling base (Pest + calidad estática)

- [x] 1.1 Instalar `pestphp/pest` ^4, `pest-plugin-laravel` y `pest-plugin-arch`
      (canon `libraries.md`); migrar la suite del esqueleto de PHPUnit puro a Pest
      > Nota: constraint efectiva `^4.7` por conflicto de `laravel/pao` con Pest `<4.6.3`.
- [x] 1.2 Configurar helper `withCurrentCompany()` en `tests/Pest.php`
- [x] 1.3 Instalar `larastan/larastan`; crear `phpstan.neon` a **nivel 6**
- [x] 1.4 Crear `pint.json` con preset `laravel`; `vendor/bin/pint --test` en verde
- [x] 1.5 `vendor/bin/phpstan analyse` en verde sobre el código existente + lo nuevo de
      este change

## 2. Kernel de tenancy y soporte (`templates-backend.md` §12)

- [x] 2.1 Implementar `CurrentCompany` (`app/Support/Tenancy`) + middleware
      `SetCurrentCompany` (alias `current_company`) registrado en el stack web
- [x] 2.2 Implementar trait `BelongsToCompany` (global scope + fill `company_id` en
      creating + `belongsToCurrentCompany()`)
- [x] 2.3 Implementar trait `Auditable` (`created_by` / `updated_by`)
- [x] 2.4 Crear fixture de prueba desechable (modelo/tabla sintética con `company_id`) —
      sin tabla `companies` real
- [x] 2.5 Tests: aislamiento de tenant (R-TEN-05) y auditoría básica contra el fixture
- [x] 2.6 Implementar `TableRequest` + `TableBuilder` (`app/Support/Table`) según contrato
      `frontend.md` §3.1; tests con query sintética (whitelist sort/filter, paginación)
- [x] 2.7 Implementar `NumberingService` en `app/Domains/Admin/Services` + migración
      mínima `document_numbers`; test de concurrencia sin colisiones (`FOR UPDATE`,
      R-BD-05)
- [x] 2.8 Implementar `EnsureModuleIsActive` (alias `module:`) con checker inyectable /
      stub hasta que exista `Module`; test con fake

## 3. Generador `make:domain-entity` (`tooling.md` §1)

- [x] 3.1 Crear comando Artisan `make:domain-entity` con firma y flags:
      `--status/--numbered/--translatable/--lines/--seed/--notifiable/--exportable/
      --no-frontend` (+ `--force`)
- [x] 3.2 Crear stubs en `stubs/domain-entity/` alineados a
      `templates-backend.md` / `templates-frontend.md` (artefactos "Siempre" +
      condicionales por flag)
- [x] 3.3 Implementar regla de no-sobreescritura salvo `--force`
- [x] 3.4 Implementar auto-provisión Functionality + 5 permisos (R-AUT-07) con
      `firstOrCreate`; si las clases aún no existen, omitir con aviso (no romper
      generación de ficheros)
- [x] 3.5 Imprimir checklist restante no automatizable (rutas, ficha módulo,
      vinculaciones §6)
- [x] 3.6 Tests Feature del generador: invocan Artisan sobre dominio/entidad scratch,
      asertan ficheros generados (p. ej. con `--status`), limpian después; cubren
      escenario de no-sobreescritura

## 4. Backstop de completitud (`tooling.md` §2)

- [x] 4.1 Implementar `tests/Architecture/EntityCompletenessTest.php` (recorre
      `app/Domains/*/Models`, comprueba artefactos "Siempre"; pasa con conjunto vacío)
- [x] 4.2 Implementar `artisan module:audit {domain?} {entity?}` con tabla ✓/✗ en
      consola
- [x] 4.3 Incluir chequeo Functionality↔permisos cuando existan esos modelos; no-op /
      skip documentado si aún no existen
- [x] 4.4 Test del comando `module:audit` (salida / códigos) con fixture mínimo o
      conjunto vacío

## 5. Tests de arquitectura iniciales

- [x] 5.1 Arch: modelos en su dominio; Support no usa Domains (R-ARQ-02); Services de
      dominio privados (R-DOM-03) — con `ignoring` documentado si aplica
- [x] 5.2 Arch: modelos de negocio usan `BelongsToCompany` (R-TEN-02); prohibido
      `session('currentCompany')` (R-TEN-03)
- [x] 5.3 Arch: controllers delgados sin DB directa (R-CAP-01); Actions con `handle()`
      (R-CAP-02); FormRequests en `Http/Requests` (R-CAP-04)
- [x] 5.4 Arch: higiene sin `dd`/`dump`/`ray`/`env` fuera de config
- [x] 5.5 (Diferido) smokes no-arch de policies 403 e i18n es↔en — fuera de este change
      (requieren auth/rutas/lang de negocio)

## 6. Pipeline CI (`tooling.md` §4)

- [x] 6.1 Crear workflow GitHub Actions: composer install + npm ci
- [x] 6.2 Jobs/pasos: Pint `--test` · PHPStan · ESLint · `tsc --noEmit`
- [x] 6.3 Pest (unit + feature + arch) con MySQL de servicio en strict mode (R-BD-01)
- [x] 6.4 `npm run build`
- [x] 6.5 `composer audit` + `npm audit` (falla en vulnerabilidad alta)
- [x] 6.6 Step Pest browser: presente como job desactivado/TODO hasta
      `pest-plugin-browser` (decision design §6) — no bloquear el resto del pipeline

## 7. Definition of Done de este change

- [x] 7.1 `composer test` (Pest) verde, incluidos Architecture y tests del generador /
      kernel
- [x] 7.2 `vendor/bin/pint --test` y `vendor/bin/phpstan analyse` verdes
- [x] 7.3 No queda entidad scratch ni tabla `companies` de negocio; Non-Goals
      respetados
- [x] 7.4 `openspec validate c2026-07-16-kernel-tooling-fase0` limpio tras apply (lo
      verifica 30-testing al archivar)
