# Spec Delta

## ADDED Requirements

### Requirement: Create Company bajo /admin/companies
El Create de Company SHALL vivir bajo el prefijo `/admin/companies` (rutas
`admin.companies.*`). SHALL NOT usar el prefijo `/partners`.

#### Scenario: URL Create
- **WHEN** un actor autorizado abre el Create de Company
- **THEN** la URL está bajo `/admin/companies/create`
