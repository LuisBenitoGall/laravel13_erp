# Proposal

## Why

En Indexes Admin con muchos Inactive (sobre todo monedas y geografía), el listado
por defecto mezcla activos e inactivos. Conviene ver primero los Active, sin
cambiar el sort cuando el usuario ordena por columna.

## What Changes

- Default Active-first (`status` string Active antes que Inactive) en endpoints
  `table` de Modules, Countries, Provinces, Towns y Currencies, **solo** si el
  cliente no envía sorts.
- Secundario: `name` en Countries, Provinces, Towns y Currencies (como hoy).
  Modules: solo Active-first, sin secundario obligatorio.
- Sin cambio de UI, columnas ni Acciones.

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `admin-module-catalog`: default sort Active-first en Index Modules sin sorts
  del cliente.
- `admin-geography-catalog`: idem en Countries, Provinces y Towns (+ secundario
  `name`).
- `admin-currency-catalog`: idem en Currencies (+ secundario `name`).

## Impact

- Controllers `table` (y tests feature de orden por defecto). Helper opcional
  compartido (design).
- E2E: N/A.
- Sin migraciones, menú, permisos ni dependencias nuevas.
