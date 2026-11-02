# Proposal

## Why

El CRUD de Módulos está funcionalmente cerrado, pero faltan higiene y el soft delete en
UI, quedan rutas/páginas genéricas de Functionalities y un `ModuleForm` legacy, y el
level no se ve en Edit (solo es inmutable).

## What Changes

- Soft delete de Module con confirm en Index (Acciones) y Edit (ActionBar); redirect a
  Index desde Edit tras borrar. Sin borrar permisos Spatie.
- **Decisión shell:** no permitir soft delete de `admin`/`workspace` por UI/API de
  destroy (mantenimiento = Inactive).
- Mostrar `level` readonly (i18n) en Edit tab Módulo.
- Retirar Index/Create/Edit/table genéricos de Functionalities (páginas + rutas);
  conservar nested bajo Module.
- Eliminar `ModuleForm.tsx` huérfano; ajustar tests que lo lean.
- Nueva funcionalidad: **sin cambio** de ubicación (sigue en ActionBar).

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `admin-module-catalog`: soft delete Module UI + bloqueo shell; level readonly visible;
  retirar CRUD genérico Functionalities.
- `server-table-reference`: Acciones Index Modules incluyen Delete (último) con confirm;
  sin Delete en filas shell.
- `admin-ui-login`: Index Acciones con Delete; Edit con level visible readonly + Delete
  en ActionBar; sin páginas genéricas Functionalities.

## Impact

- Front: `Modules/Index`, `Modules/Edit`, borrar `ModuleForm.tsx` y
  `Admin/Functionalities/*` genéricos.
- Backend: `DeleteModule` (+ guard shell); rutas `functionalities` resource/table
  genéricas; tests (`ShellActionBarTest`, menú, Module delete).
- Specs: catalog, server-table-reference, admin-ui-login.
