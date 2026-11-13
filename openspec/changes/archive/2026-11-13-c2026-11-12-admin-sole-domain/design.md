# Design

## Context

See proposal.md — Why. Hoy: `Domains/Partners` + `routes/domains/partners.php`
(`partners.*`); company-roles bajo `/company` (`company.*`); profile bajo
`/profile`; Banks en `Domains/Banks` con URL ya `/admin/banks`. MenuBuilder
apunta a `partners.companies.index`. Multi-dominio en `module-map` queda
obsoleto por decisión de producto (sencillez; v1 monolítico funcional).

## Goals / Non-Goals

**Goals:** un dominio Admin; un prefijo `/admin`; absorber Partners+Banks;
mover company-roles y profile; corte limpio; standards al día.

**Non-Goals:** entidades nuevas; redirects; tocar shell
(login/onboarding/select/switch/dashboard) salvo links rotos a profile.

**E2E:** no aplica (`e2e.md` §4).

**Excepciones a standards:** ninguna — se **corrigen** standards
(module-map / fichas) en este change.

## Decisions

1. **Un dominio**  
   Todo el código funcional absorbido vive en `App\Domains\Admin`. No
   quedan `App\Domains\Partners` ni `App\Domains\Banks`.  
   *Alt. descartada:* solo rename de URL dejando Domains separados.

2. **Rutas**  
   Unificar Functionalities en `routes/domains/admin.php` (o equivalente)
   con `prefix('admin')->name('admin.')` + middlewares por módulo como
   hoy.  
   - Companies: `admin.companies.*` → `/admin/companies`  
   - Company roles: `admin.company-roles.*` → `/admin/company-roles`  
   - Profile: `admin.profile.*` → `/admin/profile` (+ subrutas)  
   Borrar `partners.php` y el grupo `prefix('company')`. Sin redirects.

3. **Front**  
   Sustituir hardcodes y `route()`/`to_route` a los nuevos nombres.
   Pages: mover `Pages/Partners/Companies` → `Pages/Admin/Companies` (o
   path coherente bajo Admin). Inertia render paths al día.

4. **Standards**  
   `module-map.md`: Admin = único dominio funcional del ERP en Fase 0;
   Company y Bank son entidades de Admin; retirar filas Partners/Banks
   como dominios. Futuras entidades de negocio **nacen en Admin** (no se
   inventan en este change). Fusionar contenido útil de
   `docs/modules/Partners.md` y `Banks.md` en `Admin.md`; marcar o
   retirar fichas absorbidas.

5. **Specs**  
   Deltas en company-create/edit, menu-core-pillars, user-edit,
   shell-topbar, banks-catalog, account-placeholder: paths y “dominio
   Banks/Partners” → Admin + `/admin/...`.

6. **Tenancy**  
   Sin cambio de reglas: `CurrentCompany`, vínculo `user_companies`,
   Policies. Solo ubicación de código y URLs.

7. **V-01..V-20**  
   Move namespaces + rutas + i18n/menú + tests Feature de humo (index
   companies, profile, company-roles, banks) y perímetro R-TEN-05 intacto
   en companies. No migración BD.

## Risks / Trade-offs

- [Diff grande de namespaces] → mover con búsqueda sistemática
  `Partners\\` / `Banks\\`; suite Feature como red.  
- [Nombre `admin.profile` vs Functionality `profile` de Cuenta] → URL
  ficha self = `/admin/profile`; Mi cuenta SaaS sigue su ruta acordada
  (`/admin/...` cuenta si ya lo está; no reabrir account-placeholder).  
- [module-map futuro] → simplificado a Admin; se acepta a sabiendas.

## Migration Plan

1. Mover PHP Partners+Banks → Admin; actualizar imports/providers.  
2. Rutas `/admin` + borrar partners/company groups.  
3. Front + MenuBuilder.  
4. Specs/docs/standards + tests.  
Rollback: git revert (sin BD).

## Open Questions

Ninguna bloqueante.
