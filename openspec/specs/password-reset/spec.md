## Purpose

Recuperación de contraseña por email (forgot/reset) con páginas Inertia y broker Laravel.

## Requirements

### Requirement: Recuperación de contraseña
El sistema SHALL permitir solicitar y completar un reset de contraseña por email desde el
flujo de autenticación.

#### Scenario: Solicitud de reset
- **WHEN** un usuario indica su email en “olvidé mi contraseña”
- **THEN** el sistema envía (o encola) el enlace de reset según el mecanismo Laravel de
  password reset

#### Scenario: Completar reset
- **WHEN** el usuario sigue el enlace válido y define una nueva contraseña
- **THEN** puede iniciar sesión con la nueva contraseña
