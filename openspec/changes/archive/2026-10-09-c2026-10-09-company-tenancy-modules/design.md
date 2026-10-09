## Context

Admin (`c2026-07-17-admin-catalog-permissions`) dejó Module/Functionality, Spatie Teams,
Super Admin, MenuBuilder solo por permisos y `EnsureModuleIsActive` stub. Support ya
tiene `CurrentCompany` / `SetCurrentCompany` / `BelongsToCompany` (fixtures). Brief
explore `docs/prompts/c2026-10-09-company-tenancy-modules.md`. Modelo mental = v1
(Company + UserCompanies + CompanyModules); todas las companies iguales.

## Goals / Non-Goals

**Goals:** Company shell + user_companies + sesión/modal; tres capas de módulos; UI
tarjetas; menú/middleware reales; seed usable; cerrar excepción R-AUT-08.

**Non-Goals:** CustomerProvider; roles por empresa; Platform/onboarding/pago real;
engorde Company; NIF histórico/obras/contactos.

## Decisions

1. **`Company` en `App\Domains\Partners`** (casa del module-map). Alternativa descartada:
   `Admin\Company` temporal — obliga a mover el modelo en Fase 1. Consecuencia:
   dependencia **Admin → Partners** (lectura) declarada en `module-map.md`.

2. **`Company` no usa `BelongsToCompany`**: es el tenant, no un registro scoped a otro
   tenant. Arch ignoring explícito. Alternativa descartada: fingir company_id en
   companies.

3. **Dos persistencias de capas 2–3**  
   - Elegibilidad: tabla de autorización `company_module_authorizations` (company_id,
     module_id) — soft delete alineado a R-BD-03 / v1 company_modules.  
   - Activación: `company_modules` (company_id, module_id) — soft delete como v1.  
   Alternativa descartada: un solo boolean en una tabla — mezcla “puede” y “está
   encendido” y complica el estado gris de la UI.

4. **`ModuleLevel` × elegibilidad**  
   - **Basic** → al crear/asegurar company con cuenta activa, autorizar automáticamente
     todos los Module Active de level Basic.  
   - **Optional** y **AdminOnly** → requieren autorización explícita Super Admin (misma
     mecánica Capa 3).  
   **AdminOnly = misma puerta que Optional** (opción A del brief). Alternativa B
   descartada: “solo companies plataforma” — introduciría un tipo especial de company y
   contradice “todas iguales”. El Module `admin` se autoriza en seed a la(s) company(s)
   de trabajo.

5. **Post-login / middleware**  
   - Tras autenticar: 0 companies activas vinculadas → denegar ERP; 1 → set sesión; N →
     página/modal bloqueante hasta elegir.  
   - `SetCurrentCompany` (y cambio de empresa) valida pertenencia a company activa.  
   - Super Admin: `Gate::before` sigue bypass de permisos; **no** bypass de pertenencia
     ni de scope de datos.

6. **Quién gestiona UI**  
   - Capa 3 (autorizar): solo Super Admin.  
   - Capa 2 (encender/apagar): usuarios con permiso de la Functionality de “módulos de
     empresa” (+ Super Admin). Sin roles por empresa aún.

7. **E2E Pest browser**: el change toca login + modal + menú — incluir tasks E2E
   mínimas (rol 40) para login 1-empresa y rechazo sin empresa; modal N-empresas si el
   entorno lo permite. Detalle en tasks.

## Excepciones a standards

Ninguna nueva permanente. Se **retira** la excepción R-AUT-08 parcial del design Admin
(menú sin CompanyModules): este change la cumple. R-AUT-05 deja de ser stub.

## Risks / Trade-offs

- **[Riesgo]** Auto-autorizar Basic al crear company / al activar Module Basic nuevo —
  hay que definir el gancho (observer/Action). **Mitigación**: Action única
  `AuthorizeBasicModulesForCompany` + al crear Module Basic, autorizar companies activas
  existentes (o documentar job; preferir Action síncrona en este change).
- **[Riesgo]** Super Admin sin vínculo a company no entra al ERP. **Mitigación**: seed
  siempre vincula superadmin a ≥1 company.
- **[Trade-off]** AdminOnly ≡ Optional en elegibilidad: pierde semántica “solo
  plataforma”; se recupera con autorización selectiva en seed/ops.

## Migration Plan

Orden apply: Company + factories → user_companies → session/middleware/modal →
authorizations + company_modules + reglas level → UI tarjetas → MenuBuilder +
EnsureModuleIsActive → seeds → tests/E2E. Rollback = revertir change.

## Open Questions

Ninguna bloqueante. AdminOnly cerrado como A (≡ Optional en Capa 3). Revisión humana
(Luis) del DoR antes de apply.
