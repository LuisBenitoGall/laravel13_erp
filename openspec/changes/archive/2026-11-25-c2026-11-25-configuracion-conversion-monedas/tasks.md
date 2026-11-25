# Tasks

## 1. Registro normativo (glosario / ficha / mapa)

- [x] 1.1 Añadir `ExchangeRate` / Conversión monedas a `docs/standards/glossary.md`
      y verificar que la fila cita Functionality `exchange-rates`
- [x] 1.2 Actualizar `docs/modules/Admin.md`: entidad ExchangeRate + ítem menú
      Configuración; vinculaciones V-07/V-08/V-20/V-21 sí; R-TEN-05 N/A; E2E N/A
- [x] 1.3 Añadir `ExchangeRate` a la fila entidades Admin en
      `docs/standards/module-map.md` (sin dependencia inter-dominio nueva)

## 2. Datos — ExchangeRate

- [x] 2.1 Migración global `exchange_rates` (`from_currency_id`, `to_currency_id`,
      `rate` decimal(18,6), `rate_date`, softDeletes, unique
      (from,to,rate_date) no trashed, FKs restrict a `currencies`); verificar
      `php artisan migrate`
- [x] 2.2 Model `ExchangeRate` (`--global`: SoftDeletes, Auditable, relaciones
      fromCurrency/toCurrency, cast `rate` decimal:6) + Factory
- [x] 2.3 Test cross-company: mismo catálogo visible desde company A y B
      (sustituto R-TEN-05 para `--global`)

## 3. Backend ExchangeRate

- [x] 3.1 Policy Spatie `exchange-rates.*` + FormRequests Store/Update/Table;
      validar from≠to, rate>0 6 dec, currencies Active en alta; upsert por
      unique
- [x] 3.2 Actions: `UpsertExchangeRatePair` (transacción ambos sentidos;
      omitir sentido vacío) + `DeleteExchangeRate` + `ResolveExchangeRate`
      (mayor `rate_date` ≤ D; null si no hay)
- [x] 3.3 Controller Index/table/store/update/destroy + Resource; rutas
      `admin/exchange-rates*` + middleware `EnsureModuleIsActive:configuracion`
- [x] 3.4 Feature Pest: authz 403; create dual → 2 filas; create un sentido;
      upsert same day; delete soft; resolve D−3 / D / null; Index default sort
      rate_date desc

## 4. Frontend + i18n

- [x] 4.1 Page Index: ServerTable (from code, to code, rate, rate_date) +
      Acciones Edit → Delete confirm; ActionBar Nuevo; breadcrumb Configuración
- [x] 4.2 Create/Edit: selects Currency Active A/B, fecha, rate A→B, rate B→A;
      CTA «Rellenar inverso» (1/rate, 6 dec); zod espejo FormRequest
- [x] 4.3 Tipos TS + i18n es/en/ca (labels Conversión monedas, errores,
      columnas)

## 5. Menú / Functionality / vinculaciones

- [x] 5.1 Seed Functionality `exchange-rates` bajo Module `configuracion` +
      Spatie `exchange-rates.*` (AdminCatalogSeeder / CreateFunctionality /
      RealignMenuCatalog); label Conversión monedas
- [x] 5.2 `CONFIGURATION_SLUGS` / `MenuFunctionalityHref` tras `currencies` →
      Index; feature: menú SA incluye Conversión monedas (V-07, V-08)
- [x] 5.3 Feature V-21: sin permiso → 403 en rutas; SA con capas OK
- [x] 5.4 Auditable/activity log configurado como Currency (V-20); verificar
      atributos logueados en create/update

## 6. Calidad

- [x] 6.1 Pint (`--dirty`) + PHPStan en PHP tocado; suite feature
      exchange-rates verde

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40; E2E N/A).
