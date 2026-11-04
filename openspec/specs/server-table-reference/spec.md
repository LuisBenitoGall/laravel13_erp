## Purpose

Contrato de referencia de listados: único wrapper `ServerTable` (Tabulator), filtros de
cabecera, columna Acciones, i18n reactiva, toolbar (columnas / page size / Excel+PDF),
modal de búsqueda avanzada opt-in, norma CRUD y primera aplicación en Admin Modules.

## Requirements

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

En el Index de Módulos, la columna Acciones SHALL ofrecer Edit en todas las filas
elegibles y Delete (último, con confirm) en módulos no shell. El toggle de estado SHALL
mostrarse solo en filas cuyo nivel es `optional`. En niveles `basic` y `admin_only` el
toggle SHALL NOT renderizarse. Los módulos shell `admin`/`workspace` SHALL NOT ofrecer
Delete. El Index SHALL NOT mostrar columna Estado.

#### Scenario: Filtro por columna en Módulos
- **WHEN** el usuario escribe/elige un filtro en la cabecera de una columna del Index de
  Módulos
- **THEN** la tabla solicita datos remotos filtrados según el protocolo table + whitelist

#### Scenario: Acciones en un módulo optativo
- **WHEN** se muestra una fila de módulo con nivel `optional` y slug no shell
- **THEN** la columna Acciones ofrece toggle, Edit y Delete (confirm), en ese orden

#### Scenario: Sin toggle en básico ni Admin only
- **WHEN** se muestra una fila de módulo con nivel `basic` o `admin_only` y slug no shell
- **THEN** la columna Acciones no ofrece toggle de estado y sí ofrece Edit y Delete
  (último, confirm)

#### Scenario: Toggle sin recarga
- **WHEN** el usuario cambia el toggle de estado de un módulo de nivel `optional` en
  el listado
- **THEN** el estado se persiste sin recargar la página y la fila refleja el nuevo
  estado

#### Scenario: Tamaño del toggle alineado a Acciones
- **WHEN** se renderiza la columna Acciones en Módulos
- **THEN** el toggle tiene un tamaño visual coherente con el resto de botones de esa
  columna (misma escala de la celda)

#### Scenario: Columnas del Index de Módulos
- **WHEN** se muestra el listado de Módulos
- **THEN** las columnas visibles son, en este orden: name, color, icon, level,
  created_at y Acciones; no hay columna `status`/Estado; el slug NO aparece
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

### Requirement: Icono Lucide en Index de Módulos
En el Index de Módulos, la columna de icono SHALL renderizar el icono Lucide
correspondiente al valor almacenado. El nombre del icono SHALL exponerse en el
atributo HTML `title` del elemento del icono (tooltip nativo).

#### Scenario: Icono dibujado con title = nombre
- **WHEN** se muestra una fila de módulo cuyo icono es un nombre Lucide conocido
- **THEN** la celda muestra el icono dibujado y el atributo `title` contiene ese
  nombre de icono

### Requirement: Nivel traducido en Index de Módulos
En el Index de Módulos, la columna Nivel SHALL mostrar la etiqueta i18n del nivel,
no el valor crudo (`admin_only`, `basic`, `optional`, etc.).

#### Scenario: Nivel legible vía i18n
- **WHEN** se muestra una fila de módulo con un nivel conocido
- **THEN** la celda Nivel muestra el texto traducido correspondiente, no el
  slug/código crudo

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

### Requirement: Total de registros a la izquierda del pie ServerTable
El wrapper `ServerTable` SHALL mostrar en el pie de la tabla, a la izquierda y
alineado verticalmente con el paginador de la derecha, el texto i18n equivalente a
`Total: {n}`, donde `{n}` es el total de registros del resultado actual (respetando
filtros aplicados si los hay). SHALL aplicarse a todos los Indexes que usen
`ServerTable` sin configuración adicional por página.

#### Scenario: Total sin filtros
- **WHEN** el usuario abre un Index ServerTable sin filtros activos
- **THEN** el pie izquierdo muestra Total con el número total de registros del
  listado

#### Scenario: Total con filtros
- **WHEN** el usuario aplica filtros de cabecera o búsqueda avanzada
- **THEN** el pie izquierdo actualiza Total al número de coincidencias

#### Scenario: Alineación con paginador
- **WHEN** se renderiza el pie de ServerTable
- **THEN** el total queda a la izquierda y el paginador a la derecha, en la misma
  franja horizontal/vertical del footer

### Requirement: Acciones respetan icono Lucide declarado
Cuando un `RowAction` declare `icon` (componente Lucide), el formatter de Acciones de
`ServerTable` SHALL renderizar ese icono en el botón. SHALL NOT sustituirlo por un
lápiz hardcodeado en botones no-danger. La variante danger MAY seguir usando el
icono de papelera por defecto si no declara `icon`.

#### Scenario: Icono custom en acción no-danger
- **WHEN** una fila declara una acción con `icon` Lucide distinto de Pencil
- **THEN** el botón de Acciones muestra ese icono, no el SVG de lápiz genérico

### Requirement: Ancho de columna Acciones ajustado al contenido
La columna Acciones de `ServerTable` SHALL dimensionarse de forma coherente con el
número de controles (toggles + botones), con `widthGrow` que no robe espacio vacío
desproporcionado al grupo de acciones (p. ej. `widthGrow: 0` y ancho por slots).

#### Scenario: Sin hueco muerto amplio en Acciones
- **WHEN** se renderiza un Index con varias Acciones (p. ej. toggle + 3 botones)
- **THEN** la columna Acciones no deja un hueco vacío amplio a un lado del grupo de
  controles

### Requirement: ServerTable de Functionalities anidado
El listado de Functionalities bajo Module Edit SHALL usar el wrapper `ServerTable`
(R-FE-07). Columnas visibles: label, name, Acciones (toggle status, Edit, Delete con
confirm, en ese orden). SHALL NOT mostrar columna slug. SHALL filtrar por el módulo
padre. Toolbar/export: opt-in según necesidad del listado embebido (mínimo filtros de
cabecera e i18n del contrato ServerTable).

#### Scenario: Acciones Functionalities = toggle + Edit + Delete
- **WHEN** se muestra el ServerTable de Functionalities en Module Edit
- **THEN** Acciones ofrece toggle, Edit y Delete (confirm) en ese orden, sin columna
  slug

#### Scenario: Sin instancia Tabulator fuera de ServerTable
- **WHEN** se implementa el listado de Functionalities anidado
- **THEN** no se instancia Tabulator fuera de `ServerTable`

### Requirement: Norma CRUD listados
El sistema SHALL actualizar `docs/standards/frontend.md` (y templates asociados si
aplican) para exigir este patrón en todos los CRUDs/listados ServerTable, salvo excepción
declarada por escrito en el design de un change. El catálogo §4 SHALL documentar
`AdvancedSearchModal`.

#### Scenario: Norma publicada
- **WHEN** un agente implemente un Index nuevo tras este change
- **THEN** la norma vigente describe filtros de cabecera, Acciones, i18n, toolbar y modal
  avanzado opt-in
