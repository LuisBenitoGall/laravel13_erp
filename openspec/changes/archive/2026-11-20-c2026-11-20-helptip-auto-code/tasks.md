# Tasks

## 1. Form kit

- [x] 1.1 `FormHelpScope` (context) + docs de uso. Verificar export.
- [x] 1.2 `Field`: resolve code (helpCode | scope.fieldKey); fieldKey
      prop / name / htmlFor último segmento. Verificar derive +
      override + sin icono si falta tip.

## 2. Migración NIF + CompanyForm

- [x] 2.1 Fixture/seeder `companies.nif`; rename BD `company-nif` →
      `companies.nif`; alias temporal mapa si aplica. Verificar seeder
      + mapa.
- [x] 2.2 `CompanyForm`: `FormHelpScope value="companies"`; quitar
      helpCode hardcodeado. Verificar Create/Edit tip con
      `companies.nif` Active.

## 3. Docs + calidad

- [x] 3.1 frontend.md + glosario convención `{scope}.{field}`.
      Verificar docs.
- [x] 3.2 Features (derive/override/migración) + Pint + verdes.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Alinear otros forms a helpScope = work futuro por formulario.
