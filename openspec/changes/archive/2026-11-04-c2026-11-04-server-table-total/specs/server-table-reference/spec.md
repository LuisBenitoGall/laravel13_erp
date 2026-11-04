# Spec Delta

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
