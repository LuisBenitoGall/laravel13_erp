# Spec Delta

## MODIFIED Requirements

### Requirement: Slug automático con sufijo timestamp
El sistema SHALL generar el slug automáticamente (sin edición en el registro
de Company) y, si ya existe, añadir sufijo timestamp hasta que sea único.
Esta regla aplica a Company en el Create (y onboarding existente), en el
**Update** de Company (regeneración en cada guardado del Edit) y queda
disponible como helper reutilizable para otros conceptos con slug. El
formulario Create y el Edit de Company SHALL NOT exponer un campo editable
de slug. En Update, la comprobación de unicidad SHALL ignorar el propio
registro de la company.

#### Scenario: Colisión de slug
- **WHEN** el slug derivado del name/tradename ya está en uso
- **THEN** se persiste un slug con sufijo timestamp que no colisione

#### Scenario: Create sin campo slug
- **WHEN** un actor abre el Create de Company
- **THEN** no puede editar el slug; se genera en servidor al guardar

#### Scenario: Regeneración en update
- **WHEN** se guarda el Edit de una company
- **THEN** el sistema regenera el slug automáticamente y el valor
  resultante es único (sin colisionar consigo misma ni con otras)

#### Scenario: Edit sin campo slug
- **WHEN** un actor abre el Edit de Company
- **THEN** no puede editar el slug; se regenera en servidor al guardar
