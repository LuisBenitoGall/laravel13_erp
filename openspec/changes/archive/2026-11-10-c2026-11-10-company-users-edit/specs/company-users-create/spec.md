## MODIFIED Requirements

### Requirement: Edit stub de usuario empresa
Tras Create/link (y desde el Index), el Edit de Usuarios empresa SHALL usar la
ficha unificada de user-edit (tabs, Perfil usable, Contraseña solo self, tabs
Emails/teléfonos y Empresas vacíos), no un stub de solo cabecera.

#### Scenario: Edit stub accesible
- **WHEN** un actor autorizado abre el Edit del user desde Usuarios empresa
- **THEN** ve la ficha unificada con tabs y tab Perfil usable (no solo el
  texto stub previo)
