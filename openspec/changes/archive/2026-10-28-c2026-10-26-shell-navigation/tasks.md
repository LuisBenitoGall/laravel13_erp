## 1. Middleware / checker (bypass AdminOnly SA)

- [x] 1.1 Extender `CompanyModuleActivationChecker` (o `EnsureModuleIsActive`): Super Admin
      + Module Active + `ModuleLevel::AdminOnly` → permitir; Basic/Optional sin cambio
- [x] 1.2 Feature: SA + company sin `admin` encendido → GET ruta `module:admin` no 403 por
      módulo; no-SA o Basic/Optional inactivo → sigue 403

## 2. MenuBuilder

- [x] 2.1 Anteponer ítem Dashboard (href `dashboard`, label i18n snake_case) siempre que
      haya company de sesión / acceso ERP
- [x] 2.2 Para SA: construir bloque Configuración con Functionalities Active de Modules
      AdminOnly Active **sin** filtro authorized∩enabled; unificar/eliminar split
      «Plataforma» bajo Configuración
- [x] 2.3 Menú tenant: seguir R-AUT-08; mapa estático slug→grupo `cuenta`|`empresa`|default
      solo para funcionalidades existentes (p. ej. `company-roles` → empresa); sin maestros
      inventados
- [x] 2.4 Feature: SA sin admin encendido → menú tiene Dashboard + Configuración; no-SA →
      sin Configuración operador; manager con Basic elegible → ítems + Dashboard

## 3. AppLayout / branding / i18n

- [x] 3.1 Aside: zona logo ERP (config/asset placeholder) → logo company (`tenant.logo_url`)
      → menú; props Inertia si faltan
- [x] 3.2 Claves i18n snake_case (`dashboard`, `configuracion`, grupos) en es/en/ca
- [x] 3.3 Smoke visual/Feature o assert de props: logos presentes en shared/layout
- [x] 3.4 UI aside: principales homogéneos; grupos dropdown plegado por defecto;
      subpuntos con jerarquía CSS (sin cambiar payload MenuBuilder)
- [x] 3.5 UI aside: ítem/subítem activo marcado (`aria-current` + fondo); grupo padre
      auto-desplegado si un hijo coincide con la URL

## 4. Post-login / pertenencia SA

- [x] 4.1 `PostLoginRedirect`: eliminar destino SA → `admin.modules.index`; todos →
      `dashboard` (tras company)
- [x] 4.2 Confirmar SA sin company → onboarding alta empresa (mismo carril que resto);
      Feature: SA sin vínculo → redirect create; SA con company → dashboard sin 403 admin

## 5. Seed ACME

- [x] 5.1 Reemplazar/ajustar seed demo → company ACME (name ACME, nif `00000000A`, slug
      `acme`); vincular Super Admin config; authorize/enable workspace Basic; no exigir
      encender `admin`
- [x] 5.2 Actualizar tests que hardcodeen `demo` / Demo Company (p. ej. LoginTest)
- [x] 5.3 Feature/Unit seed: tras seeders → ACME + SA vinculado (idempotente)

## 6. Docs / calidad

- [x] 6.1 Nota breve en `docs/modules/Admin.md` (o ficha shell) sobre Configuración SA +
      bypass AdminOnly; perímetro datos intacto
- [x] 6.2 E2E Pest browser: el change toca login+menú (e2e.md §4).
      `tests/Browser/Auth/LoginAsSuperAdminSeesConfigurationTest.php` (SA → Configuración,
      admin apagado, entra a módulos sin 403). Destino post-login de
      `LoginWithOneCompanyTest` y `LoginWithMultipleCompaniesTest` alineado a `dashboard`.
      `LoginAsManagerSeesBasicMenuTest` afirma el ítem visible (`Catálogo demo`) y que no hay Configuración.
- [x] 6.3 `vendor/bin/pint`, PHPStan y suite Feature afectados en verde

## Vinculaciones (module-playbook §6) — N/A o cubierta

- V-09 módulo activable: N/A (no nuevo módulo; excepción documentada en menu-visibility)
- V-01..V-08 / políticas nuevas: N/A (sin entidad nueva)
- R-TEN-05: N/A entidad nueva; perímetro companies ya cubierto — no ampliar listados
