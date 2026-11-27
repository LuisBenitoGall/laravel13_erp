## Purpose

Hub de configuración de la company en sesión (`company-settings`) con
asistente de pasos reales (identidad/logo, workplace y monedas), modal
post-alta con «Ahora no» y banner mientras la configuración esté incompleta.

## Requirements

### Requirement: Hub company-settings bajo Empresas
El sistema SHALL exponer Functionality `company-settings` bajo Module
`empresas` con entrada de menú hacia el hub
`/admin/company-settings` de la company en sesión, con Policy y permisos
Spatie canónicos.

#### Scenario: Entrada de menú
- **WHEN** un usuario con `empresas.access` y `company-settings.view`
  obtiene el menú
- **THEN** ve Configuración de la empresa bajo Empresas y puede abrir el
  hub

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

### Requirement: Paso Monedas en company-settings
El hub Configuración de la empresa SHALL incluir un paso/tab real Monedas
para la company en sesión, permitiendo sincronizar permitidas + default
con la misma semántica que el Edit de Company.

#### Scenario: Completar monedas en el hub
- **WHEN** el actor guarda una moneda por defecto válida en el paso Monedas
- **THEN** el criterio de readiness de monedas queda satisfecho

### Requirement: Modal post-alta y opción Ahora no
Tras crear una company (onboarding o Mis empresas) que quede incompleta,
el sistema SHALL mostrar un modal informativo con CTA al hub y opción
«Ahora no» que permite continuar al dashboard sin bloqueo.

#### Scenario: Ahora no
- **WHEN** el actor elige «Ahora no» en el modal
- **THEN** llega al dashboard (o destino post-login) sin quedar bloqueado

### Requirement: Banner si configuración incompleta
Mientras la company de sesión no cumpla la readiness de los pasos reales,
el sistema SHALL mostrar un banner persistente en el shell con enlace a
`company-settings`.

#### Scenario: Banner tras posponer
- **WHEN** la company sigue incompleta tras «Ahora no» o sin terminar el
  asistente
- **THEN** el shell muestra banner con acceso al hub
