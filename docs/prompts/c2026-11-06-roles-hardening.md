<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".

  ESTRICTO: este change cierra el hardening de roles acordado tras la revisión
  explore. 10 DEBE copiar la sección «Rajataja / Definition of Reject» a
  design.md y tasks.md. 25 DEBE devolver RETURN TO IMPLEMENTATION si falta
  cualquier ítem de esa lista. Tras 25 APPROVED, el hilo vuelve a Luis/explore
  para aprobación final de producto ANTES de /opsx-archive (roles no se dan
  por cerrados sin ese OK).
-->

Change: c2026-11-06-roles-hardening
Rule: 10-architecture
Summary: Hardening de roles tras revisión explore + change 1 (`.view` dashboard).
  Index tenant sin exigir manager (solo `company-roles.view`); eliminar por
  completo el copy de roles tenant; cierres de seguridad acordados como
  paquete eficiente (Create plataforma sin permissions, materialización
  `universal=false`, perímetro en Actions, recorte copy plataforma).
Goals:
- Index / `CompanyRolePolicy::viewAny()`: acceso con `company-roles.view` +
  perímetro company; **SHALL NOT** exigir `isCompanyManager` (Luis: dashboard =
  `.view` para cualquier rol de la empresa con ese permiso). Delete puede
  seguir exigiendo manager si ya lo hace (no se reabre).
- **Eliminar copy de roles tenant**: sin CTA, sin modal, sin ruta
  `company.roles.copy`, sin Action/Request de copy company, sin props
  `can_copy` / `copy_companies` en Edit tenant, sin i18n/UI de copiar en ese
  flujo. Tests que asuman copy tenant → reescribir o borrar.
- Create plataforma: el alta **SHALL NOT** aceptar ni persistir `permissions`
  (solo metadatos; matriz solo en Edit). FormRequest + Action.
- Materialización (`EnsureCompanyDefaultRoles`): roles locales SHALL quedar con
  `universal = false` de forma explícita.
- Actions de mutación tenant (update/toggle/delete/create según toque el
  change): assert de perímetro (`company_id` = sesión + actor vinculado) que
  no dependa solo del bypass `Gate::before` de SA.
- Copy **plataforma** (si se mantiene): al copiar a companies del perímetro,
  permisos del rol local destino SHALL quedar recortados al catálogo tenant
  del destino (autorizados ∩ activos ∩ ¬AdminOnly, mismo criterio que matriz
  company). Sin esto, AdminOnly u otros permisos pueden colarse en locales.
- Semántica `.view` / `.viewAny` del change 1 **SHALL** respetarse (no
  revertir MenuBuilder/Policies al mapeo Laravel antiguo).
NonGoals:
- Reintroducir o “mejorar” copy tenant.
- Cambiar Create/Edit async de roles (layout, toggles, custom matrix) salvo lo
  imprescindible para quitar copy y compilar.
- Cambiar materialización de *cuándo* se crean universales en companies
  existentes (backfill); solo el flag `universal=false` en locales.
- Rediseñar assignment de roles a miembros (`AssignCompanyRole`) ni sync
  manager salvo lo que ya impone el perímetro.
- Ampliar SA a “ver todas las companies” (kernel: perímetro vinculado).
- Otro change de constitución de permisos (ya cerrado
  `c2026-11-06-permission-view-dashboard`).
Skills:
- openspec-propose
- tenancy-rbac
- testing-best-practices (regresión Index sin manager; ausencia copy tenant;
  Create plataforma sin permissions; copy plataforma recortada)
Notes:
- Origen: `/opsx-explore` revisión roles; respuestas Luis (Index `.view` sin
  manager; quitar copy tenant; paquete eficiente a criterio explore). Change 1
  permisos ya archivado.
- Estado pre-change (explore): `CompanyRolePolicy::viewAny` aún AND manager;
  copy tenant vivo (ruta/Action/UI); `CreatePlatformRole` acepta `permissions`;
  copy plataforma hace `syncPermissions` sin filtro catálogo destino;
  materialización no fuerza `universal=false`.
