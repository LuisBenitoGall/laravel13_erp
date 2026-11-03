<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-03-admin-platform-roles-index
Rule: 10-architecture
Summary: Ratificar en OpenSpec el cambio de contrato pedido por Luis sobre las
  plantillas de roles de plataforma: `manager`/`guest` siguen sin borrarse, pero
  SÍ se editan (Edit + matriz de permisos guardable). Alinear design/deltas/tasks
  (hoy inconsistentes tras un 20 que reescribió artefactos sin gate 10) y dejar
  explícito el matiz sync del manager local.
Goals:
- Enmendar los artefactos del change activo (`proposal.md`, `design.md`,
  `specs/platform-roles/spec.md`, `tasks.md`) para que el contrato canónico diga:
  - Index Acciones al estilo Modules: **Edit** (todas las filas gestionables,
    incluidas plantillas) + **Delete** solo si `can_delete`.
  - Plantillas universales `manager`/`guest`: **no borrables**; **sí editables**
    (matriz asignable/guardable en la plantilla).
  - Manager **local** de cada company: sigue gobernado por sync de autorizaciones;
    guardar la plantilla **no** sustituye ese sync ni actúa como techo que recorte
    módulos ya autorizados/activados.
  - CTA **Crear** sigue stub disabled (Create fuera del DoD de este incremento).
- Resolver contradicciones internas actuales: p. ej. tasks 2.1 / 6.1 («sin Edit»)
  vs §7 / spec delta («Edit en todas las filas»); dejar una sola verdad.
- No crear un change OpenSpec nuevo: actualizar el change existente.
NonGoals:
- No implementar ni “arreglar” código de producción (20 ya tocó Index/Update;
  tras ratificar, 25 re-audita; 20 solo si 10 deja tasks residuales explícitas).
- No completar el flujo Create ni quitar el stub Crear.
- No tocar roles tenant / `company-roles` / User↔rol / catálogo Spatie Permissions.
- No mostrar Super Admin en el Index.
- No archivar.
Skills:
- openspec-propose (enmendar artefactos del change activo; no `openspec new change`)
- tenancy-rbac (plantillas manager/guest, sync manager local vs plantilla universal)
Notes:
- Origen: brief inicial del Index decía **sin Edit** (Create/Edit/matriz diferidos).
  Luis pidió después a 20 cambiar el contrato **respecto a la edición de las
  plantillas** (`manager`/`guest` editables). 20 implementó Edit en Acciones +
  matriz editable y reescribió proposal/design/spec/Admin.md; 25/revisión señaló
  que **cambiar contrato es rol 10**, no 20. Este brief pide a 10 **ratificar y
  limpiar** ese contrato.
- Código de referencia (no rediseñar desde cero): `Admin/Roles/Index.tsx`
  (`rowActions` Edit+Delete), `UpdatePlatformRole` (plantillas dejan de forzar
  matriz read-only), `PlatformRoleResource` (`can_delete`), stub Crear disabled.
- Tras validar OpenSpec: hand-off a **25** (re-auditoría estricta con UI) si el
  código ya cumple; si 10 detecta hueco de implementación, task explícita → 20 → 25.
- Idioma del agente: español. Ficha: `docs/agents/10-architecture.md`.


## MODIFIED Requirements

### Requirement: Acciones Edit y Delete en Index de roles de plataforma
El Index SHALL ofrecer Acciones al estilo Modules: Edit (navega al Edit/matriz) en
todas las filas gestionables del listado (incluidas las plantillas `manager` y
`guest`), y Delete con confirmación solo cuando el rol sea borrable (`can_delete`).
SHALL NOT mostrar Delete usable en plantillas `manager`/`guest` ni en roles con
usuarios asignados (reglas de protección existentes). Super Admin sigue fuera del
listado.

#### Scenario: Edit en Acciones incluye plantillas
- **WHEN** Super Admin ve Acciones de una fila `manager`, `guest` u otro rol de
  plataforma en el Index
- **THEN** hay acción Edit que lleva al flujo de edición / matriz de permisos

#### Scenario: Delete de manager o guest no usable o rechazado
- **WHEN** la fila es la plantilla universal `manager` o `guest`
- **THEN** Delete no es usable o la operación se rechaza sin borrar el rol

#### Scenario: Delete de rol no protegido sin usuarios
- **WHEN** el usuario confirma Eliminar en Acciones de un rol de plataforma no
  protegido y sin usuarios asignados
- **THEN** el rol se elimina y deja de aparecer en el listado

### Requirement: Plantillas manager/guest editables (matriz)
El sistema SHALL permitir editar y guardar la matriz de permisos de las plantillas
universales `manager` y `guest`. SHALL NOT permitir soft/hard delete de esas
plantillas desde este CRUD. Los permisos efectivos del rol local `manager` de cada
company SHALL seguir gobernados por el sync de autorizaciones; guardar la plantilla
SHALL NOT sustituir ese sync ni usarse como techo que recorte funcionalidades de un
módulo ya autorizado y activado para una empresa.

#### Scenario: Plantillas manager y guest editables
- **WHEN** Super Admin abre la edición de la plantilla universal `manager` o `guest`
  y guarda permisos marcados
- **THEN** `role_has_permissions` de esa plantilla refleja la selección; la plantilla
  sigue sin poder borrarse

#### Scenario: Sync manager local intacto
- **WHEN** se guarda la matriz de la plantilla universal `manager`
- **THEN** el rol local `manager` de cada company no se recorta automáticamente por
  esa plantilla; sigue el sync de módulos autorizados/activados

### Requirement: CTA Crear rol stub en Index
El Index SHALL mostrar un control Crear rol en la ActionBar. Ese control SHALL NOT
navegar al flujo Create mientras Create permanezca fuera del DoD de este incremento
(stub visible). Edit desde Acciones SÍ está en alcance de este change (ratificado).

#### Scenario: CTA Crear visible y no completa Create
- **WHEN** Super Admin está en el Index de roles de plataforma
- **THEN** ve el control Crear rol y al usarlo no completa un alta de rol
