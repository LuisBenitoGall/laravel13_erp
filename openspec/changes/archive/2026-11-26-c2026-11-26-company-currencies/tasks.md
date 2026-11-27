# Tasks

## 1. Registro normativo (glosario / ficha / mapa)

- [x] 1.1 Añadir fila glosario: monedas de uso / `company_currencies` (pivote
      Company↔Currency + `is_default`)
- [x] 1.2 Actualizar `docs/modules/Admin.md`: Company + Delegaciones (sección
      Monedas en Info); hub readiness = nif+logo+workplace+default moneda;
      V-18/V-20 sí; V-07/V-08 no; R-TEN-05 sí; E2E N/A
- [x] 1.3 Actualizar `docs/standards/module-map.md` (Company / pivote monedas;
      sin dependencia inter-dominio nueva)

## 2. Datos — company_currencies

- [x] 2.1 Migración `company_currencies` (`company_id`, `currency_id`,
      `is_default`, unique company+currency, FKs); verificar migrate
- [x] 2.2 Relación `Company::currencies()` BelongsToMany + pivot `is_default`;
      helper `defaultCurrency()`; factory/helpers de test si aplican

## 3. Backend sync

- [x] 3.1 FormRequest sync: `currency_ids[]`, `default_currency_id` nullable;
      set vacío ⇒ default null; set no vacío ⇒ default required ∈ ids;
      nuevas = Currency Active; ya vinculadas pueden permanecer Inactive
- [x] 3.2 Action `SyncCompanyCurrencies` (transacción; ≤1 `is_default=true`;
      activity log V-20)
- [x] 3.3 Rutas + controller methods: `PUT companies/{company}/currencies`,
      `PUT delegations/{delegation}/currencies`,
      `PUT company-settings/currencies` (sesión); authz
      companies.update / delegations.update / company-settings según design
- [x] 3.4 Feature Pest: sync OK; vacío; default ∉ set 422; Create sin moneda;
      R-TEN-05 (A no muta B); Delegación no altera matriz

## 4. Frontend Edit + i18n

- [x] 4.1 Componente compartido sección Monedas (select default + checks;
      code/símbolo/nombre; ayuda; aviso Inactive; form-layout-polish)
- [x] 4.2 Cablear en Edit Company (tab Info) + Edit Delegación (tab Info);
      props currencies/options; RecordSummary chip default si existe
- [x] 4.3 i18n es/en/ca (labels, errores, ayuda, paso hub)

## 5. Onboarding / readiness (V-18)

- [x] 5.1 Extender `CompanySetupReadiness`: paso `currencies`; total_steps=3;
      complete exige default
- [x] 5.2 Hub `company-settings`: tab/paso Monedas + wizard; sync vía ruta
      settings; tests readiness incompleta/completa + banner

## 6. Calidad

- [x] 6.1 Pint (`--dirty`) + PHPStan en PHP tocado; features del change en verde

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40; E2E N/A).
