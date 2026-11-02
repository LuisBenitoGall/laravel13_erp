# Tasks

> Scope: polish Index Modules + permitir Inactive en shell. No entidad nueva.
> V-01..V-20 / R-TEN-05 nuevos: **N/A** (Module es catálogo global ya cubierto).
> E2E §4: **N/A** (design D6).

## 1. Backend — status shell

- [x] 1.1 Quitar en `UpdateModule` el rechazo por `isShellCritical()` al pasar a
  Inactive; si `isShellCritical` / `SHELL_SLUGS` quedan sin callers, eliminarlos.
  Verificar: `php artisan test --compact tests/Feature/Admin/ModuleTableReferenceTest.php`
  tras 1.2 (o tinker/patch puntual que persista Inactive en `admin`).
- [x] 1.2 Invertir el test `no permite desactivar módulos shell admin/workspace` en
  `ModuleTableReferenceTest` para que espere éxito al poner Inactive (y opcionalmente
  reactivar). Verificar: ese filtro/archivo en verde.
- [x] 1.3 Si `modulo_shell_no_desactivable` ya no tiene usos, retirarlo de
  `lang/{es,en,ca}.json`. Verificar: búsqueda en repo sin referencias huérfanas.

## 2. Frontend — Index Modules

- [x] 2.1 En `Admin/Modules/Index.tsx`, eliminar la columna `status`/Estado (y su
  `statusValues` si solo servía a esa columna). Verificar: fuente sin
  `field: 'status'` en ese Index; columnas = name, color, icon, level, created_at +
  Acciones.
- [x] 2.2 Añadir formatter de icono con `resolveMenuIcon`; `title` = nombre del icono
  almacenado. Verificar: celda no muestra solo texto crudo del nombre; `title`
  presente en el nodo.
- [x] 2.3 Formatter (o equivalente) de Nivel con i18n
  (`nivel_admin_only` / `nivel_basic` / `nivel_optional`). Verificar: celda muestra
  etiqueta traducida, no el código crudo.
- [x] 2.4 Eliminar el guard `shellCritical` en `rowActions`; mantener Edit y sin
  Delete; reutilizar `kind: 'toggle'` + `patchJson` existente. El alcance «toggle en
  todas las filas» queda sustituido por 2.5.
- [x] 2.5 En `rowActions`, declarar el toggle solo si `row.level === 'optional'`.
  En `basic` y `admin_only` la fila ofrece Edit y no el toggle. Verificar: fuente con
  esa condición; en el listado, Admin y Workspace sin interruptor.

## 3. Tests de contrato Index

- [x] 3.1 Ajustar/añadir aserciones en `ModuleTableReferenceTest` (o test hermano) para
  columnas sin `status`, presencia de formatter/icon/nivel y ausencia de
  `shellCritical` según lo que el suite ya inspeccione por fuente. Verificar:
  `php artisan test --compact tests/Feature/Admin/ModuleTableReferenceTest.php` verde.
- [x] 3.2 Pint en PHP tocado: `vendor/bin/pint --dirty --format agent`. Verificar:
  exit 0.
- [x] 3.3 Aserción de fuente en `ModuleTableReferenceTest`: el Index condiciona el
  toggle a `row.level === 'optional'`. Verificar: ese archivo en verde.

## Workflow follow-up

- Revisión humana de la propuesta (Luis) antes de `/opsx-apply`.
- Tras apply: auditoría 25 → (E2E N/A) → 30-testing / archive.
