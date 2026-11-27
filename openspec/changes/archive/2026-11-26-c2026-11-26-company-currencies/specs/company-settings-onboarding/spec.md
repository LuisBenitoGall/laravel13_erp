# Spec Delta

## MODIFIED Requirements

### Requirement: Asistente con pasos reales actuales
El hub SHALL ofrecer modo guiado sobre los pasos reales: identidad (incl.
logo) de la company en sesión, al menos un workplace Active, y monedas de
uso con moneda por defecto. SHALL NOT mostrar pasos de módulos inexistentes.
La readiness SHALL ser computada (nif + logo + ≥1 workplace Active + moneda
por defecto). Soft-gates (modal «Ahora no», banner) SHALL seguir sin muro
total del ERP.

#### Scenario: Wizard identidad y sede
- **WHEN** el actor abre el asistente con company incompleta
- **THEN** puede completar identidad/logo, disponer de un workplace mínimo
  y configurar moneda por defecto en secuencia guiada

#### Scenario: Completo sin banner
- **WHEN** la company tiene nif, logo, ≥1 workplace Active y moneda por
  defecto
- **THEN** no se muestra el banner de configuración incompleta

#### Scenario: Readiness incompleta sin default
- **WHEN** la company en sesión tiene nif, logo y ≥1 workplace Active pero
  no moneda por defecto
- **THEN** readiness incompleta y el shell puede mostrar el banner hacia
  el hub

## ADDED Requirements

### Requirement: Paso Monedas en company-settings
El hub Configuración de la empresa SHALL incluir un paso/tab real Monedas
para la company en sesión, permitiendo sincronizar permitidas + default
con la misma semántica que el Edit de Company.

#### Scenario: Completar monedas en el hub
- **WHEN** el actor guarda una moneda por defecto válida en el paso Monedas
- **THEN** el criterio de readiness de monedas queda satisfecho
