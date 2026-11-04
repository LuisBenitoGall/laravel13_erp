<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-04-server-table-total
Rule: 10-architecture
Summary: Mostrar en el pie de ServerTable (izquierda, alineado vertical con el
  paginador) el total de registros del resultado actual, formato «Total: N», para
  todas las tablas presentes y futuras vía el wrapper único.
Goals:
- Proponer change que actualice el contrato `server-table-reference` / wrapper
  `ServerTable` (Tabulator).
- Pie de tabla: texto a la **izquierda**, misma franja que el paginador a la
  **derecha**, alineación vertical con ese paginador.
- Contenido: **opción A** — `Total: {n}` (i18n; no rango de página).
- `{n}` = total del resultado **actual** (sin filtros = todos; con filtros de
  cabecera/avanzados = coincidencias). Fuente: campo `total` que ya devuelve
  `TableBuilder` (hoy el `ajaxResponse` de ServerTable no lo reenvía).
- Aplica a **todas** las tablas que usan ServerTable (presentes y futuras); sin
  opt-in por Index.
NonGoals:
- Contador “Mostrando X–Y de Z” (opción C descartada).
- Segundo total “sin filtrar” junto al filtrado.
- Cambios de backend TableBuilder salvo si hace falta exponer/documentar `total`
  (ya existe).
- Polish geografía / otros Indexes salvo el efecto global del wrapper.
Skills:
- openspec-propose (change nuevo)
- inertia-react-development / frontend ServerTable
Notes:
- Origen: `/opsx-explore`; captura pie vacío a la izquierda + paginador derecha.
- Luis elige wording **A** (`Total: N`).
- 10 decide en design el mecanismo (estado React + DOM en `.tabulator-footer`,
  footer element Tabulator, CSS flex, etc.); contrato UX = izquierda + alineado
  al paginador + i18n.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

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
