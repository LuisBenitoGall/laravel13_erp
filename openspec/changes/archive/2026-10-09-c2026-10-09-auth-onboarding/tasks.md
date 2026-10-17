## 1. Slug helper

- [x] 1.1 Implementar helper Support de slug único (base + sufijo timestamp ante colisión)
- [x] 1.2 Unit test de colisión / unicidad

## 2. Registro User + legales

- [x] 2.1 Rutas guest register + Action/controller; validación email/password/name +
      aceptación términos
- [x] 2.2 Página Inertia Register + i18n; enlaces a aviso/términos
- [x] 2.3 Vistas placeholder `legal.notice` y `legal.terms` (Inertia) + rutas
- [x] 2.4 Tras register: login + redirect a onboarding company (no ERP)
- [x] 2.5 Feature tests: registro ok; sin aceptación falla; email duplicado falla

## 3. Login sin company → onboarding (BREAKING)

- [x] 3.1 Sustituir logout+`auth.no_company` por redirect a onboarding company con sesión
- [x] 3.2 Defensa en rutas ERP (`company` middleware o equivalente): 0 companies →
      onboarding
- [x] 3.3 Feature test: login user sin company llega a onboarding autenticado
- [x] 3.4 Enlaces en Login hacia register y forgot password

## 4. Onboarding Company + logo

- [x] 4.1 FormRequest onboarding: nif required+unique (no trashed); name|tradename;
      logo mimes; status Active; slug vía helper
- [x] 4.2 Store logo en disco público; pasar path a CreateCompany / update tras create
- [x] 4.3 Página Inertia onboarding company; SetCompanySession + PostLoginRedirect al
      terminar
- [x] 4.4 Alinear unique de nif en Store/UpdateCompanyRequest del CRUD cuando nif viene
      informado (sin relajar onboarding)
- [x] 4.5 Feature tests: alta ok como manager; nif duplicado; logo inválido; sin logo ok

## 5. Password reset

- [x] 5.1 Rutas + controllers forgot/reset (broker Laravel); mail/notification
- [x] 5.2 Páginas Inertia ForgotPassword / ResetPassword + i18n
- [x] 5.3 Feature tests con Notification::fake / Password::broker

## 6. Calidad y DoD

- [x] 6.1 `composer test`, Pint, PHPStan verdes
- [x] 6.2 (Rol 40) E2E: register → company → entra AppLayout; páginas legales y forgot
      visibles — o N/A documentado
      > Browser: `RegisterOnboardEntersAppTest`, `LegalPlaceholdersVisibleTest`,
      `ForgotPasswordPageVisibleTest`. Suite Browser verde
      (`php artisan test --testsuite=Browser`). Ajuste UI mínimo: onboarding
      solo usa `forceFormData` si hay logo (multipart vacío rompía el body).
- [x] 6.3 `openspec validate c2026-10-09-auth-onboarding` limpio
