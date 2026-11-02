# Tasks

> Higiene CRUD Modules. E2E §4 / R-TEN-05 nuevo / V-* entidad nueva: N/A.

## 1. Backend — soft delete Module + shell guard

- [x] 1.1 En `DeleteModule` (o Policy): rechazar soft delete de slugs `admin`/
  `workspace`. Verificar: test Feature 403/422 y registro intacto.
- [x] 1.2 Tests: soft delete Module no shell OK; no borra permisos Spatie del
  `{slug}.access` (si aplica aserción). Verificar: suite filtrada verde.
- [x] 1.3 Asegurar `ModuleResource` (edit) expone `level` para UI readonly.
  Verificar: respuesta edit incluye level.
- [x] 1.4 Pint PHP: `vendor/bin/pint --dirty --format agent`. Verificar: exit 0.

## 2. Frontend — Delete UI + level readonly

- [x] 2.1 Index: Acciones con Delete (último, confirm) en no-shell; orden acorde a
  design D2. Verificar: lectura Index + test fuente o Feature si existe.
- [x] 2.2 Edit: ActionBar `type: 'delete'` si no shell → Index tras éxito; mantener
  Nueva funcionalidad. Verificar: Edit.tsx.
- [x] 2.3 Edit tab Módulo: mostrar level i18n readonly. Verificar: UI/source.

## 3. Retirar Functionalities genéricas + ModuleForm

- [x] 3.1 Eliminar páginas `Admin/Functionalities/*` genéricas y rutas
  `functionalities.table` + resource Index/Create/Edit/store genéricos; limpiar
  controller. Verificar: `route:list` sin index/create/edit genéricos; nested OK.
- [x] 3.2 Borrar `ModuleForm.tsx`; actualizar `ShellActionBarTest` (y otros) a
  `Create.tsx`. Verificar: tests Shell/ActionBar verdes.
- [x] 3.3 i18n solo si hay claves nuevas de delete/level. Verificar: JSON es/en/ca.

## 4. Cierre

- [x] 4.1 Smoke: delete no-shell Index/Edit; shell sin Delete; level visible; 404
  genéricos Functionalities. Verificar: manual o tests.
- [x] 4.2 Pint final si hubo más PHP. Verificar: exit 0.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → (E2E N/A) → 30 / archive.
