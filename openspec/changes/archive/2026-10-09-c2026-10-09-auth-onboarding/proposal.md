## Why

El shell multi-tenant ya existe (login, Company, roles, módulos), pero no hay alta
self-service: un visitante no puede registrarse ni recuperar la contraseña, y el login
actual **cierra sesión** si el user no tiene company (`auth.no_company`). Para cerrar
Fase 0 hace falta onboarding mínimo User → Company + reset password + legales placeholder.

## What Changes

- Registro en 2 pasos: User (email, password, name + aceptación legales) → Company
  (nif único, name|tradename, logo opcional, Active, slug auto).
- Mantener sesión tras paso 1; sin company → pantalla bloqueante de alta (no ERP).
- **BREAKING (comportamiento login):** user con 0 companies ya no se desloguea con error;
  se redirige al paso 2 de onboarding (alineado al brief).
- Reset password por email (páginas Inertia + broker Laravel).
- Vistas placeholder de aviso y términos; checkbox obligatorio en registro.
- Helper de slug único (+ timestamp) en Support; uso en CreateCompany/onboarding.
- Almacenamiento de logo (formatos jpg/webp/png/gif/svg).

## Capabilities

### New Capabilities

- `auth-registration`: registro User + onboarding Company + redirecciones sin company.
- `password-reset`: forgot/reset password por email.
- `slug-generation`: generación automática de slug con sufijo timestamp ante colisión.
- `legal-placeholders`: vistas aviso/términos + aceptación en registro.

### Modified Capabilities

- Flujo de login post-auth (comportamiento cuando no hay companies) — documentado en
  `auth-registration` / design; no hay spec main previo de “auth-session” aparte.

## Impact

- Rutas guest: register, password forgot/reset; rutas auth: onboarding company.
- `AuthenticatedSessionController` / `PostLoginRedirect` o equivalente.
- `CreateCompany` + FormRequest de onboarding (nif required+unique; slug generado).
- `app/Support` slug helper; storage logo; i18n; tests Feature (+ E2E opcional rol 40).
- Enlaces en `Login.tsx`.
