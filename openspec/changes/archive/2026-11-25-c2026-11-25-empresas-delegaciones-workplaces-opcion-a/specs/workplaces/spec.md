# Spec Delta — workplaces

## ADDED Requirements

### Requirement: Centros de trabajo del grupo viven en la matriz
En el modelo de grupo societario (opción A), los workplaces operativos SHALL
pertenecer a la Company matriz. El menú Centros de trabajo (sesión matriz)
SHALL ser el único CRUD de alta/edición de esas sedes, incluida la gestión del
vínculo N:N a Delegaciones.

#### Scenario: CRUD solo en Centros de trabajo
- **WHEN** un actor gestiona sedes del grupo
- **THEN** lo hace bajo `/admin/workplaces` (matriz), no creando workplaces
  con dueño = Delegación
