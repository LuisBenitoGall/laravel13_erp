# Spec Delta

## ADDED Requirements

### Requirement: Menú Empresas con Configuración de la empresa
El pilar `empresas` SHALL añadir el subítem Configuración de la empresa
(Functionality `company-settings` → `/admin/company-settings`) junto a
Mis empresas, Directorio y Centros de trabajo, sin sustituir esas
entradas.

#### Scenario: Entrada distinta
- **WHEN** el usuario ve el menú Empresas
- **THEN** tiene Configuración de la empresa como entrada distinta de
  Mis empresas, Directorio y Centros de trabajo
