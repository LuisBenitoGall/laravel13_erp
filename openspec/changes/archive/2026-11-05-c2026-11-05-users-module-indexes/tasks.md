# Tasks

## 1. Catálogo / menú

- [x] 1.1 Sembrar Functionality `all-users` bajo Module `usuarios` + permisos
      canónicos; labels i18n Todos los usuarios. Verificar seeder/menú.
- [x] 1.2 MenuBuilder: `company-users` → Index Usuarios empresa; `all-users` →
      Index Todos; forget cache menú / sort. Verificar entradas menú.
- [x] 1.3 Sync Manager (u roles Basic) con `all-users.viewAny` según visibilidad
      «cualquier usuario» + `usuarios.access`. Verificar menú no-SA.

## 2. Backend Indexes

- [x] 2.1 Rutas/controller/table Todos (`all-users`); Policy viewAny.
      Verificar Feature listado global.
- [x] 2.2 Rutas/controller/table Usuarios empresa filtrado
      `user_companies`×CurrentCompany; Policy. Verificar Feature + R-TEN-05.
- [x] 2.3 Toggle `status` + detach `user_companies` (eliminar). Verificar
      Feature.
- [x] 2.4 Endpoints/acciones Create y Edit: máscara (sin persistir alta/edición).
      Verificar no crean/editan user.

## 3. Frontend

- [x] 3.1 Page Index Todos: ServerTable + acción notificación máscara (toast).
      Verificar UI / Inertia.
- [x] 3.2 Page Index Usuarios empresa: ServerTable + acciones + CTA Crear
      máscara. Verificar UI.
- [x] 3.3 i18n claves nuevas (es/en/ca). Verificar.

## 4. Docs + tests

- [x] 4.1 Actualizar `docs/modules/Admin.md` (o ficha Users si 10 la crea
      mínima) — menú + Indexes. Verificar.
- [x] 4.2 Feature tests escenarios specs. Verificar Pest verde.
- [x] 4.3 E2E: no aplica. Members legacy: no borrar salvo desvío de menú.

## 5. Calidad

- [x] 5.1 Pint `--dirty`.
- [x] 5.2 PHPStan / lint front tocado.

## Workflow follow-up

- Revisión Luis (sobre todo D3: eliminar = detach) → `/opsx-apply` → 25 → 30.
- Forms Create/Edit user; roles→Empresas; notificación real; perfiles públicos:
  changes posteriores.
