# Spec Delta

## Purpose

Hub de configuración de la company en sesión (`company-settings`) con
asistente de pasos reales (identidad/logo y workplace), modal post-alta
con «Ahora no» y banner mientras la configuración esté incompleta.

## ADDED Requirements

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
El hub SHALL ofrecer modo guiado sobre los pasos reales de este change:
identidad (incl. logo) de la company en sesión y al menos un workplace
Active. SHALL NOT mostrar pasos de módulos inexistentes. La readiness
SHALL ser computada (nif + logo + ≥1 workplace Active).

#### Scenario: Wizard identidad y sede
- **WHEN** el actor abre el asistente con company incompleta
- **THEN** puede completar identidad/logo y disponer de un workplace
  mínimo en secuencia guiada

#### Scenario: Completo sin banner
- **WHEN** la company tiene nif, logo y ≥1 workplace Active
- **THEN** no se muestra el banner de configuración incompleta

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
