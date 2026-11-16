# Tasks

## 1. Functionality + menú + docs

- [x] 1.1 Seed/Realign: Functionality `company-settings` bajo `empresas`;
      permisos canónicos; `.view`/`.update` a roles básicos acordados.
      Verificar BD/seed.
- [x] 1.2 MenuBuilder: Configuración de la empresa →
      `/admin/company-settings`. Verificar menú Feature.
- [x] 1.3 Glosario / Admin.md (hub + onboarding; protocolo pasos futuros).
      Verificar docs.

## 2. Readiness + shell (modal / banner)

- [x] 2.1 Servicio/helper readiness (nif + logo + ≥1 workplace Active) +
      shared Inertia props. Verificar Feature/unit.
- [x] 2.2 Flash `show_modal` tras CreateCompany (onboarding + Mis
      empresas); dismiss «Ahora no» (sesión). Verificar Feature.
- [x] 2.3 Banner en AppLayout si incomplete; i18n. Verificar UI/Feature.

## 3. Hub + wizard

- [x] 3.1 Ruta/controller hub Index; Policy. Verificar 403/200 Feature.
- [x] 3.2 Page hub: progreso + tabs/pasos Identidad | Sedes; modo
      `wizard=1` stepper. Verificar UI.
- [x] 3.3 Paso identidad: update name/tradename/nif + logo (reutilizar
      Actions/rutas). Verificar Feature.
- [x] 3.4 Paso sedes: listado estado + enlace Create workplace con
      retorno al hub; paso OK con ≥1 Active. Verificar Feature.
- [x] 3.5 i18n es/en/ca + Pint + PHPStan + Features en verde.

## 4. Correcciones 25 (HIGH / MEDIUM / LOW)

- [x] 4.1 HIGH: Mis empresas store → `SetCompanySession` a company nueva +
      `show_modal`; Feature Inertia incomplete/modal sobre la nueva.
- [x] 4.2 MEDIUM: wizard `onSuccess` identidad no avanza a Sedes sin logo.
- [x] 4.3 LOW: quitar `can.update_settings` unused; login incompleta → solo
      banner (`show_modal` false).

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica (design).
- Nuevos pasos de onboarding: solo si Luis lo pide en el change del
  módulo (preguntar siempre).
