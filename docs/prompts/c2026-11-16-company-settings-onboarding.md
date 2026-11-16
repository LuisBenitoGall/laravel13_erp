<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-16-company-settings-onboarding
Rule: 10-architecture
Summary: Hub «Configuración de la empresa» (`company-settings` bajo
  Empresas) con asistente guiado de pasos reales actuales (identidad/logo
  + workplace mínimo). Tras crear/entrar con company incompleta: modal
  con CTA al hub y «Ahora no»; si pospone o queda incompleto, banner
  persistente. Más pasos se añadirán módulo a módulo (preguntar siempre).
Goals:
- Functionality `company-settings` bajo Module `empresas`; ruta bajo
  `/admin/...` (p. ej. `/admin/company-settings`); menú Empresas →
  Configuración de la empresa (label acordado).
- Vista hub con progreso + modo guiado (asistente) y acceso libre a tabs
  de pasos reales.
- Pasos reales Fase 0 de este change: identidad/logo de la company en
  sesión + al menos un centro de trabajo (workplace).
- Post-create / company incompleta: modal informativo con enlace al hub
  y opción «Ahora no» (sigue al dashboard).
- Si incompleto tras «Ahora no» o sin terminar: banner persistente en
  shell con enlace al hub.
- Soft-gates duros por capacidad de negocio: fuera de este change
  (solo informar / guiar).
NonGoals:
- Muro total del ERP sin «Ahora no».
- Tabs/pasos de dominios aún no existentes (series, plan contable,
  almacén, SEPA, pivot payment methods, etc.).
- Sustituir Configuración SA (catálogos globales) ni Cuenta/Mis módulos.
- Incluir automáticamente todo módulo nuevo en el onboarding sin que
  Luis lo pida explícitamente (protocolo: preguntar cada vez).
Skills:
- openspec-propose
- tenancy-rbac
- new-module
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore`. Visión v2 ya cita asistente de onboarding;
  hoy solo OnboardCompany mínimo + CreateCompany (roles/Basic).
- Decisión Luis: modal 1ª vez + «Ahora no» + banner; pasos reales solo;
  crecer bajo indicación explícita por módulo (preguntar siempre).
- Distinguir de Edit Mis empresas / workplaces CRUD: el hub orquesta y
  guía; puede reutilizar pantallas/acciones existentes.
- Readiness: preferible computada (hechos de dominio) frente a flags
  sueltos; detalle en design 10-architecture.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Hub company-settings bajo Empresas
El sistema SHALL exponer Functionality `company-settings` bajo Module
`empresas` con entrada de menú hacia el hub de configuración de la
company en sesión, con Policy/permisos Spatie canónicos.

#### Scenario: Entrada de menú
- **WHEN** un usuario con `empresas.access` y permiso de la Functionality
  obtiene el menú
- **THEN** ve Configuración de la empresa bajo Empresas y puede abrir el
  hub

### Requirement: Asistente con pasos reales actuales
El hub SHALL ofrecer modo guiado (asistente) sobre los pasos reales
disponibles en este change: completar identidad/logo de la company y
disponer de al menos un workplace. SHALL NOT mostrar pasos de módulos
inexistentes.

#### Scenario: Wizard identidad y sede
- **WHEN** el actor abre el asistente con company incompleta
- **THEN** puede completar identidad/logo y alta/selección de workplace
  mínimo en secuencia guiada

### Requirement: Modal post-alta y opción Ahora no
Tras desembocar en el ERP con una company nueva o incompleta (primera o
adicional), el sistema SHALL mostrar un modal que informe de la
configuración pendiente, con CTA al hub y opción «Ahora no» que permite
continuar al dashboard.

#### Scenario: Ahora no
- **WHEN** el actor elige «Ahora no» en el modal
- **THEN** llega al dashboard (o destino post-login) sin quedar bloqueado

### Requirement: Banner si configuración incompleta
Mientras la company de sesión no cumpla los pasos reales del hub, el
sistema SHALL mostrar un banner persistente con enlace a
`company-settings`.

#### Scenario: Banner tras posponer
- **WHEN** la company sigue incompleta tras «Ahora no» o sin terminar el
  asistente
- **THEN** el shell muestra banner con acceso al hub


## MODIFIED Requirements

### Requirement: Menú Empresas con Configuración de la empresa
(El pilar `empresas` con Mis empresas / Directorio / Centros de trabajo.)
SHALL añadir el subítem Configuración de la empresa
(`company-settings`) sin sustituir las entradas existentes.

#### Scenario: Entrada distinta
- **WHEN** el usuario ve el menú Empresas
- **THEN** tiene Configuración de la empresa como entrada distinta de
  Mis empresas, Directorio y Centros de trabajo
