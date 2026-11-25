# Spec Delta — company-delegations

## ADDED Requirements

### Requirement: Panel RO de sedes de matriz vinculadas a la Delegación
El Edit de Delegación SHALL mostrar un panel de solo lectura con los Workplaces
de la matriz (`company_id` = `parent_id` de la Delegación) que tienen esta
Delegación en el mapeo N:M (`workplace_delegation`), indicando si es la
preferida de esa sede. SHALL NOT permitir editar el mapeo desde ese panel (la
escritura sigue en Edit Workplace). SHALL incluir copy que distinga esos
centros de los workplaces cuyo `company_id` es la propia Delegación.

#### Scenario: Lista vínculos desde la hija
- **WHEN** la matriz tiene un workplace vinculado a la Delegación D (preferida
  o no) y un actor abre el Edit de D
- **THEN** ese workplace aparece en el panel RO con el indicador de preferida
  si aplica

#### Scenario: Sin vínculos
- **WHEN** ningún workplace de la matriz tiene a D en el mapeo
- **THEN** el panel RO se muestra vacío (o empty state), sin error

#### Scenario: Copy distintivo
- **WHEN** el actor ve el Edit de Delegación
- **THEN** el texto deja claro la diferencia entre centros de la Delegación y
  centros de la matriz que la vinculan

#### Scenario: Edit Delegación con panel RO
- **WHEN** un actor autorizado abre `/admin/delegations/{id}/edit`
- **THEN** ve el panel de sedes de matriz vinculadas (RO) además del resto de
  la ficha
