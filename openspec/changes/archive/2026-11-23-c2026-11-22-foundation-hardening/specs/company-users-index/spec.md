# Spec Delta

## ADDED Requirements

### Requirement: Desactivar user es global y bloquea acceso
Desactivar un user desde Usuarios empresa (acción de fila o masiva) SHALL
persistir `users.status` inactivo a nivel global (no status por membership).
Ese user SHALL quedar imposibilitado de login al ERP. La UI SHALL indicar
de forma clara (copy/confirm) que la desactivación es global.

#### Scenario: Desactivar desde company-users
- **WHEN** un actor autorizado desactiva un user en Usuarios empresa
- **THEN** el status global del user queda inactivo y ese user no puede
  iniciar sesión

#### Scenario: Copy UI global
- **WHEN** el actor inicia desactivar (fila o masivo) en Usuarios empresa
- **THEN** ve copy i18n que aclara que la acción afecta al acceso global
  al ERP

## MODIFIED Requirements

### Requirement: Acciones masivas Usuarios empresa
El Index Usuarios empresa SHALL declarar en ServerTable acciones masivas sobre
la página actual: eliminar (detach `user_companies` de sesión + confirm),
activar y desactivar status **global** del User. Cada bulk SHALL ir por Action
Admin + Policy equivalente a la fila. SHALL NOT borrar el User global al
eliminar masivo. Desactivar masivo SHALL impedir el login de esos users.

#### Scenario: Eliminar masivo con confirm
- **WHEN** el actor autorizado selecciona uno o más users de la página y
  confirma la acción masiva eliminar
- **THEN** se eliminan los vínculos `user_companies` con la company de sesión
  para esos users y los User globales no se borran por esa acción

#### Scenario: Activar masivo
- **WHEN** el actor autorizado selecciona uno o más users de la página y elige
  activar
- **THEN** el status global de esos users queda activo

#### Scenario: Desactivar masivo
- **WHEN** el actor autorizado selecciona uno o más users de la página y elige
  desactivar
- **THEN** el status global de esos users queda inactivo y no pueden iniciar
  sesión
