# Spec Delta

## ADDED Requirements

### Requirement: Menú Empresas con Centros de trabajo
El pilar `empresas` SHALL añadir el subítem Centros de trabajo
(Functionality `workplaces` → `/admin/workplaces`) junto a Mis empresas y
Directorio, sin sustituir esas entradas.

#### Scenario: Entrada distinta
- **WHEN** el usuario ve el menú Empresas
- **THEN** tiene Centros de trabajo como entrada distinta de Mis empresas
  y Directorio
