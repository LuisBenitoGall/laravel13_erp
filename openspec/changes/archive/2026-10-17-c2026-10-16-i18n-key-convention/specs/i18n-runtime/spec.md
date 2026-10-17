## MODIFIED Requirements

### Requirement: JSON por locale con claves alfabéticas
Las traducciones SHALL vivir en `lang/es.json`, `lang/en.json` y `lang/ca.json`. Las
claves DENTRO de cada fichero SHALL estar ordenadas alfabéticamente. El conjunto de
claves SHALL ser el mismo en los tres ficheros. Los **nombres de clave** SHALL seguir
la convención castellana snake_case reutilizable (capability `i18n-key-convention`),
no el esquema jerárquico en inglés con puntos.

#### Scenario: Orden y paridad
- **WHEN** corre el enforcement de CI/arquitectura sobre los JSON
- **THEN** falla si faltan claves entre locales o si el orden no es alfabético

#### Scenario: Convención de nombre de clave
- **WHEN** se inspeccionan las claves de los JSON tras este change
- **THEN** cumplen el patrón de índices en castellano sin puntos jerárquicos
