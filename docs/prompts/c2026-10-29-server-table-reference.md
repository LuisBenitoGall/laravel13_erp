<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-29-server-table-reference
Rule: 10-architecture
Summary: Elevar `<ServerTable>` (Tabulator) al contrato de listados de referencia —
    filtros por columna, columna Acciones, i18n completa, toolbar (columnas, page size,
    Excel/PDF), modal de búsqueda avanzada genérico (opt-in); aplicar en dashboard
    Módulos; actualizar norma frontend para que todo CRUD copie el patrón salvo
    excepción escrita.
Goals:
- Opción A: endurecer el Index/dashboard de Configuración → Módulos como primera
  aplicación del patrón, sobre Tabulator vía único wrapper `ServerTable` (ya confirmado
  en libraries/frontend; no instanciar Tabulator fuera).
- Regla 1 — Filtros en cabecera: cada columna con filtro acorde al tipo (string, date,
  select/opciones cerradas, …); referencia visual adjuntada (inputs bajo el título +
  sort).
- Toolbar de listado (referencia visual): selector “Mostrar/ocultar columnas” con
  checkboxes; persistencia de visibilidad de columnas **por usuario en localStorage**;
  selector de nº de registros por página (“Mostrar N Registros”); botones de descarga
  **Excel (.xlsx)** y **PDF**.
- Regla 2 — Columna derecha siempre **Acciones**: título centrado; botones icono alineados
  a la derecha; tooltip obligatorio (Editar, Eliminar, …); Eliminar siempre el último a
  la derecha y con confirm. API la más eficiente (p. ej. `rowActions` en ServerTable).
  En Módulos: **solo Edit**. En cada implementación futura de dashboard, preguntar a Luis
  qué botones van en Acciones.
- Regla 3 — Traducir todos los textos de la tabla al idioma en sesión, incluidos los de
  Tabulator (paginador, etc.); al cambiar idioma, actualizar sin F5.
- Regla 4 — Componente genérico reutilizable de **búsqueda avanzada** (modal) al que se
  pasan los inputs por parámetro; se abre desde la ActionBar como primer CTA a la
  izquierda salvo que haya Volver; misma estructura, request de datos filtrados. En
  Módulos **no aplica**: no mostrar el botón.
- Actualizar la **norma** (`frontend.md` y plantillas/templates que correspondan) para
  que **todos los CRUDs** copien este patrón salvo que un design indique otra cosa por
  escrito.
NonGoals:
- Implementar búsqueda avanzada cableada en Módulos (solo API/componente listo; sin botón).
- Migrar todos los listados existentes del ERP en este change (primera aplicación:
  Módulos; el resto irá adoptando).
- Edición inline en listados (sigue prohibida).
- Inventar dominio de Presupuestos/Pedidos/etc.; solo preparar el patrón.
Skills:
- frontend-design
- laravel-specialist
Notes:
- Gap actual: `frontend.md` §3 describe contrato rico; `ServerTable.tsx` hoy es mínimo
  (ajax + paginación + columns).
- Canon export: `maatwebsite/excel`, `barryvdh/laravel-dompdf` en libraries.md.
- Persistencia columnas: localStorage (clave tipicamente userId + persistKey), no BD.
- TableRequest/TableBuilder + whitelist de filtros (norma backend) deben alinearse con
  header filters y con el modal avanzado (un canal filter).
- Referencias visuales explore: toolbar columnas/page size/Excel-PDF; dropdown
  mostrar/ocultar columnas; fila Acciones con iconos.


## ADDED Requirements

### Requirement: ServerTable con filtros de cabecera, Acciones e i18n
El sistema SHALL implementar en el wrapper único `ServerTable` (Tabulator) filtros por
columna tipados (string/date/select según columna), columna Acciones a la derecha con
iconos, tooltips i18n, Eliminar último + confirm cuando se declare, y localización
completa de UI Tabulator (incl. paginador) al locale de sesión, reactiva al cambio de
idioma sin recarga completa.

#### Scenario: Filtro por columna en Módulos
- **WHEN** el usuario escribe/elige un filtro en la cabecera de una columna del Index de
  Módulos
- **THEN** la tabla solicita datos remotos filtrados según el protocolo table + whitelist

#### Scenario: Acciones solo Edit en Módulos
- **WHEN** se muestra el listado de Módulos
- **THEN** la columna Acciones ofrece Edit (icono + tooltip) y no Delete

#### Scenario: i18n al cambiar idioma
- **WHEN** el usuario cambia el locale en sesión con la tabla montada
- **THEN** títulos, tooltips, paginador y textos Tabulator se muestran en el nuevo idioma
  sin F5

---

### Requirement: Toolbar columnas, page size y export Excel/PDF
El sistema SHALL mostrar en el listado una toolbar con: selector mostrar/ocultar columnas
(persistido por usuario en localStorage), selector de tamaño de página, y acciones de
exportación Excel (.xlsx) y PDF respetando filtros activos cuando el listado lo declare
exportable.

#### Scenario: Persistencia de columnas
- **WHEN** el usuario oculta una columna y recarga o vuelve al listado
- **THEN** la preferencia de visibilidad se conserva (localStorage por usuario + clave de
  listado)

#### Scenario: Export
- **WHEN** el usuario pulsa Excel o PDF en un listado exportable
- **THEN** se genera la descarga en ese formato (vía stack canónico)

---

### Requirement: Modal búsqueda avanzada genérico (opt-in)
El sistema SHALL proveer un componente modal genérico de búsqueda avanzada que recibe los
campos/inputs por parámetro y dispara el filtrado remoto. SHALL integrarse desde la
ActionBar como primer CTA tras Volver cuando la página lo active. En el dashboard de
Módulos SHALL NOT mostrarse el botón.

#### Scenario: Módulos sin búsqueda avanzada
- **WHEN** se renderiza el Index de Módulos
- **THEN** no aparece el botón de búsqueda avanzada en la ActionBar

---

### Requirement: Norma CRUD listados
El sistema SHALL actualizar `docs/standards/frontend.md` (y templates asociados si
aplican) para exigir este patrón en todos los CRUDs/listados ServerTable, salvo excepción
declarada por escrito en el design de un change.

#### Scenario: Norma publicada
- **WHEN** un agente implemente un Index nuevo tras este change
- **THEN** la norma vigente describe filtros de cabecera, Acciones, i18n, toolbar y modal
  avanzado opt-in


## MODIFIED Requirements

### Requirement: (frontend.md §3 ServerTable — contrato)
Sustituir/ampliar el contrato §3 para reflejar header filters, rowActions, toolbar
columnas/page size/export, persistencia localStorage, i18n Tabulator reactiva, y
AdvancedSearch opt-in; primera referencia implementada en Admin Modules Index.
