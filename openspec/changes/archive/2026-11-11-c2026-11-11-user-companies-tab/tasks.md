# Tasks

## 1. Schema + modelo

- [x] 1.1 Migración: `position`, `department` nullable string en
      `user_companies`. Verificar migrate.
- [x] 1.2 Modelo `UserCompany` (o withPivot equivalente) + factory/helpers
      para tests. Verificar relation User↔Company con pivot fields.

## 2. Backend

- [x] 2.1 Policy membership (table/delete) = self o updateCompanyUser sobre
      owner. Verificar 403.
- [x] 2.2 Endpoint `*/table` profile + company-users (todas las companies
      del user; columnas acordadas). Verificar JSON table.
- [x] 2.3 Action detach + destroy; última company OK; si self y company de
      sesión → otra company (menor id) o logout. Verificar Feature.
- [x] 2.4 Test perímetro ajeno / SA no bypass. Verificar Feature.

## 3. Frontend

- [x] 3.1 Tab Empresas: ServerTable (patrón functionalities) + delete
      confirm; position/department solo lectura; sin CTA alta / sin edit.
      Verificar UI.
- [x] 3.2 i18n columnas/acciones/fecha. Verificar keys.

## 4. Docs + calidad

- [x] 4.1 Glosario membership vs Empleo TBD; `docs/modules/Admin.md` tab
      Empresas. Verificar filas.
- [x] 4.2 Pint + PHPStan + Feature afectados en verde.

## Workflow follow-up

- `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Empleo: change futuro.
