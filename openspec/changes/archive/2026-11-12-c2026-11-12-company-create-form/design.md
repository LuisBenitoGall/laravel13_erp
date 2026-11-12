# Design

## Context

See proposal.md — Why. `CompanyForm` compartido Create/Edit expone slug,
logo y nif max 32; `StoreCompanyRequest` exige slug unique; `CreateCompany`
hace `create($data)` sin generar slug. Ya existen `UniqueSlug` y spec
`slug-generation`. Redirect a Edit ya en `CompanyController::store`.
NIF formato: v1 `AppServiceProvider::validateNif` (NIF/NIE/CIF/EIN); v2
aún no tiene `NifResolver` / `CompanyIdentifier` (visión futura).

## Goals / Non-Goals

**Goals:** Create mínimo; slug auto; NIF unique+formato+maxLength; status;
redirect Edit; kit Form + R-FE-08.

**Non-Goals:** paso de pago; port completo CompanyIdentifier/NifResolver
histórico por vigencia; rediseñar Edit completo (salvo no romper share del
form); cambiar onboarding UX.

**E2E:** no aplica (`e2e.md` §4).

**Excepciones a standards:** ninguna.

## Decisions

1. **Campos Create**  
   `name` required, `tradename` nullable, `nif` required, `status` required
   (`CompanyStatus`). Sin `slug` ni `logo` en request/UI de Create.

2. **Slug**  
   En `CreateCompany`, antes del create:  
   `UniqueSlug::from($name, fn ($s) => Company::withTrashed()->where('slug',$s)->exists())`  
   (o equivalent soft-delete policy del proyecto). No aceptar slug del
   cliente en Store.  
   *Edit:* fuera de alcance estricto; si el form es compartido, modo
   `create` oculta slug/logo; Edit puede seguir mostrando slug RO o
   editable — **decisión:** en Create no se muestra; Edit no se rediseña
   en este change (si comparte componente, `mode="create"|"edit"`).

3. **NIF formato**  
   Extraer regla/validador PHP (p. ej. `Rules\ValidSpanishTaxId` o
   `Support\Nif\NifValidator`) portando el algoritmo de v1
   `validateNif` (sin depender de `session('companySettings')`: en Create
   **siempre** validar si nif no vacío; aquí nif required ⇒ siempre).  
   Unique: `Rule::unique('companies','nif')->whereNull('deleted_at')`.  
   *No* es el NifResolver histórico por fecha de documento.

4. **maxLength NIF**  
   Formatos v1 ≤ 9–10 chars; UI + Request **`max:12`** (margen guiones /
   normalización). Name/tradename: default 255. Status: Select enum.

5. **UI**  
   Create usa Field/Input/Select del kit; submit con Button shadcn (dejar
   de usar `<button>` crudo si el form create se toca). Breadcrumb
   `mis_empresas` / `empresa_nueva` alineado al menú.

6. **Pago futuro**  
   Anotar en NonGoals / Partners.md; no hook en código.

7. **V-01..V-20**  
   FormRequest, Action, i18n, tests Feature (alta, slug auto/colisión, nif
   invalid/duplicate, redirect edit). No: menú nuevo, migración BD (salvo
   que nif column max < 12 — hoy 32 OK).

## Risks / Trade-offs

- [NIF extranjero no ES/EIN] → rechazado hasta ampliar validador. Aceptado
  (v1 igual).  
- [Edit aún con slug editable] → deuda menor; Create cumple spec.  
- [tradename empty + name] → slug from name only.

## Migration Plan

1. Validador NIF + StoreCompanyRequest + CreateCompany slug.  
2. CompanyForm mode create.  
3. Tests + docs.  
Rollback: revertir form/request (sin migración).

## Open Questions

Ninguna.
