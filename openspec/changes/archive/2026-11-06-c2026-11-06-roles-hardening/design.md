# Design

## Context

Brief: `docs/prompts/c2026-11-06-roles-hardening.md`. Change 1
`permission-view-dashboard` archivado. Estado pre: Index AND manager; copy
tenant vivo; Create plataforma con `permissions`; copy plataforma sin
filtro; materialización sin forzar `universal=false`.

## Goals / Non-Goals

Del brief (no ampliar). Non-Goals del brief aplican sin excepción.

## Rajataja / Definition of Reject

25-architecture-review **SHALL** marcar RETURN TO IMPLEMENTATION si ocurre
cualquiera de estos:

1. `CompanyRolePolicy::viewAny` (o gate Index/table) sigue exigiendo
   `isCompanyManager` / `hasRole('manager')`.
2. Existe ruta nombrada `company.roles.copy`, Action `CopyCompanyRole*`,
   Request de copy company, o UI tenant con CTA/modal de copiar rol.
3. `CreatePlatformRole` / `StorePlatformRoleRequest` aún aceptan o sincronizan
   `permissions` en el alta.
4. Tras materializar, un rol local nuevo puede quedar con `universal = true`.
5. Copy plataforma a company destino asigna un permiso fuera del catálogo
   filtrado tenant de ese destino.
6. Mutación tenant de rol ajeno a `CurrentCompany` (o sin vínculo del actor)
   pasa por Action sin assert de perímetro (solo confiar en Gate::before).
7. Se revierte el mapeo change 1 (menú/Index otra vez con Spatie `.viewAny`
   como dashboard).
8. Tasks del change quedan sin tests Feature que fallen en rojo ante 1–5.

20 **SHALL NOT** ampliar alcance (nuevas features de roles, copy “mejorado”,
cambios de sync manager). Ante duda: parar y devolver a 10 / Luis.

## Decisions

### D1 — Index sin manager

`CompanyRolePolicy::viewAny`: `userBelongsToCurrentCompany` +
`can('company-roles.view')`. Quitar AND `isCompanyManager`. Delete **sin
cambio** (sigue manager + delete).

### D2 — Borrar copy tenant

Eliminar: ruta `company.roles.copy`, método controller, Action
`CopyCompanyRoleToCompanies`, Request, props/UI Edit (`can_copy`, modal,
CTA), tests Feature de copy tenant. Delta OpenSpec: **REMOVED** requirement
«Copiar rol empresa…». Actualizar Purpose / «Universales…» para no citar copy
tenant.

### D3 — Create plataforma

`StorePlatformRoleRequest`: sin `permissions`. `CreatePlatformRole`: no sync
de matriz; rol nace vacío de permisos (Edit atribuye). Preferir rechazo
`prohibited` si llega `permissions` (más estricto que ignorar).

### D4 — Materialización

Tras `findOrCreate` / create local: `forceFill(['universal' => false])->save()`
(o equivalente). Test Feature.

### D5 — Perímetro en Actions

En Create/Update/Toggle/Delete company role: assert
`company_id` del rol == company de sesión (cuando aplica) y, donde el Action
reciba User, vínculo a esa company. No sustituye Policy; refuerza frente a SA
bypass. Reutilizar helper privado o trait mínimo — sin inventar framework.

### D6 — Copy plataforma + recorte

`CopyPlatformRoleToCompanies`: filtrar nombres con
`CompanyRolePermissionCatalog::filterPermissionNames($destination, …)` (o
equivalente) antes de `syncPermissions`. Test: permiso AdminOnly del origen
no aparece en destino.

### D7 — Proceso

Tras 25 APPROVED → informe a Luis/explore → OK producto → 30 archive.

## Risks / Trade-offs

- [Roles custom solo con viewAny antiguo] → Index usa `.view` (change 1); manager
  sync suele dar los 5.
- [i18n keys copy huérfanas] → se pueden dejar en JSON (no bloquean); no
  reintroducir UI.

## Migration Plan

Solo código + tests + specs. Sin migration SQL. Rollback = revert.

## Open Questions

Ninguna.
