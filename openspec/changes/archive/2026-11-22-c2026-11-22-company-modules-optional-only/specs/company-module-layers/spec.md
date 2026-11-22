# Spec Delta

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

### Requirement: Basic siempre on sin toggle en Mis módulos
Los Modules Basic Active y autorizados para la company SHALL estar
disponibles para menú/acceso de esa company sin exponerse en Mis módulos y
sin Encender/Apagar ahí. Enable/Disable de company-modules SHALL NOT aplicar
a Basic.

#### Scenario: Basic no en Mis módulos
- **WHEN** un actor abre Mis módulos
- **THEN** no ve tarjetas Basic ni puede apagarlas desde esa pantalla

#### Scenario: Basic disponible sin company_modules
- **WHEN** un Module Basic Active está autorizado para la company de sesión
- **THEN** menú/acceso lo tratan como disponible aunque no haya fila de
  encendido en `company_modules`

### Requirement: AdminOnly fuera de Mis módulos
Los Modules AdminOnly SHALL NOT aparecer en Mis módulos. SHALL seguir
disponibles para Super Admin según el circuito de menú/acceso SA.

#### Scenario: Configuración no en Mis módulos
- **WHEN** un actor abre Mis módulos
- **THEN** no ve tarjetas AdminOnly (p. ej. Configuración)

### Requirement: Enable Disable solo Optional
Las Actions de encender/apagar módulos de empresa SHALL aceptar solo Modules
Optional. SHALL rechazar Basic y AdminOnly.

#### Scenario: Enable Basic rechazado
- **WHEN** se fuerza enable de un Module Basic para la company
- **THEN** la operación se rechaza

## MODIFIED Requirements

### Requirement: Tres capas de módulos (catálogo, elegibilidad, activación)
El sistema SHALL aplicar (1) estado global del Module, (2) elegibilidad por
empresa (autorizado sí/no; Basic autorizados por defecto; Optional y
AdminOnly requieren autorización explícita de Super Admin), y (3) activación
por empresa **para Modules Optional** (encender/apagar en Mis módulos /
`company_modules`). Modules Basic Active y autorizados SHALL considerarse
siempre disponibles para la company sin toggle en Mis módulos. AdminOnly
SHALL NOT gestionarse en Mis módulos (disponibilidad Super Admin). El menú y
los accesos de Optional exigen Active global, autorizado y encendido; Basic
exige Active global y autorizado; AdminOnly + Super Admin mantiene la
excepción SA. La elegibilidad la asigna Super Admin (flag sí/no; sin
pago/invitación reales) acotada a companies vinculadas (`user_companies`).

#### Scenario: Module global Inactive
- **WHEN** un Module está Inactive en el catálogo
- **THEN** no se muestra a nadie y se bloquea el acceso a todas sus
  Functionalities

#### Scenario: Module Active no autorizado para la empresa
- **WHEN** un Module Optional está Active pero la empresa de la sesión no
  está autorizada
- **THEN** aparece en Mis módulos en estado no activable (gris / equivalente)
  y no puede encenderse para esa empresa

#### Scenario: Module Basic por defecto
- **WHEN** existe una company con cuenta activa y un Module de level Basic
  en estado Active
- **THEN** ese módulo queda autorizado por defecto para esa empresa y
  disponible sin pasar por Encender en Mis módulos

#### Scenario: Optional o AdminOnly sin autorización
- **WHEN** un Module Active es Optional y Super Admin no ha autorizado a la
  empresa
- **THEN** la empresa no puede encenderlo (tarjeta en gris / equivalente en
  Mis módulos si el módulo es Optional Active)
- **WHEN** un Module Active es AdminOnly
- **THEN** no aparece en Mis módulos

#### Scenario: Activación por empresa
- **WHEN** la empresa tiene un Optional autorizado y lo tiene encendido en
  `company_modules`
- **THEN** los usuarios en esa sesión pueden alcanzar sus Functionalities
  sujetos a permisos Spatie; si está apagado, no (con independencia de
  permisos)

#### Scenario: UI de módulos de empresa
- **WHEN** un usuario autorizado gestiona los módulos de su empresa
- **THEN** ve una rejilla de tarjetas solo de Optional Active (color/icono),
  con los no autorizados en gris u estado equivalente

#### Scenario: Elegibilidad solo sobre companies vinculadas
- **WHEN** Super Admin abre la UI de autorización de módulos por empresa
- **THEN** el selector de companies solo incluye empresas a las que está
  vinculado

#### Scenario: Authorize/revoke fuera de perímetro rechazado
- **WHEN** Super Admin intenta autorizar o revocar un módulo para una company
  a la que no está vinculado
- **THEN** la operación se rechaza
