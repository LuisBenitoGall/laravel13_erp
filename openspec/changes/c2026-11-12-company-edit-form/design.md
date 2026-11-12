# Design

## Context

See proposal.md — Why. Edit actual: form plano + `members_url`;
`UpdateCompanyRequest` exige slug editable, nif nullable max 32, logo
string; `UpdateCompany` hace `update($data)` sin regenerar slug. Create ya
usa `UniqueSlug` + `ValidCompanyTaxId` max 12. Aside:
`HandleInertiaRequests` → `tenant.logo_url` desde `companies.logo`.
Patrón UI: `Users/Edit` + `RecordEditLayout` / `RecordSummary` /
`UserProfileReadout`.

## Goals / Non-Goals

**Goals:** tabs Info + Imágenes; form Info alineado Create (NIF); slug auto
en Update; logo único por file ≤500 KB; readout rectangular; sin link
Miembros; aside si sesión.

**Non-Goals:** `CompanyImage`/galería/public/featured; tab Miembros;
onboarding; pago; Create.

**E2E:** no aplica (`e2e.md` §4 — no listado como flujo crítico de ese
catálogo).

**Excepciones a standards:** ninguna.

## Decisions

1. **Layout**  
   `RecordEditLayout` + query `?tab=` (mismo patrón Users). Tabs: `info` |
   `images`. Sin `members_url` en props Edit.  
   *Alt. descartada:* form plano con secciones.

2. **Readout**  
   Componente local `CompanyProfileReadout` (Pages/Partners/…): logo
   rectangular centrado V+H (object-contain en caja fija), name,
   tradename, nif, status. No promover a catálogo hasta ≥2 usos (R-FE-02).  
   *Alt. descartada:* reutilizar `UserProfileReadout` (avatar circular +
   emails/tels).

3. **Form Info**  
   Campos: name, tradename, nif, status. Sin slug/logo. NIF =
   `ValidCompanyTaxId` + unique ignore self + max:12 + Input maxLength 12.
   Authz: Policy `update` + vínculo (`assertLinked`). PUT existente
   `partners.companies.update`.

4. **Slug en Update**  
   En `UpdateCompany`, tras validar:  
   `UniqueSlug::from($name, fn ($s) => Company::withTrashed()->where('slug',$s)->whereKeyNot($company->id)->exists())`  
   y asignar a `$data['slug']` (prohibido aceptar slug del cliente). Se
   regenera en **cada** Update guardado (brief), no solo si cambia name.  
   *Alt. descartada:* slug inmutable; regenerar solo si name cambia.

5. **Logo — un fichero, no galería**  
   Columna `companies.logo` = path relativo en disco `public`. Action
   `UploadCompanyLogo` (o nombre equivalente): valida imagen ≤512 KB
   (500 KB UI; Request max KB alineado), guarda path tipo
   `companies/{id}/logo-{uuid}.ext`, borra fichero anterior si existía,
   actualiza `logo`. Ruta dedicada POST (p. ej.
   `partners/companies/{company}/logo`) + FormRequest; no mezclar con PUT
   Info. Quitar `logo` string del UpdateCompanyRequest.  
   *Alt. descartada:* `CompanyImage` + featured (brief: un solo logo).

6. **Aside**  
   Sin cambio de contrato shared: middleware ya resuelve
   `CurrentCompany` → `logo`. Tras upload, respuesta Inertia que
   revalide/shared (back/reload) ⇒ si la company editada es la de sesión,
   el aside muestra el nuevo URL; si no, `tenant` no cambia.  
   *Alt. descartada:* evento/broadcast.

7. **CompanyForm**  
   Modo edit deja de pedir slug/logo; o Edit deja de usar el form
   compartido y monta fields en el panel Info (Create sigue con
   `mode=create`). Decisión de implementación: preferir un solo
   `CompanyForm` con `mode` limpio (create: 4 campos; edit: mismos 4 +
   status ya en create).

8. **V-01..V-20**  
   Aplica: FormRequests, Actions, Policy existente, ruta logo, i18n,
   storage, tests Feature (NIF, slug unique ignore self, upload tamaño,
   perímetro vínculo, aside/sesión vía shared tras upload). No: Module
   nuevo, menú, BelongsToCompany en Company, ServerTable, E2E §4,
   glosario nuevo (logo ya en Company).

## Risks / Trade-offs

- [Regenerar slug siempre] → URLs/bookmarks por slug (si los hubiera)
  cambian en cada save; hoy rutas usan `{company}` id — OK.  
- [UniqueSlug + self] → sin `whereKeyNot` colisiona consigo; mitigado en
  decisión 4.  
- [500 KB vs max:512 Request] → documentar bytes en Request (512) y copy
  UI “500 KB”.  
- [Members link quitado] → ruta Members sigue; acceso solo por URL hasta
  tab futuro.

## Migration Plan

1. UpdateCompanyRequest + UpdateCompany slug + NIF.  
2. UploadCompanyLogo + ruta + quitar logo del PUT.  
3. Edit UI tabs/readout/file input; quitar members_url.  
4. Tests + Partners.md.  
Rollback: revertir código (sin migración de esquema).

## Open Questions

Ninguna bloqueante (brief cerrado).
