## Purpose

Vistas placeholder de aviso y términos con aceptación obligatoria en el registro.

## Requirements

### Requirement: Aviso y términos (vistas placeholder)
El sistema SHALL exigir aceptación de aviso y términos en el registro y SHALL exponer
vistas propias del proyecto para consultarlos (contenido placeholder hasta textos
definitivos).

#### Scenario: Sin aceptación no hay registro
- **WHEN** el visitante intenta registrarse sin marcar la aceptación
- **THEN** la validación falla

#### Scenario: Consulta de textos
- **WHEN** el visitante abre el enlace de aviso o términos
- **THEN** ve una vista propia del proyecto (placeholder)
