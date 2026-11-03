# Design

## Context

See proposal.md — Why. Estado tras apply + corrección 25 + matiz plantillas (pendiente
ratificación 10):

- Index ya usa ServerTable + `rowActions` Edit+Delete condicional + CTA Crear disabled.
- `platformManaged` excluye Super Admin; `DeletePlatformRole` protege plantillas y
  roles con usuarios.
- `PlatformRoleResource`: `can_delete`, `permissions_readonly = false`.
- `UpdatePlatformRole` ya no fuerza matriz read-only en plantilla manager.
- Spec main `platform-roles` ya describe plantillas editables; el delta del change
  debe ser la única fuente coherente Index+matriz+stub.

## Goals / Non-Goals

**Goals:**

- Contrato OpenSpec único y sin contradicciones (Index Edit+Delete; plantillas
  editables; Crear stub; sync local intacto).
- Design alineado al código de referencia existente (sin rediseñar desde cero).
- Tasks históricas corregidas; residuales de implementación solo si 10 detecta hueco.

**Non-Goals:**

- Completar Create (quitar stub).
- Roles tenant / User↔rol / catálogo permisos custom.
- Mostrar Super Admin en Index.
- Archivar (lo hace 30 tras 25).
- Reimplementar lo ya verde salvo gap explícito.

## Decisions

### D1 — Stub Crear = botón disabled (sin navegación)
**Elección:** ActionBar `type: 'button'`, `disabled: true`, sin `href` a Create.
**Descartado:** link al Create existente (sacaría Create del “fuera de DoD”).

### D2 — Acciones Edit + Delete condicional (ratificado)
**Elección:** Edit (Pencil → `/admin/roles/:id/edit`) en **todas** las filas del
Index; Delete solo si `can_delete`.
**Descartado (brief inicial):** Acciones solo Delete / sin Edit — sustituido por
decisión de Luis sobre plantillas editables, ratificada aquí por 10.

### D3 — Plantillas manager/guest: no delete; sí matriz
**Elección:** `can_delete = false` en plantillas; Update persiste permisos de la
plantilla. `permissions_readonly` no se usa para bloquear plantillas.
**Manager local:** sync de autorizaciones sigue siendo la fuente de permisos
efectivos del `manager` de cada company; guardar la plantilla no dispara recorte
ni sustituye ese sync (coherente con materialización: manager local no copia la
matriz de plantilla).

### D4 — Reutilizar backend; no nuevo change
**Elección:** Enmendar este change (`c2026-11-03-admin-platform-roles-index`); no
`openspec new change`. Código de referencia ya en repo; 25 re-audita tras esta
ratificación.

### D5 — E2E
N/A (`e2e.md` §4).

## Risks / Trade-offs

- [Confusion plantilla vs manager local] → Mitigación: ficha Admin + scenarios
  explícitos de sync intacto.
- [Create accesible por URL] → Aceptado mientras stub en Index.
- [Artefactos reescritos por 20] → Mitigación: esta pasada 10 es la verdad; tasks
  históricas se reescriben para no contradecir §7.

## Migration Plan

N/A (contrato/docs). Código ya desplegado en el change; siguiente paso = 25.

## Open Questions

Ninguna. Huecos de implementación: ninguno detectado en lectura de Index /
Resource / tests de plantilla; 25 confirma en UI.
