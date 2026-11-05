# Design

## Context

Brief canónico. Menú data-driven (`MenuCatalogCache` + `sort`). `company-users`
→ Members. User tiene columna `status` boolean (migración users). Kernel AND:
comodín Module + permiso Functionality.

## Goals / Non-Goals

**Goals:** (brief)

- Menú Usuarios: Usuarios empresa + Todos.
- Todos: ServerTable global; solo máscara notificación.
- Usuarios empresa: filas `user_companies` × company sesión; toggle status,
  delete, edit máscara; Crear máscara.

**Non-Goals:**

- Formularios Create/Edit.
- Roles empresa → Empresas.
- Filtro perfiles públicos; notificación real; alta real.
- Sustituir/eliminar Members (asignación rol) salvo dejar de ser destino del
  menú Usuarios empresa.

**E2E:** no aplica.

## Decisions

### D1 — Functionalities / rutas

| Entrada | Functionality | Ruta (plana) |
|---|---|---|
| Usuarios empresa | `company-users` (existente) | p. ej. `/admin/company-users` Index+table |
| Todos los usuarios | **nueva** `all-users` (slug) bajo Module `usuarios` | p. ej. `/admin/all-users` |

Labels i18n: `usuarios_empresa`, `todos_los_usuarios` (o reutilizar).
`company-roles` **no** se toca (sigue donde esté; fuera de alcance).

Visibilidad menú Todos: brief «cualquier usuario ve todos». Semilla:
Functionality Active + Basic Module `usuarios` ya elegible; sync Manager (y
guest si aplica) con `all-users.viewAny` + `usuarios.access` según política
existente de Basics — **sin** restringir a SA. Policy: `viewAny` con permiso
(o Gate::before SA). No inventar “solo SA”.

### D2 — Todos Index

- ServerTable: columnas mínimas name, email (y surname/status si caben sin
  inventar producto; preferencia name, email, status lectura).
- Row action: icono notificación → toast/máscara (sin POST real).
- Sin Edit/Delete/toggle.

### D3 — Usuarios empresa Index

- Query: `User` whereHas companies id = `CurrentCompany::id()`.
- Acciones reales: toggle `status` (PATCH); delete (soft si User lo tiene —
  hoy **no** SoftDeletes → hard delete solo si brief “eliminar”; **preferencia:**
  soft-delete añadir SoftDeletes **o** desvincular de company — 
  **Decisión 10:** «eliminar» = quitar vínculo `user_companies` de la sesión
  (no borrar el User global), coherente con perímetro empresa y con Todos
  global. Si Luis quiso borrar User, lo corrige en review.
- Edit: acción visible → máscara (toast / disabled navigate); sin formulario.
- Crear: ActionBar máscara (disabled o toast), como roles stub histórico.

### D4 — Authz

- `company-users.*` para Index empresa + toggle/detach.
- `all-users.viewAny` para Todos (solo lectura + máscara notify).
- Middleware `module:usuarios` + company sesión.

### D5 — Specs

Capabilities nuevas `users-directory` + `company-users-index`; MODIFIED leve
`menu-core-pillars` si el pilar Usuarios documenta Members → actualizar a
Index company-users + Todos.

### D6 — Vinculaciones

V-07: sí nuevas perms `all-users.*` (mínimo viewAny); `company-users` ya.
V-08: menú vía Functionalities.
V-09: Module `usuarios` existente.
R-TEN-05: Usuarios empresa — company A no ve members de B.
Todos: global — R-TEN-05 N/A aislamiento (es directorio global a propósito).
E2E N/A.

## Risks / Trade-offs

- [Directorio global temprano] → aceptado Luis; filtro público luego.
- [Eliminar = detach vs destroy] → D3 documentado; review Luis.

## Migration Plan

Seed Functionality `all-users`; reassign href MenuBuilder; forget menu cache;
páginas + tests.

## Open Questions

Ninguna bloqueante (eliminar = detach company; corregible en review humana).
