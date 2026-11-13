# Spec Delta

## ADDED Requirements

### Requirement: Edit Company bajo /admin/companies
El Edit de Company SHALL vivir bajo `/admin/companies/{id}/edit` (rutas
`admin.companies.*`). SHALL NOT usar el prefijo `/partners`.

#### Scenario: URL Edit
- **WHEN** un actor autorizado abre el Edit de una company
- **THEN** la URL está bajo `/admin/companies/.../edit`
