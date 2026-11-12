# Design

## Context

See proposal.md — Why. Index actual: `ColumnDefinition` crudo; Resource sin
`created_at`; `CompanyTableRequest` sortable ya incluye `created_at` pero
filterable solo `search`. `CompanyStatus`: `draft` | `active` | `closed` con
transiciones Draft→Active, Active→Closed. Perímetro: `Company::forUser` +
Policy `companies.*` + `assertLinked` en mutaciones. Autorizaciones SA:
columna HTML → `/admin/company-module-authorizations?company_id=`.

## Goals / Non-Goals

**Goals:** Index canónico; fecha alta; toggle/Edit/Delete/Autorizaciones SA;
resource + table whitelist; status endpoint con transiciones.

**NonGoals:** ficha User / `user_companies` tab; Empleo; quitar Autorizaciones;
rediseñar Create/Edit Company (salvo consumir status si hace falta).

**E2E:** no aplica (`e2e.md` §4).

**Excepciones a standards:** ninguna.

## Decisions

1. **Capability `mis-empresas-index`**  
   Comportamiento durable del Index; `company-tenancy` solo reafirma
   perímetro del listado/table.

2. **Columnas datos**  
   `name`, `tradename`, `nif`, `created_at` (fecha alta). Sin `slug`. Sin
   columna Estado. `status` sigue en JSON para el toggle.  
   Header filters: string en name/tradename/nif; date en created_at.

3. **Fecha**  
   `companies.created_at` ISO en API; front `valueKind: 'date'`,
   `headerFilterKind: 'date'`, `fmt.date(locale)` (dd/mm/yyyy es/ca).

4. **ServerTable**  
   `persistKey="partners.companies"`, `exportable`, `rowActions`. Breadcrumb
   `optionKey: 'mis_empresas'` (alineado al label de menú).

5. **Toggle status (enum 3 estados)**  
   UI binaria sobre el enum:
   - `checked` ⇔ `status === active`
   - ON (true): si `draft` → `active` (transición permitida); si ya
     `active` no-op; si `closed` → **rechazo** (sin transición Closed→*).
   - OFF (false): si `active` → `closed`; si `draft` no-op o rechazo.
   Endpoint dedicado `PATCH .../companies/{company}/status` (JSON como
   Modules) + Action que valida `transitions()`. Authz: `update` + linked.  
   *Alt. descartada:* toggle libre a cualquier string (rompe enum).

6. **Acciones (orden)**  
   1) toggle · 2) Edit → `partners.companies.edit` · 3) Autorizaciones
   (solo si `operator`/SA, navigate query `company_id`) · 4) Delete
   confirm → destroy existente. Quitar columna HTML Autorizaciones.

7. **TableRequest**  
   Ampliar `filterable`/`sortable` a name, tradename, nif, created_at
   (y status si hace falta para export/internal). Resource: añadir
   `created_at`.

8. **Docs**  
   `docs/modules/Partners.md`: Index Mis Empresas canónico.

## Risks / Trade-offs

- [Draft vs toggle off] → Draft aparece unchecked; usuario activa con ON.
  Documentado.  
- [Closed irreversible vía toggle] → acorde a `transitions()`; reabrir =
  change futuro / Edit form.  
- [Autorizaciones solo SA] → mismo criterio `operator` que hoy.

## Migration Plan

1. Resource + TableRequest + status endpoint/Action.  
2. Index frontend canónico.  
3. Tests + Partners.md.  
Rollback: revertir Index (sin migración BD).

## Open Questions

Ninguna.
