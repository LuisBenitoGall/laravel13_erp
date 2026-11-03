Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Origen: **RETURN TO IMPLEMENTATION** de 25-architecture-review sobre
`c2026-11-03-admin-platform-roles-index`. Evidencia UI (captura): Index de Roles
sigue mostrando el listado **pre-change** — CTA «Crear» como link activo, columna
«Acción» con links «Editar», sin `rowActions` Delete, sin filtros de cabecera del
contrato ServerTable. 25 anuló el APPROVED previo: no basta diff/tests de fuente.

Objetivo: dejar el Index `/admin/roles` **observablemente** conforme al contrato del
change + contrato de tablas (`docs/standards/frontend.md` §3, referencia
`Admin/Modules/Index` y `Admin/Permissions/Index`).

## Regla

- La UI en navegador (no solo el fichero) es la prueba del DoD.
- Columna Acciones = `rowActions` de `<ServerTable>` (título i18n `acciones` →
  «Acciones»). Prohibido columna HTML «Acción» / links «Editar».
- CTA Crear = stub **disabled** (design D1): sin `href` a `/admin/roles/create`.
- Delete operativo con confirm solo si `can_delete`; plantillas `manager`/`guest` y
  roles con usuarios sin Delete usable.
- Header filters declarados en columnas **deben** estar en
  `PlatformRoleTableRequest::$filterable` (o quitar el `headerFilterKind`). Prohibido
  filtros de cabecera muertos por whitelist.

## Qué hacer

### 1. Confirmar / cerrar Index conforme (front)

En `resources/js/Pages/Admin/Roles/Index.tsx` (y assets servidos):

- Columnas: `name`, `description`, `universal` (sí/no i18n) + Acciones vía
  `rowActions`.
- `rowActions`: solo Delete (`confirm: true`) si `can_delete`; **cero** Edit.
- ActionBar: `type: 'button'`, `labelKey: 'rol_crear'`, `disabled: true`, sin
  `href` Create (soporte `disabled` ya en `ViewActionBar`).
- `persistKey="admin.roles"`, `exportable={false}` (salvo que el design diga otra
  cosa — no inventar export).
- Quitar cualquier resto del Index viejo: formatter HTML Editar, `labelKey: 'crear'`
  con link, campo `accion`.

Referencia de patrón: `Admin/Modules/Index.tsx` (Acciones) y
`Admin/Permissions/Index.tsx` (Delete + can_delete).

### 2. Contrato ServerTable — whitelist filters

En `PlatformRoleTableRequest`:

- Si Index declara `headerFilterKind` en `name` y/o `description`, añadir esos
  fields a `$filterable` (y a `$sortable` lo que ya se ordene en UI, p. ej. `name`,
  `description` si aplica).
- Si no se quieren filters en esas columnas, **quitar** el `headerFilterKind` del
  Index. No dejar UI de filtro que el backend ignore.

Verificar `universal` sigue filterable (select sí/no).

### 3. Assets / smoke UI (obligatorio)

- Asegurar que el navegador sirve el Index nuevo (`composer dev` / Vite, o
  `npm run build` si aplica). Hard refresh si hace falta.
- Smoke manual o assert de fuente **más** comprobación de que la página Inertia
  monta `Admin/Roles/Index` con el contrato:
  - No aparece link «Editar» en filas.
  - Título de columna Acciones = i18n Acciones (no «Acción»).
  - Crear visible y disabled / no navega a Create.
  - Header filters visibles solo donde el whitelist los honra; al filtrar name (si
    se declara) la table responde.

### 4. Tests

Mantener / endurecer `tests/Feature/Admin/PlatformRolesTest.php`:

- Fuente Index: `disabled: true`, `rol_crear`, `labelKey: 'eliminar'`, **no**
  `href: '/admin/roles/create'`, **no** `labelKey: 'editar'`, **no** formatter/
  columna `accion` legacy.
- Table: Super Admin ausente; `can_delete` false en manager/guest y con usuarios;
  true en rol libre.
- Destroy: no protegido OK; manager/guest rechazado (reutilizar asserts).
- Si se amplía `$filterable`: feature corto que un filter `name` (o description)
  whitelistado altera el resultado (o al menos no se ignora en silent fail).

Suite Platform Roles verde.

### 5. tasks.md del change

En `openspec/changes/c2026-11-03-admin-platform-roles-index/tasks.md`, añadir
subtarea de corrección 25 (Index UI + filterable alineado + smoke) y marcarla al
cerrar. No inventar proposal/design nuevos.

## No tocar

- Completar Create / Edit / matriz (siguen fuera de alcance; páginas pueden
  quedar en repo).
- Roles tenant / `company-roles`.
- Catálogo permisos Spatie.
- Mostrar Super Admin en el listado.
- Archivar. No E2E Pest browser (N/A del change).

## Gate

- Captura o smoke: Index Roles = ServerTable contrato (Acciones `rowActions`, sin
  Editar, Crear stub disabled, filters coherentes con Request).
- `PlatformRoleTableRequest` alineado con `headerFilterKind` del Index.
- Tests del punto 4 verdes.
- Pint solo si hubo PHP.

## Contexto de change

Change activo: `c2026-11-03-admin-platform-roles-index`.
Artefactos: `design.md` D1–D3, delta `specs/platform-roles`,
`docs/prompts/c2026-11-03-admin-platform-roles-index.md`.
Tras cerrar → re-auditoría **25** (con evidencia UI); no saltar a 30.
