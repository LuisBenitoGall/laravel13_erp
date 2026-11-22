# Tasks

> Sin entidad BD nueva. E2E §4: no aplica. R-TEN-05: tests de aislamiento en
> bulk company-users. V-01..V-20 entidad nueva: N/A.

## 1. Contrato ServerTable (frontend)

- [x] 1.1 Extender `ServerTable` con prop opt-in `massActions` (lista dinámica):
  columna checkbox 1ª izquierda (no ocultable), estado de selección página
  actual (clear al cambiar página), header select-all página; verificar que
  sin prop no hay columna ni dropdown
- [x] 1.2 Añadir desplegable de acciones masivas a la izquierda de
  «Mostrar/ocultar columnas», `disabled` con 0 seleccionados; confirm vía
  `ConfirmDialog` cuando `confirm`; i18n label del dropdown; verificar orden
  toolbar y disabled
- [x] 1.3 Actualizar `docs/standards/frontend.md` §3 (y `templates-frontend.md`
  si aplica) con mass actions opt-in + regla «preguntar qué acciones»;
  verificar que el texto documenta checkbox, dropdown, página actual y
  pregunta al agente

## 2. Backend bulk Usuarios empresa

- [x] 2.1 Crear Actions `BulkDetachCompanyUsers` y
  `BulkUpdateCompanyUserStatus` reutilizando detach/status por id + company de
  sesión en transacción; verificar unit/feature que detach no borra User
- [x] 2.2 FormRequests + rutas POST `bulk-detach` / `bulk-status` en
  `CompanyUserController` con Policy `delete`/`update`; verificar 403 sin
  permiso y validación de `ids`
- [x] 2.3 Test aislamiento R-TEN-05: bulk con sesión company A no detach/status
  de users solo de company B; verificar assertions Pest verdes

## 3. Piloto Index Usuarios empresa

- [x] 3.1 Declarar `massActions` en `Admin/CompanyUsers/Index` (eliminar+confirm,
  activar, desactivar) llamando a los endpoints y refrescando la tabla;
  verificar manualmente o con feature/browser smoke el flujo básico
- [x] 3.2 Claves i18n es/en/ca para labels del dropdown/acciones masivas;
  verificar ausencia de literales hardcodeados en el Index
- [x] 3.3 Suite filtrada del change verde (`php artisan test --compact` paths
  tocados) + Pint en PHP dirty

## Workflow follow-up

- Auditoría 25 → 30-testing (sin 40) → `/opsx-archive`
- Ejecutar `/opsx:apply` para implementar
