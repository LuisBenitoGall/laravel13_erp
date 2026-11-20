# Spec Delta

## ADDED Requirements

### Requirement: Field helpCode para HelpTip
El componente `Form/Field` SHALL aceptar una prop opcional `helpCode`
(distinta del hint textual `help`) que coloca el icono HelpTip a la
derecha del control hijo. SHALL NOT sustituir ni eliminar el hint
`help` bajo el campo.

#### Scenario: helpCode en Field
- **WHEN** un Field declara helpCode y el code resuelve a un Help Active
- **THEN** el control muestra el icono HelpTip a su derecha

#### Scenario: help y helpCode coexisten
- **WHEN** un Field declara help y helpCode
- **THEN** el hint textual sigue bajo el campo y el icono queda a la
  derecha del control
