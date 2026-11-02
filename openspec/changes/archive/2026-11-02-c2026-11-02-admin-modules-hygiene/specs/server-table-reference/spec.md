# Spec Delta

## MODIFIED Requirements

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
