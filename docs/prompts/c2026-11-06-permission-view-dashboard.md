<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-06-permission-view-dashboard
Rule: 10-architecture
Summary: Alinear el significado de permisos Spatie al uso habitual del ERP:
  `{functionality}.view` = acceso al dashboard/Index (y visibilidad de menú);
  `{functionality}.viewAny` = ver el detalle de un registro (show), no el
  dashboard. Corregir kernel, menú y Policies/rutas Index que hoy usan
  `viewAny` al estilo Laravel invertido respecto a esta regla.
Goals:
- Documentar en `docs/standards/kernel.md` (y R-* / architecture que citen
  menú+viewAny) el principio confirmado Luis:
  - `.view` → dashboard / Index / entrada de menú de la Functionality
  - `.viewAny` → detalle de un registro (no listado, no dashboard)
- `MenuBuilder`: comodín `{module}.access` **AND** `{functionality}.view`
  (dejar de exigir `.viewAny` para mostrar el ítem).
- Policies e Index/table de Functionalities: la habilidad de listado/dashboard
  SHALL comprobar el permiso Spatie `.view` (los métodos Policy de Laravel
  pueden seguir llamándose `viewAny()` / `view()` mapeando al permiso correcto:
  `viewAny()` → `.view`; `view()` → `.viewAny`).
- Alinear tests de menú, Index y authz que asuman el mapeo Laravel antiguo.
- Change 1 = constitución global del significado; dejar listo el terreno para
  el change 2 (hardening roles: Index company-roles con `.view`, quitar copy
  tenant, etc.).
NonGoals:
- Hardening de roles (quitar copy tenant, Create plataforma sin permissions,
  materialización `universal=false`, recorte copy plataforma, asimetría
  manager en Index roles) — **change 2**, a proponer cuando este quede cerrado.
- Renombrar métodos de Policy Laravel (`viewAny`/`view` en PHP) ni renombrar
  los 5 sufijos Spatie en BD (siguen siendo viewAny/view/create/update/delete);
  solo cambia **qué significa cada uno** en producto y qué gate usa Index/menú.
- Rediseñar matrices de roles, Create/Edit async, ni otras Functionalities’
  UX más allá del gate correcto.
Skills:
- openspec-propose
- tenancy-rbac
Notes:
- Origen: `/opsx-explore` revisión roles; Luis: dashboard de **todas** las
  Functionalities = `.view`; no debe usarse `viewAny` para dashboard. Pedido:
  alinear perfectamente; dos changes si hace falta; al acabar el 1º preguntar
  por el 2º.
- Hallazgo explore: `viewAny` en menú/kernel nace con el kernel
  (`09a218a` / convención Laravel) y se reafirmó en `163b9bd` (AND +
  `viewAny`/acción). No es un error solo de company-roles.
- Hoy: MenuBuilder exige `.viewAny`; Policies Index suelen `can('….viewAny')`.
  Eso contradice el principio de producto de Luis.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Semántica view vs viewAny
El sistema SHALL tratar el permiso Spatie `{functionality}.view` como acceso al
dashboard/Index de esa Functionality (incluida la visibilidad del ítem de menú,
junto al comodín de Module). SHALL tratar `{functionality}.viewAny` como permiso
para ver el detalle de un registro de esa Functionality. SHALL NOT exigir
`.viewAny` para mostrar el dashboard ni la entrada de menú.

#### Scenario: Menú con view
- **WHEN** un usuario tiene `{module}.access` y `{functionality}.view`
- **THEN** la Functionality aparece en el menú

#### Scenario: Menú sin view
- **WHEN** un usuario tiene `{module}.access` y solo `{functionality}.viewAny`
  (sin `.view`)
- **THEN** la Functionality no aparece en el menú por ese permiso

#### Scenario: Index/dashboard exige view
- **WHEN** un usuario sin `{functionality}.view` intenta abrir el Index/dashboard
  de esa Functionality
- **THEN** el acceso se rechaza (403)


## MODIFIED Requirements

### Requirement: Visibilidad de menú (AND comodín + permiso Functionality)
Donde el estándar/spec diga que el ítem de Functionality exige comodín **y**
`viewAny`, SHALL pasar a comodín **y** `.view` (dashboard). El detalle de
registro sigue gobernado por `.viewAny` / Policy `view`.

#### Scenario: AND con view para subítem
- **WHEN** faltan el comodín o el `.view` de la Functionality
- **THEN** el subítem no se muestra (salvo bypass Super Admin de permisos)
