## Purpose

Visibilidad de menú R-AUT-08: Module Active + autorizado + encendido para la empresa de sesión, más regla OR comodín/Functionality. `EnsureModuleIsActive` aplica la misma triple condición en rutas.

## Requirements

### Requirement: Visibilidad de menú corregida
El sistema SHALL mostrar un `Module` en el menú si, para la empresa de la sesión, el
Module está Active globalmente, la empresa está autorizada a él, lo tiene encendido, y
además el usuario tiene el permiso comodín `<module-slug>.access` **o** al menos un
permiso de alguna `Functionality` del módulo. SHALL mostrar cada `Functionality` si el
usuario tiene el comodín de su módulo o su propio permiso `viewAny` — nunca exigiendo
ambos a la vez (R-AUT-08). `EnsureModuleIsActive` SHALL bloquear rutas cuyo módulo no
cumpla Active + autorizado + encendido para la empresa de sesión.

#### Scenario: Módulo visible por permiso de Functionality sin comodín
- **WHEN** un usuario tiene el permiso `viewAny` de una `Functionality`, el Module está
  Active, autorizado y encendido para la empresa de la sesión, pero no tiene el comodín
  `<module-slug>.access`
- **THEN** el módulo aparece en el menú con esa `Functionality` visible

#### Scenario: Comodín de módulo implica todas sus Functionalities
- **WHEN** un usuario tiene el permiso comodín `<module-slug>.access` y el Module está
  Active, autorizado y encendido para la empresa de la sesión
- **THEN** ve todas las Functionalities activas de ese módulo en el submenú, tenga o no
  permisos individuales sobre cada una

#### Scenario: Módulo visible solo si elegible y encendido
- **WHEN** se construye el menú para la empresa de la sesión
- **THEN** un Module solo aparece si está Active globalmente, la empresa está autorizada,
  lo tiene encendido, y el usuario cumple la regla de permisos (comodín OR permiso de
  Functionality)

#### Scenario: Menú respeta módulos encendidos
- **WHEN** un usuario tiene permisos sobre una Functionality cuyo Module no está
  encendido para la empresa de la sesión
- **THEN** esa Functionality no aparece en el menú y el acceso por ruta queda bloqueado
