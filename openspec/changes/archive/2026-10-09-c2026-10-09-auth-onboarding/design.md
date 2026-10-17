## Context

Login + tenancy + company-roles archivados. Hoy
`AuthenticatedSessionController@store` con 0 companies hace **logout** +
`auth.no_company`. El brief exige sesión persistente y paso 2 bloqueante. `CreateCompany`
ya recibe `User $creator` y asigna manager/Basic/sync. `PostLoginRedirect` distingue Super
Admin vs dashboard.

## Goals / Non-Goals

**Goals:** register 2 pasos; redirect sin company → onboarding company; reset password;
slug helper; logo opcional; legales placeholder; enlaces desde login.

**NonGoals:** email verification; Platform/billing; roles custom; wizard vision; textos
legales definitivos.

## Decisions

1. **Cambio de login sin company**  
   Dejar de desloguear. Si `activeCompanies()` vacío → `redirect()->route('onboarding.company')`
   (nombre exacto a fijar) **con sesión**. Logout sigue disponible. Alternativa descartada:
   mantener logout — contradice el brief.

2. **Registro User**  
   Action `RegisterUser` (o controller delgado + Action): crea User, `Auth::login`,
   redirect onboarding company. Validación: email unique, password confirmed, name,
   `accepted:terms` (o boolean accepted). Sin Fortify completo salvo que simplifique;
   preferir controladores Inertia propios alineados al login actual.

3. **Onboarding Company**  
   FormRequest dedicado (`OnboardCompanyRequest`):  
   - `nif` required + unique (`companies.nif` where null deleted_at)  
   - `name` required_without:tradename; `tradename` required_without:name  
   - `logo` nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:…  
   - status forzado Active  
   - slug vía helper, no input  
   Action: store logo → `CreateCompany` existente → `SetCompanySession` →
   `PostLoginRedirect`.  
   **CRUD admin** (`StoreCompanyRequest`): alinear `nif` a unique cuando se informa;
   onboarding siempre required. No relajar unique en registro.

4. **`UniqueSlug` en `App\Support\Strings` (o similar)**  
   `from(string $source, callable|string $existsCheck): string` — base Str::slug;
   si existe, `{base}-{timestamp}` (+ reintento si hiciera falta). Usado en onboarding;
   CreateCompany/admin pueden adoptarlo en el mismo change cuando generen slug.

5. **Logo**  
   Disco `public` (o `local` + enlace), path `companies/{id}/logo.{ext}` o
   `companies/logos/{uuid}.ext` antes de tener id (store temporal luego move). URL pública
   para AppLayout hueco de logo. Tamaño máximo razonable (p. ej. 2MB) en Request.

6. **Password reset**  
   Tablas `password_reset_tokens` (Laravel default si no están); Notification/Mail
   configurada vía `.env` (log driver válido en local/testing). Páginas
   `Auth/ForgotPassword`, `Auth/ResetPassword`. Enlace en Login.

7. **Legales**  
   Rutas guest `legal.notice`, `legal.terms` → Inertia placeholder i18n. Checkbox en
   Register con links. No CMS.

8. **Middleware**  
   Grupo `auth` sin `company`: onboarding + logout + select.  
   Grupo `auth`+`company`: ERP.  
   Middleware o gate en rutas ERP que redirija a onboarding si 0 companies (defensa si
   alguien salta el login path).

9. **E2E (rol 40)**  
   Flujo: register → create company → ve dashboard/AppLayout; forgot password página
   visible. Mail fake en tests Feature.

## Excepciones a standards

Ninguna. Branding logo ya previsto en frontend.md §2.

## Risks / Trade-offs

- **[Riesgo]** Mail en local: reset “no llega”. **Mitigación:** `MAIL_MAILER=log` + tests
  con `Notification::fake()`.
- **[Riesgo]** SVG upload XSS si se sirve inline. **Mitigación:** servir como fichero
  estático con content-type adecuado; no embeber SVG crudo en HTML sin sanitizar.
- **[BREAKING]** Clients/scripts que dependían de `auth.no_company` en login dejan de
  ver ese error — pasan a redirect onboarding.

## Migration Plan

Orden: slug helper → register + legales → cambiar login empty-companies → onboarding
company + logo → password reset → i18n/tests/E2E. Rollback = revertir change.

## Open Questions

Ninguna bloqueante. Revisión humana (Luis) del DoR antes de apply.
