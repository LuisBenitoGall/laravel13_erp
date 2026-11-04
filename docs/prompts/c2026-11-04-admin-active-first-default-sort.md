<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-04-admin-active-first-default-sort
Rule: 10-architecture
Summary: Ordenación por defecto Active-first en Indexes ServerTable de Módulos,
  Países, Provincias, Poblaciones y Monedas, solo cuando el cliente no envía sort;
  después el criterio secundario que ya usaba cada tabla (salvo Módulos, sin
  secundario explícito).
Goals:
- Cuando `$request->sorts() === []`, ordenar primero por status Active y luego
  Inactive (status string `active`/`inactive`, no booleano).
- Aplicar en: Modules, Countries, Provinces, Towns, Currencies.
- Tras Active-first, conservar el secundario actual donde exista: Countries /
  Provinces / Towns / Currencies → `name`. Modules → solo Active-first (Luis: sin
  secundario necesario).
- Solo en ausencia de sort del cliente; si el usuario ordena por columna, no
  prefijar Active-first.
NonGoals:
- Prefijo Active-first cuando hay sorts del cliente.
- Cambiar columnas visibles, Acciones, filtros o UI.
- Otros Indexes (Roles, Permisos, Functionalities anidadas, etc.) salvo los cinco
  listados.
- Refactor obligatorio de TableBuilder (10 decide si helper o orderBy locales).
Skills:
- openspec-propose
- laravel-best-practices (queries table())
Notes:
- Origen: `/opsx-explore` tras currencies archivado.
- Respuestas Luis: incluir Provincias y Poblaciones; Modules sin secundario;
  Active-first solo sin sort del cliente.
- Hoy: Countries/Provinces/Towns/Currencies ya hacen `orderBy('name')` si sorts
  vacío; Modules no tiene default explícito.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Default Active-first en Indexes Admin acordados
El sistema SHALL, en los endpoints `table` de Modules, Countries, Provinces, Towns
y Currencies, cuando el cliente no envía sorts, ordenar los registros con status
Active antes que Inactive. SHALL NOT aplicar ese prefijo cuando el cliente envía
sorts. Tras Active-first, Countries, Provinces, Towns y Currencies SHALL conservar
`name` como criterio secundario; Modules SHALL NOT exigir un secundario adicional.

#### Scenario: Países sin sort del cliente
- **WHEN** se solicita el `table` de countries sin sorts
- **THEN** aparecen primero los Active y, dentro de cada grupo de status, por name

#### Scenario: Módulos sin sort del cliente
- **WHEN** se solicita el `table` de modules sin sorts
- **THEN** aparecen primero los Active y después los Inactive

#### Scenario: Usuario ordena por columna
- **WHEN** el cliente envía un sort (p. ej. por name o code)
- **THEN** se aplica ese sort sin prefijo Active-first obligatorio

#### Scenario: Provincias / Poblaciones / Monedas sin sort
- **WHEN** se solicita el `table` de provinces, towns o currencies sin sorts
- **THEN** Active primero y luego name
