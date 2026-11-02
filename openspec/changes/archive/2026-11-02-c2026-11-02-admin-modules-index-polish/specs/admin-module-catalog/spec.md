# Spec Delta

## ADDED Requirements

### Requirement: Status de Module aplicable también a shell
El sistema SHALL permitir cambiar el `status` (Active/Inactive) de cualquier `Module`
del catálogo, incluidos los slugs de shell `admin` y `workspace`. SHALL NOT rechazar
la desactivación solo por ser módulo de shell.

#### Scenario: Desactivar módulo admin
- **WHEN** un usuario autorizado actualiza el status del módulo `admin` a Inactive
- **THEN** el cambio se persiste y el módulo queda Inactive

#### Scenario: Desactivar módulo workspace
- **WHEN** un usuario autorizado actualiza el status del módulo `workspace` a Inactive
- **THEN** el cambio se persiste y el módulo queda Inactive

#### Scenario: Reactivar módulo shell
- **WHEN** un usuario autorizado actualiza el status de un módulo shell Inactive a
  Active
- **THEN** el cambio se persiste y el módulo queda Active
