# Design

## Context

See proposal.md — Why. Catálogos hermanos: Units/Currencies/Banks (global,
Configuración, ServerTable+modales). Translations de registro: Spatie
`HasTranslations` + `LangTabs` (patrón Module). v1 `company_sectors` era
FK a `categories` — **no** portar esa semántica; Sector es catálogo propio.

## Goals / Non-Goals

**Goals:** Sector global + i18n; CRUD Configuración; pivot N:N; atribución
desde Edit Company; menú/perms.

**Non-Goals:** Brand; UTE; jerarquía Company; centros coste; portal.

**E2E:** no aplica (`e2e.md` §4).

**Excepciones a standards:** ninguna.

## Decisions

1. **Modelo Sector**  
   Tabla `sectors`: `id`, `slug` unique, `name` JSON (translatable),
   `status` string + enum `SectorStatus` Active/Inactive, soft deletes,
   timestamps. Sin `company_id`. Slug auto `UniqueSlug` desde name
   (locale default).  
   *Alt. descartada:* name solo string sin translations; reusar categories.

2. **CRUD**  
   Espejo Units: Index ServerTable (name traducido, Acciones
   toggle→Edit→Delete; sin columna Estado); Create/Edit modales con
   LangTabs (name por locale; default required); Active-first+name.
   Functionality `sectors` bajo Module `configuracion`; permisos
   canónicos `sectors.*` + Policy. Rutas `/admin/sectors`. SA vía capas
   AdminOnly como el resto de Configuración.

3. **Pivot**  
   `company_sector` (o `company_sectors` v2): `company_id`, `sector_id`,
   unique pair, FKs. `Company::sectors()`, `Sector::companies()`.
   Sync vía Action `SyncCompanySectors` (lista de ids Active existentes).

4. **Atribución en Edit Company**  
   Control multi-select (sectores Active del catálogo) en Edit — tab o
   bloque en Info (**decisión:** bloque/sección en tab Info o tab ligero
   `sectors`; preferir **sección en Info** para no multiplicar tabs;
   si Info queda cargado, tab `sectors`). Authz `companies.update` +
   vínculo. Create sin sectores (null/vacío al alta).

5. **Seeder**  
   Functionality+permisos+menú via Realign/seed; datos Sector: puñado
   inicial con ES/EN/CA o vacío documentado — **decisión:** seeder con
   sectores genéricos comunes (construcción, comercio, industria, …)
   traducidos; IDs no críticos (no hay v1 Sector).

6. **Glosario / ficha**  
   `Sector` / Sectores. Admin.md fila catálogo + atribución Company.

7. **V-01..V-20**  
   Migraciones, modelo/factory, Policy, Actions, FormRequests, rutas,
   i18n UI, menú V-07/V-08, tests Feature (CRUD, translations, sync,
   perímetro company). E2E N/A. R-TEN-05 N/A en Sector (global); sí en
   sync solo companies vinculadas.

## Risks / Trade-offs

- [Nombre pivot `company_sectors` vs v1] → usar `company_sector` singular
  table Laravel o `company_sectors` limpia (v2 no migra esa tabla v1).
  **Decisión:** `company_sector` (convención belongToMany default) o
  `company_sectors` explícita sin columnas category — OK.  
- [Sector Inactive aún pivoteado] → sync solo ofrece Active; pivotes
  huérfanos de Inactive se muestran o se limpian en sync — **decisión:**
  al guardar sync reemplaza set; Inactive no seleccionables; pivotes a
  Inactive pueden quedar hasta próximo sync (aceptado).

## Migration Plan

1. Migraciones + modelo + seeder Functionality.  
2. CRUD backend/UI.  
3. Pivot + Sync + Edit Company.  
4. Docs/tests.  
Rollback: drop tables / quitar Functionality.

## Open Questions

Ninguna bloqueante.
