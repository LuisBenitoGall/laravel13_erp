# Tasks

## 1. Backend table + status

- [x] 1.1 `CompanyResource` expone `created_at`; `CompanyTableRequest`
      whitelist sort/filter (name, tradename, nif, created_at). Verificar
      JSON table.
- [x] 1.2 Endpoint + Action status (transiciones Draft→Active,
      Active→Closed) + Policy update + linked. Verificar Feature toggle /
      rechazo Closed→Active.
- [x] 1.3 Perímetro forUser intacto en table. Verificar Feature.

## 2. Frontend Index

- [x] 2.1 `Partners/Companies/Index`: ColumnDef + fecha date + persistKey +
      exportable; quitar columna HTML Autorizaciones. Verificar UI/source.
- [x] 2.2 rowActions: toggle + Edit + Autorizaciones (SA) + Delete confirm.
      Verificar UI/source.
- [x] 2.3 Breadcrumb `mis_empresas` + i18n fecha_alta/acciones. Verificar
      keys.

## 3. Docs + calidad

- [x] 3.1 Actualizar `docs/modules/Partners.md` (Index canónico). Verificar
      fila.
- [x] 3.2 Pint + PHPStan + Feature Partners afectados en verde.

## Workflow follow-up

- `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
