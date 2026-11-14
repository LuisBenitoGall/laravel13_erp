# Design

## Context

See proposal.md — Why. Edit ya tiene `CompanySectorsPanel` en Info;
`SyncCompanySectors` + `PUT .../sectors` autorizan con
`can('update', $company)`. Pivot `company_sector` sin modelo Eloquent.
Module `empresas` hoy solo Functionality menú `companies`.

## Goals / Non-Goals

**Goals:** tab Sectores; `company-sectors.view`/`.update`; reutilizar
sync/pivot; Create sin panel.

**Non-Goals:** modelo `CompanySector`; CRUD `sectors`; directorio;
Brand; NACE seeder.

**E2E:** no aplica.

**Excepciones a standards:** ninguna.

## Decisions

1. **Functionality sin menú**  
   Crear Functionality `company-sectors` bajo Module `empresas` (label
   i18n). **No** añadir href al MenuBuilder / lista de subítems de
   menú: es permiso de tab, no entrada lateral. Canónicos
   `company-sectors.*` (+ auto `{module}.access` al crear Functionality
   si el pipeline lo hace). Seed/Realign + sync manager si aplica.  
   *Alt. descartada:* `company-sector.access` como comodín de módulo.

2. **Authz**  
   - Props Edit: `can.view_company_sectors` / `can.update_company_sectors`
     (o equivalente) vía `$user->can('company-sectors.view')` etc.  
   - Tab visible solo si `.view`.  
   - `SyncCompanySectorsRequest::authorize`: vínculo +
     `company-sectors.update` (dejar de basarse solo en Policy
     `update` Company). Perímetro `assertLinked` intacto.  
   - Sin `.update`: panel read-only (checkboxes disabled / sin submit).

3. **UI**  
   `EDIT_TABS` += `sectors`; panel actual movido; Info sin sectores.
   `?tab=sectors`.

4. **Pivot model**  
   **No** crear `CompanySector`. Directory futuro = `whereHas('sectors')`.

5. **V-01..V-20**  
   Functionality+permisos+seeder, Request/Policy gate, UI, i18n tab,
   tests Feature. Sin migración BD. Menú: Functionality sin V-08 de
   ítem nuevo (no href).

## Risks / Trade-offs

- [Roles existentes sin el permiso] → tab invisible hasta asignar;
  documentar en seed/manager sync.  
- [Doble gate ficha] → abrir Edit sigue exigiendo update company; el
  tab es capa extra.

## Migration Plan

1. Seed Functionality + permisos.  
2. Request + props can.  
3. Mover panel a tab.  
4. Tests.  
Rollback: revertir código/seed (sin BD schema).

## Open Questions

Ninguna.
