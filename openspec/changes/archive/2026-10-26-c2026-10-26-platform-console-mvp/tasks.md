## 1. Perímetro Company

- [x] 1.1 Scope/helper `Company::forUser(User)` (o equivalente) vía `user_companies`
- [x] 1.2 Aplicar en `CompanyController@table` (y edit/show si aplica sin vínculo → 403)
- [x] 1.3 Policy o abort en view/update/delete: exigir vínculo además del permiso
      (defensa en profundidad)

## 2. Elegibilidad operador

- [x] 2.1 `CompanyModuleAuthorizationController@index`: companies solo del perímetro
- [x] 2.2 `authorizeModule` / `revoke`: abort si SA no vinculado a esa company
- [x] 2.3 UX: enlace desde listado tenants → authorizations?company_id= (+ i18n)

## 3. Consola / menú

- [x] 3.1 Agrupar entradas operador (companies/tenants, authorizations, roles) bajo
      menú «Plataforma» / labels i18n para Super Admin
- [x] 3.2 Actualizar `docs/modules/Admin.md` (MVP plataforma entra; pago sigue fuera)

## 4. Tests

- [x] 4.1 Feature: SA table/list solo companies vinculadas
- [x] 4.2 Feature: authorizations selector sin companies ajenas
- [x] 4.3 Feature: authorize/revoke company no vinculada → 403
- [x] 4.4 Feature: authorize en company vinculada sigue OK (+ sync si ya cubierto)
- [x] 4.5 E2E: N/A

## 5. Calidad

- [x] 5.1 Pint + PHPStan + tests del change en verde
