# Tasks

## 1. Backend Create

- [x] 1.1 Validador NIF (puerto lógica v1) + `StoreCompanyRequest` (name,
      tradename, nif required unique+formato max:12, status; sin slug/logo).
      Verificar Feature rechazo formato/duplicado.
- [x] 1.2 `CreateCompany` genera slug con `UniqueSlug` desde name. Verificar
      Feature alta + colisión slug.
- [x] 1.3 Redirect a Edit intacto tras store. Verificar Feature.

## 2. Frontend Create

- [x] 2.1 `CompanyForm` modo create: solo name, tradename, nif
      (maxLength 12), status; sin slug/logo. Verificar UI/source.
- [x] 2.2 Breadcrumb/i18n Create alineados (mis_empresas). Verificar keys.

## 3. Docs + calidad

- [x] 3.1 `docs/modules/Partners.md`: Create mínimo + pago futuro NonGoal.
      Verificar fila.
- [x] 3.2 Pint + PHPStan + Feature Partners create en verde.

## Workflow follow-up

- `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Pago por nueva empresa: change futuro.
