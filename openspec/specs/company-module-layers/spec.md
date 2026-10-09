## Purpose

Tres capas de módulos por empresa: estado global del catálogo, elegibilidad (autorizado sí/no) y activación (`company_modules`), con UI de tarjetas.

## Requirements

### Requirement: Tres capas de módulos (catálogo, elegibilidad, activación)
El sistema SHALL aplicar (1) estado global del Module, (2) elegibilidad por empresa
(autorizado sí/no; Basic autorizados por defecto; Optional y AdminOnly requieren
autorización explícita de Super Admin), y (3) activación por empresa, de modo que el
menú y los accesos solo permitan Functionalities de módulos globales Active, autorizados
y encendidos para la empresa de la sesión. La elegibilidad en este change la asigna solo
Super Admin (flag sí/no; sin pago/invitación reales).

#### Scenario: Module global Inactive
- **WHEN** un Module está Inactive en el catálogo
- **THEN** no se muestra a nadie y se bloquea el acceso a todas sus Functionalities

#### Scenario: Module Active no autorizado para la empresa
- **WHEN** un Module está Active pero la empresa de la sesión no está autorizada
- **THEN** aparece en la UI de selección en estado no activable (gris / equivalente) y no
  puede encenderse para esa empresa

#### Scenario: Module Basic por defecto
- **WHEN** existe una company con cuenta activa y un Module de level Basic en estado
  Active
- **THEN** ese módulo queda autorizado por defecto para esa empresa (visible para
  activar/encender según Capa 2)

#### Scenario: Optional o AdminOnly sin autorización
- **WHEN** un Module Active es Optional o AdminOnly y Super Admin no ha autorizado a la
  empresa
- **THEN** la empresa no puede encenderlo (tarjeta en gris / equivalente)

#### Scenario: Activación por empresa
- **WHEN** la empresa tiene un módulo autorizado y lo tiene encendido en
  `company_modules`
- **THEN** los usuarios en esa sesión pueden alcanzar sus Functionalities sujetos a
  permisos Spatie; si está apagado, no (con independencia de permisos)

#### Scenario: UI de módulos de empresa
- **WHEN** un usuario autorizado gestiona los módulos de su empresa
- **THEN** ve una rejilla de tarjetas (color/icono/selección) similar a v1, con los no
  autorizados en gris u estado equivalente
