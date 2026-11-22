<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-22-company-modules-optional-only
Rule: 10-architecture
Summary: Alinear Mis módulos (`/admin/company-modules`) a Optional Active
  globales únicamente: sin Basic ni AdminOnly en la rejilla; Basic siempre
  on para la company; AdminOnly solo Super Admin fuera de esta pantalla;
  SA no gestiona Mis módulos de companies ajenas.
Goals:
- Mis módulos = registro de módulos Optional que la company de sesión usa o
  puede usar (encender/apagar).
- Acceso: usuarios con permisos de la company (`company-modules.*`); Super
  Admin puede usar Mis módulos **solo de sus companies** (perímetro
  vinculado + sesión), no de empresas ajenas. SA sigue creando/editando y
  activando/desactivando módulos a **nivel global** (catálogo), no «Mis
  módulos» de terceros.
- En la rejilla SHALL mostrarse **solo** módulos con level **Optional** y
  status **Active** en el catálogo global. Inactive global → no se muestran.
- **Basic**: no se listan; «siempre están» para la company (no Encender/
  Apagar en esta pantalla).
- **AdminOnly**: no se listan en Mis módulos; «siempre están» pero **solo
  para Super Admin** (menú/acceso SA), no como toggle de empresa.
- Optional Active global sin autorización de company: se muestran (p. ej.
  estado no encendible / requiere autorización); el filtro de visibilidad
  es Optional+Active global, no «solo ya autorizados».
- Actualizar contrato que choque (kernel §5 / `company-module-layers` /
  UI / Actions / tests) para que Basic no sea apagable desde esta pantalla
  y no aparezca; Enable/Disable no apliquen a Basic ni AdminOnly aquí.
NonGoals:
- Rediseñar la pantalla de elegibilidad Super Admin
  (`company-module-authorizations`) salvo lo imprescindible para coherencia.
- Cambiar el catálogo global Modules (CRUD) más allá de la semántica de
  capas que afecte a Mis módulos.
- Facturación / planes de pago por módulo.
Skills:
- openspec-propose
- tenancy-rbac
Notes:
- Origen: `/opsx-explore` clarificación Mis módulos vs captura actual (Basic
  con Apagar; AdminOnly y Optional no autorizado en gris).
- Contradicción actual: Index lista todos los Active y permite Apagar Basic;
  spec/kernel contemplan activación también de Basic.
- «Habilitados a nivel global» = `modules.status` Active (no confundir con
  `company_module_authorizations`).
- 10 debe decidir el mecanismo técnico de «Basic siempre on» (p. ej. menú
  trata Basic autorizado+encendido de forma implícita, o filas
  `company_modules` siempre presentes / no soft-deletables) **sin** inventar
  reglas de negocio no debatidas: el debate exige que Basic no se muestre ni
  se pueda apagar en Mis módulos y que «siempre estén».


## ADDED Requirements

### Requirement: Mis módulos solo Optional Active globales
La pantalla `/admin/company-modules` SHALL listar únicamente Modules con
level Optional y status Active en el catálogo global. SHALL NOT listar
Basic ni AdminOnly. Modules Inactive globales SHALL NOT aparecer.

#### Scenario: Solo Optional Active en la rejilla
- **WHEN** un actor autorizado abre Mis módulos
- **THEN** ve solo tarjetas de módulos Optional Active (ninguna Basic ni
  AdminOnly)

#### Scenario: Inactive global oculto
- **WHEN** un Module Optional está Inactive en el catálogo
- **THEN** no aparece en Mis módulos

### Requirement: Basic siempre disponibles sin toggle en Mis módulos
Los Modules Basic Active SHALL permanecer disponibles para la company de
sesión sin exponerse en Mis módulos ni ofrecer Encender/Apagar ahí.

#### Scenario: Basic no apagable desde Mis módulos
- **WHEN** un actor abre Mis módulos
- **THEN** no ve tarjetas Basic ni puede apagar Dashboard/Usuarios/Empresas/
  Cuenta (u otros Basic) desde esa pantalla

### Requirement: AdminOnly solo Super Admin fuera de Mis módulos
Los Modules AdminOnly SHALL NOT aparecer en Mis módulos. SHALL seguir
disponibles para Super Admin según el circuito de menú/acceso SA (no como
activación de empresa en esta pantalla).

#### Scenario: Configuración no en Mis módulos
- **WHEN** un actor abre Mis módulos
- **THEN** no ve la tarjeta AdminOnly (p. ej. Configuración)

### Requirement: Super Admin perímetro en Mis módulos
Super Admin SHALL poder gestionar Mis módulos solo para companies a las que
está vinculado y con esa company en sesión. SHALL NOT gestionar Mis módulos
de companies ajenas. La gestión del catálogo global de Modules permanece
separada.

#### Scenario: SA en su company
- **WHEN** Super Admin con company de sesión propia vinculada abre Mis
  módulos
- **THEN** opera sobre Optional Active de esa company según las reglas de
  esta pantalla

#### Scenario: SA no gestiona company ajena vía Mis módulos
- **WHEN** Super Admin no tiene una company ajena como sesión/perímetro
  aplicable
- **THEN** no gestiona los módulos de esa company desde Mis módulos

---

## MODIFIED Requirements

### Requirement: Tres capas de módulos (catálogo, elegibilidad, activación)
La capa de activación expuesta en Mis módulos SHALL aplicarse a Optional
Active (encender/apagar si autorizados; sin autorización no encendibles pero
visibles si Active global). Basic no se activan/desactivan en esa UI (siempre
disponibles). AdminOnly no entra en Mis módulos (disponibilidad SA). El resto
de elegibilidad Super Admin y Active/Inactive global se mantiene en espíritu,
ajustado a esta semántica de UI.

#### Scenario: Module Active no autorizado para la empresa
- **WHEN** un Module Optional está Active pero la empresa no está autorizada
- **THEN** aparece en Mis módulos en estado no encendible (gris / equivalente)

#### Scenario: Activación por empresa
- **WHEN** la empresa tiene un Optional autorizado y lo enciende en
  `company_modules`
- **THEN** los usuarios en esa sesión pueden alcanzar sus Functionalities
  sujetos a permisos Spatie; si está apagado, no
