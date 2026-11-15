# Spec Delta

## ADDED Requirements

### Requirement: Menú Empresas con Mis empresas y Directorio
El pilar `empresas` (con Mis empresas) SHALL añadir el subítem Directorio
(Functionality `company-directory` → `/admin/company-directory`) junto a Mis
empresas, sin sustituir el Index de Mis empresas.

#### Scenario: Dos entradas
- **WHEN** el usuario ve el menú Empresas
- **THEN** tiene Mis empresas y Directorio como entradas distintas
