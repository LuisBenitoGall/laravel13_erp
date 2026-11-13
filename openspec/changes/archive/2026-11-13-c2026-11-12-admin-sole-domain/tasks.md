# Tasks

## 1. PHP: absorber Partners y Banks en Admin

- [x] 1.1 Mover `App\Domains\Partners\*` → `App\Domains\Admin\*` (Company,
      Actions, Controllers, Policies, Requests, Enums). Actualizar imports,
      providers, factories. Verificar autoload / Feature companies arranca.
- [x] 1.2 Mover `App\Domains\Banks\*` → `App\Domains\Admin\*`. Actualizar
      imports/providers. Verificar Feature banks.
- [x] 1.3 Eliminar carpetas `Domains/Partners` y `Domains/Banks` vacías.
      Verificar que no quedan referencias `Domains\Partners` /
      `Domains\Banks` en app/tests.

## 2. Rutas /admin

- [x] 2.1 Companies: `admin.companies.*` bajo `/admin/companies` (table,
      status, logo, resource). Quitar `partners.php`. Verificar route:list /
      Feature.
- [x] 2.2 Company-roles: de `/company/roles` a `/admin/company-roles`
      (`admin.company-roles.*`). Verificar Feature roles empresa.
- [x] 2.3 Profile self: de `/profile*` a `/admin/profile*`
      (`admin.profile.*` o nombres coherentes). Verificar Feature profile.
- [x] 2.4 Confirmar ausencia de prefijos `/partners` y `/company` en
      Functionalities; sin redirects legacy. Verificar 404 u equivalente.

## 3. Front + menú

- [x] 3.1 MenuBuilder + hardcodes Pages (Companies, logo, Index) →
      `/admin/companies`. Verificar href menú.
- [x] 3.2 Avatar / UserMenu → `/admin/profile`. AsideNav highlight.
      Verificar.
- [x] 3.3 Company-roles front → `/admin/company-roles`. Verificar.
- [x] 3.4 Mover pages Inertia `Partners/Companies` → `Admin/Companies` (o
      path Admin) y actualizar `Inertia::render`. Verificar.

## 4. Docs + standards + tests

- [x] 4.1 `module-map.md`: Admin único dominio funcional; Company/Bank en
      Admin; retirar Partners/Banks como dominios. Verificar doc.
- [x] 4.2 Fusionar fichas `Partners.md` / `Banks.md` en `Admin.md`; retirar
      o marcar absorbidas. Verificar.
- [x] 4.3 Actualizar tests Feature (partners.* → admin.companies.*,
      profile, company-roles). Suite Feature afectada en verde.
- [x] 4.4 Pint + PHPStan en verde sobre el move.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
