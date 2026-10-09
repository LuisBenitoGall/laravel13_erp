## 1. Company shell (Partners)

- [x] 1.1 Migración `companies`: name, slug (unique), tradename, nif, status, logo
      nullable; auditoría; soft deletes (R-BD-03)
- [x] 1.2 Modelo `App\Domains\Partners\Models\Company` + enum status + factory; **sin**
      `BelongsToCompany`; arch ignoring documentado
- [x] 1.3 Policy/FormRequests/Actions/Resource/Controller mínimos (CRUD Admin o Partners
      según rutas); i18n es/en
- [x] 1.4 Feature tests CRUD + autorización; no test R-TEN-05 de aislamiento “hijo”
      (Company es el tenant)

## 2. Pertenencia user_companies y sesión

- [x] 2.1 Migración `user_companies` (user_id, company_id, unique pair); **sin** soft
      delete
- [x] 2.2 Relación User↔Company; helper/query “companies activas del usuario”
- [x] 2.3 Post-login: 0 → denegar ERP; 1 → set `current_company_id`; N → redirigir a
      selección bloqueante
- [x] 2.4 UI modal/página bloqueante de selección de empresa + endpoint para fijar sesión
- [x] 2.5 `SetCurrentCompany` / switcher: validar pertenencia a company activa
- [x] 2.6 Tests: 0/1/N empresas; rechazo de company ajena; Super Admin sin bypass de
      pertenencia

## 3. Elegibilidad y activación de módulos (Admin)

- [x] 3.1 Migración `company_module_authorizations` (company_id, module_id; soft deletes)
- [x] 3.2 Migración `company_modules` (company_id, module_id; soft deletes) — activación
- [x] 3.3 Actions: autorizar/desautorizar (Super Admin); encender/apagar (permiso +
      Super Admin); `AuthorizeBasicModulesForCompany`
- [x] 3.4 Al crear company (cuenta activa): auto-autorizar Modules Active level Basic
- [x] 3.5 Al crear/activar Module Basic: autorizar companies activas existentes (Action)
- [x] 3.6 Optional y AdminOnly: solo vía autorización Super Admin (misma mecánica)
- [x] 3.7 Tests de las tres capas (Inactive global; no autorizado gris; Basic default;
      encendido/apagado)

## 4. UI módulos de empresa + Functionalities

- [x] 4.1 Functionality + permisos para gestionar módulos de empresa (seed); menú “Mi
      cuenta / Módulos” o bajo admin según design de rutas
- [x] 4.2 Página rejilla de tarjetas (color/icono/check; no autorizados en gris)
- [x] 4.3 UI Super Admin para marcar autorización company↔module (mínima pero usable)
- [x] 4.4 i18n es/en

## 5. MenuBuilder y EnsureModuleIsActive (cierra R-AUT-08)

- [x] 5.1 `MenuBuilder`: filtrar Active + autorizado + encendido + reglas de permiso
      existentes
- [x] 5.2 `EnsureModuleIsActive`: dejar de ser stub; 403/redirect si falla la triple
      condición
- [x] 5.3 Tests menú y ruta bloqueada con módulo apagado / no autorizado / Inactive
- [x] 5.4 Actualizar spec main `menu-visibility` vía archive; retirar excepción documentada
      en ficha Admin

## 6. Seeds, fichas y mapa

- [x] 6.1 Seed ≥1 company activa; vincular superadmin; autorizar Basic + Module `admin`
      (AdminOnly) a esa company; encender `admin` (y Basic necesarios)
- [x] 6.2 DatabaseSeeder / AdminCatalogSeeder cableados
- [x] 6.3 Actualizar `docs/modules/Admin.md` y `Partners.md` a “como quedó”
- [x] 6.4 Confirmar `module-map.md` Admin→Partners (ya propuesto)

## 7. Calidad, E2E y DoD

- [x] 7.1 `composer test` / Pint / PHPStan verdes
- [x] 7.2 `module:audit` sobre entidades tocadas
- [x] 7.3 E2E (rol 40): login con 1 empresa entra al AppLayout; usuario sin empresa no
      entra al ERP; (si aplica) flujo N empresas con modal
      > Cubierto en `tests/Browser/Auth/LoginWithOneCompanyTest.php`,
      `LoginWithoutCompanyTest.php`, `LoginWithMultipleCompaniesTest.php`.
      Suite Browser verde (`php artisan test --testsuite=Browser`).
- [x] 7.4 `openspec validate c2026-10-09-company-tenancy-modules` limpio
