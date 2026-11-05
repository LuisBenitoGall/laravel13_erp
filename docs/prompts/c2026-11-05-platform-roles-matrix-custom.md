<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-05-platform-roles-matrix-custom
Rule: 10-architecture
Summary: La matriz de permisos del Edit de roles de plataforma debe mostrar y
  permitir asignar también los permisos Spatie **custom** de cada Functionality
  (mismo UI que los 5 canónicos), no solo `viewAny/view/create/update/delete`.
Goals:
- En la card de cada Functionality (p. ej. Mis empresas / `companies`) SHALL
  aparecer, junto a los 5 automáticos, cualquier permiso del catálogo cuyo
  nombre sea `{functionalitySlug}.{sufijo}` custom (p. ej.
  `companies.limpiar-algunas-empresas`).
- Misma interfaz y mismo comportamiento de toggles que los canónicos; la única
  diferencia es la **génesis** (auto-provision vs alta ad hoc en catálogo
  Permisos).
- «Todas las acciones» SHALL incluir también los custom de esa Functionality
  (mismo comportamiento ampliado al set visible; confirmado Luis).
- `{module}.access` SHALL seguir **fuera** de la card Functionality (comodín
  de Module, no permiso de Functionality).
- Alcance de este change: matriz de roles **plataforma**
  (`PlatformRolePermissionCatalog` / Edit plataforma). Company-roles: omitir
  ahora (aún no abordado); la lógica será similar más adelante.
NonGoals:
- Abordar company-roles / `CompanyRolePermissionCatalog` en este change
  (explícito Luis: omitir).
- Cambiar el modelo de alta de permisos custom en el catálogo Admin (ya
  existe); solo su aparición/asignación en la matriz de roles plataforma.
- Meter `{module}.access` dentro de las cards Functionality.
Skills:
- openspec-propose
- tenancy-rbac (permisos Functionality vs comodín Module)
Notes:
- Origen: `/opsx-explore` correcciones Edit roles; prueba real
  `companies.limpiar-algunas-empresas` creado en catálogo y **no** visible en
  card «Mis empresas».
- Causa (explore): `PlatformRolePermissionCatalog::forMatrix` hardcodea solo
  las 5 acciones canónicas; no consulta permisos Spatie del prefijo de la
  Functionality.
- Spec/matriz previa hablaba de las 5 acciones; este change **amplía** el
  contrato a canónicos + custom bajo el mismo UI.
- Relacionado con `c2026-11-05-platform-roles-edit` (toggles/layout); 10 decide
  si es change aparte (preferible si edit ya archivado/aplicado) o delta
  adicional — no mezclar alcance company-roles.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Matriz de roles incluye permisos custom de Functionality
El sistema SHALL listar en la matriz Edit de roles de plataforma, por cada
Functionality, los permisos Spatie canónicos y los custom cuyo prefijo sea el
slug de esa Functionality. SHALL permitir asignarlos/revocarlos por la misma
UI de checkboxes. SHALL NOT ocultar un permiso custom existente del catálogo
por el solo hecho de no ser una de las cinco acciones automáticas.

#### Scenario: Custom visible en la card
- **WHEN** existe el permiso `companies.limpiar-algunas-empresas` y Super Admin
  abre Edit de un rol de plataforma con la Functionality `companies` en matriz
- **THEN** ese permiso aparece en la card «Mis empresas» junto a los canónicos

#### Scenario: Toggle custom igual que canónico
- **WHEN** Super Admin marca o desmarca un permiso custom en la matriz
- **THEN** el cambio se persiste con el mismo mecanismo que un canónico (sin
  recarga si el Edit ya usa toggles)

#### Scenario: Todas las acciones incluye custom
- **WHEN** Super Admin usa «Todas las acciones» en una Functionality que tiene
  canónicos y custom
- **THEN** se conceden o revocan todos los permisos listados de esa
  Functionality, incluidos los custom


## MODIFIED Requirements

### Requirement: Matriz de permisos en edición de rol de plataforma
La matriz deja de limitarse a las cinco acciones hardcodeadas: el set visible
y asignable por Functionality SHALL ser el de permisos del catálogo ligados a
ese slug (canónicos + custom), manteniendo `{module}.access` fuera de la card
Functionality.
