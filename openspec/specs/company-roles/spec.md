## Purpose

Roles Spatie `manager` y `guest` por empresa al crear Company; vínculo del creador como manager; asignación mínima manager|guest a usuarios ya vinculados.

## Requirements

### Requirement: Roles manager y guest por empresa
El sistema SHALL, al crear cada Company, crear los roles Spatie `manager` y `guest`
asociados a esa empresa (`company_id`), vincular al usuario creador en `user_companies`,
asignarle el rol `manager`, y dejar `guest` sin permisos. SHALL permitir asignar
`manager` o `guest` a un usuario ya vinculado a la company sin crear roles adicionales.

#### Scenario: Alta de company genera roles obligatorios
- **WHEN** un usuario crea una Company nueva
- **THEN** existen los roles `manager` y `guest` con `company_id` de esa company, el
  creador está en `user_companies` y tiene el rol `manager`

#### Scenario: Guest sin permisos
- **WHEN** se crea el rol `guest` de una company
- **THEN** no tiene ninguna entrada en `role_has_permissions`

#### Scenario: Asignar guest a usuario vinculado
- **WHEN** un actor autorizado asigna el rol `guest` a un usuario ya vinculado a la
  company
- **THEN** ese usuario tiene el rol `guest` de esa company (con `company_id` correcto) y
  no se crea ningún rol nuevo
