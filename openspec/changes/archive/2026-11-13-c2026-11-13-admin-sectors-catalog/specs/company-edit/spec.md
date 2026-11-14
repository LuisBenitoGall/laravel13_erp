# Spec Delta

## ADDED Requirements

### Requirement: Atribución de sectores en Edit Company
El Edit de Company SHALL permitir atribuir cero o más sectores Active del
catálogo global. SHALL NOT exigir sectores en Create. Authz: Policy update
+ perímetro de vínculo.

#### Scenario: Guardar sectores
- **WHEN** el actor autorizado selecciona sectores y guarda en Edit
- **THEN** la company queda vinculada a esos sectores (N:N)

#### Scenario: Create sin UI de sectores
- **WHEN** un actor abre el Create de Company
- **THEN** no se exige ni se muestra la atribución de sectores como
  requisito del alta mínima