- **Proceso obligatorio**: 10 propose → 20 apply → 25 review → **parada**:
  volver a Luis/explore con informe 25 para aprobación de producto → solo
  entonces 30 archive. Prohibido archivar o declarar “roles completos” sin
  esa aprobación.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.

## Rajataja / Definition of Reject (copiar a design.md + tasks.md)

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


## ADDED Requirements

### Requirement: Index roles empresa sin exigir manager
El sistema SHALL permitir el Index/table de roles de la company de sesión a
cualquier usuario vinculado con permiso Spatie `company-roles.view` (método
Policy de listado según semántica change 1). SHALL NOT exigir el rol local
`manager` para ese Index.

#### Scenario: Index con view sin ser manager
- **WHEN** un usuario de la company tiene `company-roles.view` y no es
  `manager`, y abre Index o `table` de roles empresa
- **THEN** el acceso se concede (200) dentro del perímetro

#### Scenario: Index sin view
- **WHEN** un usuario sin `company-roles.view` (y sin bypass SA) fuerza Index
  o `table`
- **THEN** recibe 403

### Requirement: Sin copy de roles tenant
El sistema SHALL NOT ofrecer ni ejecutar copia de roles en el contexto
company/tenant (UI, rutas, Actions). El copy de roles de **plataforma** puede
permanecer, con el recorte de catálogo del destino exigido abajo.

#### Scenario: Sin superficie copy tenant
- **WHEN** se inspeccionan rutas, Edit UI y Actions de company roles
- **THEN** no existe flujo usable de copiar un rol de empresa a otras
  companies

### Requirement: Create plataforma solo metadatos (sin permissions)
El sistema SHALL rechazar o ignorar de forma estricta cualquier `permissions`
enviado en el Create de rol de plataforma; el alta solo persiste metadatos y
deja la matriz para Edit.

#### Scenario: POST Create con permissions
- **WHEN** un cliente envía `permissions` en el store de rol plataforma
- **THEN** esos permisos no quedan asignados por el Create (rechazo de
  validación o ignorados de forma que el rol nace sin esa matriz)

### Requirement: Locales materializados nunca universales
Al materializar plantillas universales en una company, cada rol local creado o
asegurado SHALL quedar con `universal = false`.

#### Scenario: Materialización fuerza no universal
- **WHEN** se ejecuta la materialización de defaults para una company
- **THEN** los roles locales resultantes tienen `universal = false`

### Requirement: Copy plataforma recorta al catálogo destino
Al copiar un rol de plataforma no universal a una company del perímetro, el
sistema SHALL asignar al rol local solo permisos ⊆ catálogo tenant del destino
(autorizados ∩ activos ∩ ¬AdminOnly).

#### Scenario: Copy plataforma sin AdminOnly en destino
- **WHEN** el origen plataforma tiene un permiso AdminOnly (u otro fuera del
  catálogo filtrado del destino) y se copia a una company
- **THEN** ese permiso no queda en el rol local destino


## MODIFIED Requirements

### Requirement: CRUD de roles custom por empresa (manager)
El Index/listado del CRUD tenant SHALL autorizarse con `company-roles.view`
sin exigir rol `manager`. La creación/edición siguen sus permisos Spatie ya
acordados. El copy entre companies **deja de formar parte** del CRUD tenant.
Delete y protecciones manager/guest/universales no se rediseñan salvo lo
necesario para compilar tras quitar copy.

#### Scenario: Index sin manager con view
- **WHEN** un usuario no manager con `company-roles.view` lista roles de su
  company
- **THEN** la operación tiene éxito

### Requirement: Copiar rol empresa a companies del perímetro
Este requisito del spec `company-custom-roles` SHALL quedar **retirado** del
comportamiento del sistema (REMOVED efectivo vía este change: sin CTA, sin
endpoints, sin Action). 10 documentará REMOVED/MODIFIED en el delta OpenSpec
de forma que archive no deje el copy tenant como requisito vigente.

#### Scenario: Copy tenant inexistente
- **WHEN** un cliente intenta la antigua ruta o UI de copy de rol empresa
- **THEN** no hay operación exitosa de copia (404/410/ausencia de ruta o
  equivalente inequívoco)
