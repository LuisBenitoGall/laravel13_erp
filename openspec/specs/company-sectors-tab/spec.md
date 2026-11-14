## Purpose

Tab Sectores en el Edit de Company para atribuir sectores Active, con
Functionality `company-sectors` (`.view` / `.update`) bajo Module
`empresas`, reutilizando el pivot existente.

## Requirements

### Requirement: Tab Sectores en Edit Company
El Edit de Company SHALL incluir un tab «Sectores» donde el actor marca
cero o más sectores Active del catálogo. SHALL NOT mostrar ese panel en
el tab Info ni en Create.

#### Scenario: Tab Sectores
- **WHEN** un actor con `company-sectors.view` abre el Edit de una
  company
- **THEN** ve el tab Sectores con la lista de sectores Active y los
  actualmente vinculados

#### Scenario: Sin panel en Info/Create
- **WHEN** se abre Info del Edit o el Create
- **THEN** no está el panel de atribución de sectores en esas pantallas

### Requirement: Permisos company-sectors.view y .update
La visibilidad del tab Sectores SHALL exigir `company-sectors.view`.
Guardar la sincronización de sectores SHALL exigir
`company-sectors.update`. SHALL mantener el perímetro de vínculo a la
company. SHALL NOT exigir un modelo Eloquent `CompanySector`.

#### Scenario: Ver tab sin update
- **WHEN** el actor tiene `company-sectors.view` pero no `.update`
- **THEN** puede ver el tab en solo lectura y no persistir el sync

#### Scenario: Sync con update
- **WHEN** el actor con `company-sectors.update` guarda sector_ids
  válidos Active en una company vinculada
- **THEN** el pivot refleja el conjunto guardado

#### Scenario: Sync sin update
- **WHEN** un actor sin `company-sectors.update` llama al sync
- **THEN** la operación se rechaza
