## MODIFIED Requirements

### Requirement: Vista Edit de usuario unificada con tabs
El sistema SHALL exponer una vista Edit de usuario compartida por `/profile` y
por el Edit de Usuarios empresa, con ActionBar, RecordSummary (alta, última
actualización, y rol de la company de sesión en solo lectura cuando el
contexto sea company-users) y tabs extensibles. Tabs: Perfil (usable),
Contraseña (solo self), Emails y teléfonos (columnas emails y teléfonos
operables), Empresas (vacío). Submit SHALL ser por tab donde aplique formulario.

#### Scenario: Misma página en dos rutas
- **WHEN** el actor abre `/profile` o el Edit de un user desde Usuarios empresa
- **THEN** se renderiza la misma vista de ficha con cabecera y tabs

#### Scenario: Tabs vacíos presentes
- **WHEN** se abre la ficha
- **THEN** el tab Emails y teléfonos existe con emails y teléfonos usables;
  Empresas sigue sin CRUD
