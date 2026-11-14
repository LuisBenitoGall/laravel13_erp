# Spec Delta

## MODIFIED Requirements

### Requirement: Company atribuye N sectores
Una Company SHALL poder vincularse a cero o más sectores del catálogo
(N:N). La atribución SHALL gestionarse desde el tab Sectores del Edit de
Company (no desde Create ni desde el tab Info). Solo sectores Active
SHALL ser seleccionables. Authz del sync: `company-sectors.update` +
perímetro de vínculo (no bastará el mero `companies.update`).

#### Scenario: Sync sectores
- **WHEN** un actor autorizado con `company-sectors.update` guarda un
  conjunto de sector ids Active en el Edit de una company vinculada
- **THEN** el pivot refleja exactamente ese conjunto

#### Scenario: Create sin sectores
- **WHEN** se crea una company
- **THEN** no se exige atribuir sectores en el Create
