## MODIFIED Requirements

### Requirement: Avatar con Perfil mínimo y Logout
El sistema SHALL mostrar un avatar del usuario en sesión (imagen featured si
existe; si no, placeholder/iniciales) con desplegable que permita ir a la
ficha de perfil (`/profile`) y hacer logout. Logout SHALL cerrar la sesión
con el endpoint existente.

#### Scenario: Avatar con featured
- **WHEN** el usuario en sesión tiene imagen featured
- **THEN** el topbar muestra esa imagen como avatar

#### Scenario: Avatar sin featured
- **WHEN** el usuario en sesión no tiene imagen featured
- **THEN** el topbar muestra el placeholder/iniciales

#### Scenario: Perfil placeholder
- **WHEN** el usuario elige Perfil en el menú del avatar
- **THEN** accede a `/profile` (ficha Edit unificada)

#### Scenario: Logout
- **WHEN** el usuario elige Logout
- **THEN** se cierra la sesión y las rutas protegidas redirigen al login
