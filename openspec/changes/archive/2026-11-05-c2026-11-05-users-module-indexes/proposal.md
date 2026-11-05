# Proposal

## Why

El Module Usuarios debe ofrecer dos Index ServerTable: directorio global «Todos
los usuarios» (acciones mínimas) y «Usuarios empresa» (`user_companies` de la
sesión, acciones estándar). Hoy `company-users` redirige a Members HTML; no
existe «Todos».

Fuente: `docs/prompts/c2026-11-05-users-module-indexes.md`.

## What Changes

- Menú Usuarios: entradas «Usuarios empresa» + «Todos los usuarios» (Functionality
  nueva para Todos; `company-users` apunta al Index nuevo).
- Index Todos: ServerTable de todos los `users`; acción fila = máscara
  notificación; sin edit/activar/eliminar.
- Index Usuarios empresa: ServerTable users en `user_companies` de
  `CurrentCompany`; acciones activar/desactivar (`users.status`), eliminar,
  editar = máscara/stub; CTA Crear = máscara.
- Sin formularios Create/Edit; sin reubicar `company-roles`; sin filtro
  perfiles públicos; sin envío real de notificaciones.

## Capabilities

### New Capabilities

- `users-directory`: Index global Todos los usuarios (ServerTable + máscara
  notificación).
- `company-users-index`: Index Usuarios empresa (ServerTable + acciones
  acordadas + Crear máscara).

### Modified Capabilities

- `menu-core-pillars` (o menú Usuarios): submenú Usuarios con las dos entradas
  (sin exigir Roles empresa en este change).

## Impact

- Dominio Users/Admin o Partners: controllers table, routes, pages Inertia,
  seed Functionality, MenuBuilder hrefs, cache menú.
- Members (`company-member-role-assignment`) puede seguir existiendo para
  asignación de rol; deja de ser el destino del ítem Usuarios empresa.
- E2E: no aplica salvo que 10 marque flujo e2e — no.
