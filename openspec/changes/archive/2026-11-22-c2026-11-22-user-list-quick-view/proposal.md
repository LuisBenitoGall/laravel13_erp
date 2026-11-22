# Proposal

## Why

Los listados Usuarios empresa y Todos los usuarios solo permiten ir al Edit o
acciones de fila. Falta una vista rápida de lectura (como en v1) sin abandonar
el listado, reutilizando `UserProfileReadout`.

## What Changes

- Icono «ver detalle» a la izquierda del nombre en `/admin/company-users` y
  `/admin/all-users`.
- Panel lateral derecho con `UserProfileReadout`; al elegir otro user se
  recarga el mismo panel sin cerrarlo.
- Cierre solo con botón X (sin cierre por clic fuera).
- Company-users: CTA Editar en cabecera del panel solo con permiso de update
  de usuarios → Edit company-users.
- All-users: misma ficha RO; sin CTA Editar.
- Endpoint(s) JSON de quick-view (payload acotado al readout); no hinchar
  `/table`.
- Contenedor de panel en catálogo UI (p. ej. Sheet shadcn configurado para no
  cerrar por outside click).

## Capabilities

### New Capabilities
- `user-list-quick-view`: panel de vista rápida de usuario desde listados
  (trigger, shell, carga, switch, cierre solo X, Editar condicional).

### Modified Capabilities
- `company-users-index`: disparador de vista rápida en columna nombre.
- `users-directory`: disparador de vista rápida (solo lectura, sin Editar).

## Impact

- Frontend: Indexes company-users / all-users; componente panel; formatter
  nombre; posible `ui/sheet`; catálogo `frontend.md`.
- Backend: quick JSON en `CompanyUserController` / `AllUserController` (+
  helper de payload reutilizando shape de `UserEditPageData` / readout).
- Authz: `company-users.view` (+ `update` para flag Editar); `all-users.view`
  + solo users `is_public`.
- Tests feature authz/perímetro; i18n tooltip/labels.
- Sin migraciones. E2E §4: no aplica (CRUD/listado).
  `libraries.md`: Sheet = shadcn copiado (sin paquete npm nuevo fuera del
  canon shadcn/radix ya listado).
