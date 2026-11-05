# Tasks

## 1. Frontend Create + Index

- [x] 1.1 Index: CTA `rol_crear` habilitado → visita `/admin/roles/create`.
      Verificar navegación manual o Feature + aserción fuente.
- [x] 1.2 Create: formulario solo metadatos (name, description, universal);
      sin matriz ni props `matrix*`. Verificar Inertia props Create.
- [x] 1.3 Extraer campos metadatos a componente reutilizable bajo
      `Pages/Admin/Roles/components/` (documentar reuso tenant). Verificar
      Create y Edit siguen funcionando.
- [x] 1.4 Edit: conservar matriz; alinear campos metadatos al componente
      compartido sin regresiones. Verificar Feature update/matriz existentes.

## 2. Backend (ajuste mínimo)

- [x] 2.1 `create()` no necesita pasar matrices si Create no las usa; store
      sigue permitiendo `permissions` vacío y redirect Edit. Verificar
      Feature store → edit.
- [x] 2.2 FormRequest Create: `permissions` opcional/ausente OK. Verificar
      validación.

## 3. Docs + tests

- [x] 3.1 Actualizar `docs/modules/Admin.md` (Index: CTA Create real, no
      stub; Create = metadatos). Verificar ficha coherente.
- [x] 3.2 Sustituir test «CTA stub disabled» por CTA navega / Create
      metadatos / store redirect Edit. Verificar
      `PlatformRolesTest` (y afines) verdes.
- [x] 3.3 R-TEN-05 / E2E / V-07..V-09 nuevas: no aplican (design).

## 4. Calidad

- [x] 4.1 Pint `--dirty` PHP tocado.
- [x] 4.2 Lint/tsc front tocado; PHPStan paths PHP si hubo cambio.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 / archive.
- Change posterior: cablear mismo formulario a company roles + catálogo de
  permisos tenant.
