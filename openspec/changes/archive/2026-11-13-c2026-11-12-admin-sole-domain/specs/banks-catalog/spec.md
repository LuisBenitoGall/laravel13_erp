# Spec Delta

## MODIFIED Requirements

### Requirement: Catálogo global Bank (dominio Banks)
El sistema SHALL persistir bancos como catálogo global sin `company_id`, en el
dominio **Admin** (ya no existe dominio PHP Banks separado), con soft delete y
status Active/Inactive (columna string + enum PHP). Bank SHALL incluir name,
slug, tradename, lei, eu_code, supervisor_code, swift, swift_source y
swift_updated_at.

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

#### Scenario: Código en Admin
- **WHEN** se revisa el árbol de dominios
- **THEN** Bank vive bajo `App\Domains\Admin`, no bajo `App\Domains\Banks`
