# Spec Delta

## ADDED Requirements

### Requirement: Login exige user activo
El sistema SHALL denegar el login cuando `users.status` es inactivo.
SHALL NOT crear sesión autenticada usable para un user inactivo. Un user
activo con credenciales correctas SHALL seguir el flujo post-login vigente
(companies / onboarding).

#### Scenario: Inactivo no entra
- **WHEN** un user con status inactivo envía credenciales correctas en login
- **THEN** no accede al ERP (error de credenciales o mensaje de cuenta
  inactiva; sin sesión operativa)

#### Scenario: Activo sí entra
- **WHEN** un user activo envía credenciales correctas
- **THEN** el login sigue el flujo post-login vigente (companies /
  onboarding)
