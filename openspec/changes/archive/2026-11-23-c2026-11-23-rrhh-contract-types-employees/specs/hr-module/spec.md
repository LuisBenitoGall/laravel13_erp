# Spec Delta

## Purpose

Module SaaS `rrhh` (Optional) con Functionalities de oleada 1, menú data-driven
y acceso HTTP acotado a las tres capas más permisos.

## ADDED Requirements

### Requirement: Module rrhh y Functionalities
El sistema SHALL registrar el Module slug `rrhh` (level Optional) con labels
i18n y Functionalities `contract-types` y `employees`, permisos
`<functionality>.<acción>` y comodín `rrhh.access`, conforme a las tres capas
SaaS.

#### Scenario: Menú con capas y permiso
- **WHEN** el Module está Active, la company autorizada y (si Optional)
  encendido, y el actor tiene `{functionality}.view`
- **THEN** el ítem de menú correspondiente es visible

#### Scenario: Acceso por permiso
- **WHEN** un actor sin el `.view` correspondiente (y sin Super Admin) fuerza
  el Index de tipos o de empleados
- **THEN** recibe 403

### Requirement: Rutas bajo módulo activable
Las rutas HR de oleada 1 SHALL quedar bajo el gate de módulo `rrhh` (V-21):
módulo off (capas) bloquea; sin permiso → 403; capas+permiso → OK.

#### Scenario: Módulo off
- **WHEN** las capas de `rrhh` no permiten acceso para la company de sesión
- **THEN** las rutas `/hr/*` de oleada 1 no son usables (403/denegación de
  módulo)
