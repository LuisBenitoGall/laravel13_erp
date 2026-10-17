## Purpose

Helper reutilizable de slug único con sufijo timestamp ante colisión; usado en alta de Company (onboarding/CRUD).

## Requirements

### Requirement: Slug automático con sufijo timestamp
El sistema SHALL generar el slug automáticamente (sin edición en el registro de Company) y,
si ya existe, añadir sufijo timestamp hasta que sea único. Esta regla aplica a Company en
este change y queda disponible como helper reutilizable para otros conceptos con slug.

#### Scenario: Colisión de slug
- **WHEN** el slug derivado del name/tradename ya está en uso
- **THEN** se persiste un slug con sufijo timestamp que no colisione
