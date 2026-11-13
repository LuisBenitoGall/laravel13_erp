# Spec Delta

## MODIFIED Requirements

### Requirement: Vista Edit de usuario unificada con tabs
El sistema SHALL exponer una vista Edit de usuario compartida por
`/admin/profile` (self) y por el Edit de Usuarios empresa, con ActionBar,
RecordSummary (alta, última actualización, y rol de la company de sesión en
solo lectura cuando el contexto sea company-users) y tabs extensibles. Tabs:
Perfil (usable), Contraseña (solo self), Emails y teléfonos (columnas emails
y teléfonos operables), Imágenes (usable), Documentos (usable), Empresas
(usable: ServerTable de vínculos). Submit SHALL ser por tab donde aplique
formulario.

#### Scenario: Misma página en dos rutas
- **WHEN** el actor abre `/admin/profile` o el Edit de un user desde
  Usuarios empresa
- **THEN** se renderiza la misma vista de ficha con cabecera y tabs

#### Scenario: Tabs vacíos presentes
- **WHEN** se abre la ficha
- **THEN** Emails y teléfonos, Imágenes, Documentos y Empresas son usables

### Requirement: Breadcrumb de ficha self no es Mi cuenta de menú
Cuando el contexto es profile (ficha del propio user), el breadcrumb SHALL
NOT presentarse como el ítem de menú «Mi cuenta» del Module cuenta. SHALL
usar etiquetas de perfil/edición de usuario alineadas al acceso vía avatar.

#### Scenario: Breadcrumb self
- **WHEN** el actor abre `/admin/profile`
- **THEN** el breadcrumb no usa el label de menú Mi cuenta (cuenta SaaS) como
  option de navegación de menú Cuenta
