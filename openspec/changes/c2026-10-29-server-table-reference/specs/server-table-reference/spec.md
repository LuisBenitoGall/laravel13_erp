## Purpose

Contrato de referencia de listados: único wrapper `ServerTable` (Tabulator), filtros de
cabecera, columna Acciones, i18n reactiva, toolbar (columnas / page size / Excel+PDF),
modal de búsqueda avanzada opt-in, norma CRUD y primera aplicación en Admin Modules.

## ADDED Requirements

### Requirement: ServerTable con filtros de cabecera, Acciones e i18n
El sistema SHALL implementar en el wrapper único `ServerTable` (Tabulator) filtros por
columna tipados (string/date/select según columna), columna Acciones a la derecha con
iconos, tooltips i18n, Eliminar último + confirm cuando se declare, y localización
completa de UI Tabulator (incl. paginador) al locale de sesión, reactiva al cambio de
idioma sin recarga completa. Ningún módulo SHALL instanciar Tabulator fuera de
`ServerTable` (R-FE-07). Los textos de etiqueta visibles de columnas y toolbar de la
tabla SHALL mostrarse con la primera letra en mayúscula vía i18n (p. ej. «Acciones»,
no «acciones»). Los controles de filtro de cabecera (solo en columnas que declaren
filtro) SHALL tener un borde de color suave distintivo.

#### Scenario: Filtro por columna en Módulos
- **WHEN** el usuario escribe/elige un filtro en la cabecera de una columna del Index de
  Módulos
- **THEN** la tabla solicita datos remotos filtrados según el protocolo table + whitelist

#### Scenario: Acciones solo Edit en Módulos
- **WHEN** se muestra el listado de Módulos
- **THEN** la columna Acciones ofrece Edit (icono + tooltip) y no Delete

#### Scenario: Columnas del Index de Módulos
- **WHEN** se muestra el listado de Módulos
- **THEN** las columnas visibles son, en este orden: name, color, icon, level,
  created_at, status y Acciones; el slug NO aparece

#### Scenario: Alineación vertical de celdas
- **WHEN** se renderizan filas del ServerTable (p. ej. color con swatch)
- **THEN** el contenido de las celdas queda alineado verticalmente al centro

#### Scenario: Fecha según locale
- **WHEN** se muestra `created_at` (u otra fecha de tabla) con locale es o ca
- **THEN** el formato es dd/mm/yyyy; con locale en es yyyy/mm/dd (mapa en `lib/format`
  ampliable a nuevos idiomas)

#### Scenario: Alineación horizontal fechas y números
- **WHEN** una columna es fecha (`headerFilterKind`/`valueKind` date) o numérica
  (`headerFilterKind`/`valueKind` number, p. ej. importes con `fmt.money`)
- **THEN** el contenido de la celda (y cabecera por defecto) queda alineado a la derecha

#### Scenario: Slug nunca en tablas
- **WHEN** se renderiza cualquier Index con ServerTable
- **THEN** no se muestra columna `slug` (el slug puede seguir en formularios/API)

#### Scenario: Etiqueta Acciones capitalizada
- **WHEN** se renderiza la columna Acciones
- **THEN** el título visible es la traducción i18n con mayúscula inicial (es: Acciones)

#### Scenario: Borde suave en filtros de cabecera
- **WHEN** una columna declara header filter
- **THEN** su input/select de filtro muestra un borde de color suave; las columnas sin
  filtro no muestran ese control

#### Scenario: i18n al cambiar idioma
- **WHEN** el usuario cambia el locale en sesión con la tabla montada
- **THEN** títulos, tooltips, paginador y textos Tabulator se muestran en el nuevo idioma
  sin F5

---

### Requirement: Toolbar columnas, page size y export Excel/PDF
El sistema SHALL mostrar en el listado una toolbar con: selector mostrar/ocultar columnas
(persistido por usuario en localStorage), selector de tamaño de página, y acciones de
exportación Excel (.xlsx) y PDF respetando filtros activos cuando el listado lo declare
exportable. El endpoint `table` SHALL aceptar `export=xlsx|csv|pdf` con la misma
whitelist de filtros/sort; Excel vía `maatwebsite/excel`, PDF vía
`barryvdh/laravel-dompdf`.

#### Scenario: Persistencia de columnas
- **WHEN** el usuario oculta una columna y recarga o vuelve al listado
- **THEN** la preferencia de visibilidad se conserva (localStorage por usuario + clave de
  listado)

#### Scenario: Export Excel
- **WHEN** el usuario pulsa Excel en un listado exportable con filtros activos
- **THEN** se descarga un `.xlsx` que respeta esos filtros (stack canónico)

#### Scenario: Export PDF
- **WHEN** el usuario pulsa PDF en un listado exportable con filtros activos
- **THEN** se descarga un PDF que respeta esos filtros (stack canónico)

---

### Requirement: Modal búsqueda avanzada genérico (opt-in)
El sistema SHALL proveer un componente modal genérico de búsqueda avanzada que recibe los
campos/inputs por parámetro y dispara el filtrado remoto por el mismo canal `filter[]`.
SHALL integrarse desde la ActionBar como primer CTA tras Volver cuando la página lo
active. En el dashboard de Módulos SHALL NOT mostrarse el botón.

#### Scenario: Módulos sin búsqueda avanzada
- **WHEN** se renderiza el Index de Módulos
- **THEN** no aparece el botón de búsqueda avanzada en la ActionBar

#### Scenario: Modal aplica filtros
- **WHEN** una página Index activa advanced search y el usuario aplica el modal
- **THEN** `ServerTable` solicita datos con los filtros del modal en el protocolo table

---

### Requirement: Norma CRUD listados
El sistema SHALL actualizar `docs/standards/frontend.md` (y templates asociados si
aplican) para exigir este patrón en todos los CRUDs/listados ServerTable, salvo excepción
declarada por escrito en el design de un change. El catálogo §4 SHALL documentar
`AdvancedSearchModal`.

#### Scenario: Norma publicada
- **WHEN** un agente implemente un Index nuevo tras este change
- **THEN** la norma vigente describe filtros de cabecera, Acciones, i18n, toolbar y modal
  avanzado opt-in
