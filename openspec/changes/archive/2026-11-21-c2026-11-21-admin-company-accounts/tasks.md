# Tasks

## 1. Datos CompanyAccount

- [x] 1.1 Migration `company_accounts` + model `CompanyAccount`
  (BelongsToCompany, softDeletes, Auditable) + enum
  `CompanyAccountStatus` + factory; verificar migrate OK
- [x] 1.2 Actions `AssignDefaultCompanyAccount` y `RenewCompanyAccount`
  (≤1 Active; Cancelled/Expired según design); verificar unit/Feature
  invariante y R-TEN-05

## 2. Alta Company + backfill + job

- [x] 2.1 Enganchar AssignDefault en `CreateCompany` (misma TX; falla si
  no hay Account default); verificar Feature alta Company crea Active
- [x] 2.2 Comando/seeder backfill companies Active sin fila; verificar
  idempotencia
- [x] 2.3 Command `company-accounts:expire` + Schedule diario; verificar
  Feature/unit Active→Expired por end_date

## 3. Gate 403 + whitelist

- [x] 3.1 Middleware `EnsureCompanyAccountIsActive` en grupo `company` +
  página/props 403 i18n + CTA a `account.show`; whitelist Mi cuenta /
  renew / select / logout; verificar Feature 403 en dashboard y 200 en
  `/account` con cuenta caducada

## 4. Mi cuenta UI + renovación

- [x] 4.1 Sustituir ComingSoon: histórico ServerTable (R-FE-04 fechas/
  price) + resumen Active; endpoint table acotado a CurrentCompany;
  verificar Feature + assert fuente
- [x] 4.2 Formulario/modal renovación (POST con `profile.update` si
  existe; si no, alinear permiso y documentar); verificar renew deja
  ≤1 Active
- [x] 4.3 Redirect `admin.accounts.renewal` → `account.show`; verificar
  redirect

## 5. Specs registro / DoD

- [x] 5.1 Glosario CompanyAccount; ficha Admin; delta menu-core; V-16/
  V-18/V-21; E2E N/A (o task 40 si Luis pide)
- [x] 5.2 Pint + PHPStan + tests afectados en verde

## Workflow follow-up

- Revisión Luis → `/opsx:apply` → 25 → 30 archive.
