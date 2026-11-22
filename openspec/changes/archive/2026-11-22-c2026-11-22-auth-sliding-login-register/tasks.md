# Tasks

## 1. UI SlidingAuth

- [x] 1.1 Crear página Inertia `Auth/SlidingAuth` (prop `mode`) con
  panel/overlay deslizante, forms login+register (`useForm` → POST
  existentes), overlay teal ERP, i18n CTA/legales/forgot; verificar
  visual + `prefers-reduced-motion`
- [x] 1.2 Controllers Login/Register renderizan SlidingAuth con mode;
  toggle `router.visit` sincroniza URL; retirar UI antigua de
  Login.tsx/Register.tsx (o reexport); verificar Feature GET mode +
  login/register existentes verdes

## 2. Layout / DoD

- [x] 2.1 AuthLayout: canvas/tokens sin romper Forgot/Reset/Onboard/
  SelectCompany estáticos; LanguageSwitcher; verificar esas páginas OK
- [x] 2.2 Claves i18n es+en+ca; assert fuente/testid; E2E N/A; Pint/
  build si aplica

## Workflow follow-up

- Revisión Luis → `/opsx:apply` → 25 → 30 archive.
