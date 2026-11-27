# Spec Delta

## MODIFIED Requirements

### Requirement: Edit Delegación — un solo listado “Sus centros”
El Edit de Delegación SHALL mostrar un único listado de workplaces de la
matriz asociados a esa Delegación mediante `<ServerTable>` (enlace a Edit
de la sede; sede preferida de la Delegación editable desde esa ficha).
SHALL NOT ofrecer CRUD de centros con dueño = hija ni un segundo bloque
paralelo con otra semántica. Copy en lenguaje llano (sin jerga N:M / mapa).

#### Scenario: Solo sus centros asociados
- **WHEN** el actor abre el Edit de una Delegación con sedes asociadas
- **THEN** ve esas sedes en un único ServerTable comprensible, sin dos
  categorías técnicas de “centros”

#### Scenario: Sin centros asociados
- **WHEN** la Delegación no tiene vínculos
- **THEN** empty state con copy de ayuda hacia Centros de trabajo (vincular
  desde Edit Workplace; sin CTA botón obligatorio en la ficha)

#### Scenario: Ya no hay panel CRUD de centros de la hija
- **WHEN** un actor abre el Edit de Delegación
- **THEN** no puede crear/editar/borrar workplaces con `company_id` = esa
  Delegación desde esa ficha
