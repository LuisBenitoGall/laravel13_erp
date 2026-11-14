# Spec Delta

## MODIFIED Requirements

### Requirement: Edit Company con tabs Info e Imágenes
El Edit de Company SHALL usar layout con tabs al estilo ficha User
(`RecordEditLayout`). Tab Info: readout a la izquierda (logo rectangular
centrado V+H + info de la empresa) y formulario a la derecha. Tab
Imágenes: gestión del único logo. Tab jerarquía: padre e hijos (ver
capability `company-hierarchy`). SHALL NOT mostrar link ni tab de
Miembros en este Edit. SHALL NOT poner controles de jerarquía en el
Create.

#### Scenario: Tabs Info e Imágenes
- **WHEN** un actor autorizado abre el Edit de una company
- **THEN** ve tabs Info e Imágenes (sin tab/link de Miembros en esta UI)

#### Scenario: Readout Info
- **WHEN** está en el tab Info
- **THEN** el bloque izquierdo muestra el logo (rectangular, centrado
  vertical y horizontal) y la info de la empresa; a la derecha el
  formulario editable

#### Scenario: Tabs Info, Imágenes y jerarquía
- **WHEN** un actor abre el Edit tras este change
- **THEN** ve los tabs de Info e Imágenes y el nuevo tab de jerarquía
