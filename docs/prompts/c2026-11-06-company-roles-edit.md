<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-06-company-roles-edit
Rule: 10-architecture
Summary: Redesign del **Edit** de roles de empresa (tenant) espejo del Edit
  plataforma: layout v1, toggles async, Guardar=descripción, matriz
  autorizados∩activos∩¬AdminOnly (+ custom), copy a otras companies del
  perímetro, sin UI universal. Universales no editables por el tenant
  (ocultar Editar + Policy bloquea URL).
Goals:
- Espejo completo del Edit plataforma (layout ancho v1, toggles async sin
  recarga, Guardar solo descripción, permisos custom en card, comodín
  `{module}.access` con ≥1 permiso, revert+toast), adaptado al catálogo
  tenant (autorizados ∩ activos ∩ ¬AdminOnly).
- Authz Edit/toggles/copy: `company-roles.update` (alineado al Create con
  permiso Spatie + perímetro).
- Nombre **siempre** readonly en Edit (también custom).
- Descripción editable también en el rol local `manager` (matriz de
  `manager` sigue no editable / `permissions_readonly`).
- CTA Copiar en **este mismo change**: destino = otras companies del
  perímetro del usuario (visible si >1 company); permisos del rol copiado
  recortados al catálogo del destino; versionar `name` si hay colisión.
- Roles **universales** no editables por tenants: ocultar por completo el
  botón/enlace Editar en Index (y UI relacionada); Policy SHALL impedir
  acceso por URL a Edit/toggles/update/copy de un rol universal.
- SHALL NOT mostrar ni mencionar «universal» en UI/copy/i18n del flujo
  tenant (igual que Create).
NonGoals:
- Cambiar Create de roles empresa (ya cerrado en
  `c2026-11-06-company-roles-create`).
- CRUD de roles de plataforma / materialización de plantillas universales
  (salvo el bloqueo de edición tenant sobre universales).
- Rediseñar Index más allá de ocultar Editar en universales y CTA Copiar
  en Edit.
- Cambiar sync de permisos del `manager` local / reglas de borrado
  manager|guest|custom-con-usuarios salvo lo mínimo que Edit ya respete.
Skills:
- openspec-propose
- tenancy-rbac (roles Spatie teams / company_id / universales)
- v1-reference (solo si 10 necesita contrastar layout/copy; patrón canónico
  = Edit plataforma v2 ya hecho)
Notes:
- Origen: `/opsx-explore` / propose Edit tras Create; dudas cerradas Luis
  (2026-11-06): (1) espejo Edit plataforma sí; (2) copy en este change sí
  (perímetro, recorte catálogo destino, versionar nombre); (3) name
  readonly también; (4) descripción de `manager` local editable; (5) un
  solo change Edit+copy+custom.
- Nota adicional Luis: universales no editables por tenants → ocultar
  botón Editar + políticas anti-URL.
- Modelo previo (recap Create): tenant no administra plantillas
  universales; custom nunca universales; matriz ¬AdminOnly.
- Referencia de comportamiento: changes archivados
  `c2026-11-05-platform-roles-edit` y
  `c2026-11-05-platform-roles-matrix-custom`.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Edit rol empresa estilo plataforma (async + layout)
El sistema SHALL presentar el Edit de rol de la company de sesión a ancho
completo (layout v1/plataforma), con matriz de permisos por toggles que
persisten al marcar/desmarcar sin recarga (permiso individual o control
global de Functionality, incluidos custom del slug). El botón Guardar
SHALL persistir solo la descripción. El nombre SHALL ser readonly. Ante
fallo de toggle SHALL revertir el checkbox y mostrar toast. Cuando el rol
obtenga al menos un permiso de Functionality de un Module, SHALL dejar el
Module activado para ese rol (`{module}.access`). La matriz SHALL limitarse
a módulos autorizados ∩ activos ∩ ¬AdminOnly. Acceso a Edit/toggles:
`company-roles.update`.

#### Scenario: Layout y Guardar descripción
- **WHEN** un usuario con `company-roles.update` abre Edit de un rol local
  editable
- **THEN** ve layout ancho, nombre readonly, puede guardar solo la
  descripción, y la matriz no depende de ese submit

#### Scenario: Toggle async con custom y comodín
- **WHEN** marca o desmarca un permiso (canónico o custom) o «Todas las
  acciones» de una Functionality visible
- **THEN** persiste sin recarga; el primer permiso bajo un Module concede
  `{module}.access`; fallo ⇒ revert + toast

#### Scenario: Descripción de manager local
- **WHEN** edita la descripción del rol local `manager` y guarda
- **THEN** la descripción se actualiza y la matriz permanece no editable

### Requirement: Copiar rol empresa a companies del perímetro
El sistema SHALL ofrecer CTA Copiar en el Edit de un rol local editable
cuando el usuario tenga más de una company vinculada, hacia otras companies
de su perímetro. El rol destino SHALL crearse local (`universal = false`)
con permisos recortados al catálogo del destino. Si el `name` ya existe en
destino, SHALL versionar el nombre.

#### Scenario: Copy con recorte y colisión
- **WHEN** copia un rol editable a otra company del perímetro
- **THEN** existe el rol local en destino con permisos ⊆ catálogo destino y
  nombre versionado si había homónimo

### Requirement: Universales no editables por el tenant
El sistema SHALL NOT ofrecer control Editar (Index u otra UI tenant) sobre
un rol universal. Policy y rutas de Edit/update/toggles/copy SHALL rechazar
el acceso del tenant a roles universales (bloqueo por URL incluido).

#### Scenario: Sin CTA Editar ni URL
- **WHEN** el Index lista un rol universal, o el tenant intenta abrir Edit /
  mutar por URL un rol universal
- **THEN** no hay control Editar usable y la mutación/acceso se rechaza
  (403/404)


## MODIFIED Requirements

### Requirement: CRUD de roles custom por empresa (manager)
El Edit/matriz del CRUD tenant SHALL alinearse al patrón async + layout
plataforma y a la authz `company-roles.update`; la matriz del `manager`
local sigue no editable; `guest` y custom sí; descripción editable también
en `manager`. Universales quedan fuera del Edit tenant. El Create por
metadatos no se reabre en este change.

#### Scenario: Editar guest o custom (async)
- **WHEN** un usuario con `company-roles.update` conmuta permisos en `guest`
  o en un rol custom local (no universal)
- **THEN** `role_has_permissions` refleja la selección permitida sin depender
  del Guardar de descripción

### Requirement: Matriz acotada a módulos autorizados y activos
La matriz del Edit tenant SHALL incluir, además de las acciones canónicas,
los permisos custom del slug de cada Functionality visible, con el mismo UI
de toggles; sigue sin AdminOnly ni módulos no autorizados/apagados.

#### Scenario: Custom visible en card tenant
- **WHEN** existe un permiso `{functionality}.{sufijo}` custom y el Module
  está en el catálogo filtrado de la company
- **THEN** ese permiso aparece en la card de esa Functionality en el Edit
  tenant
