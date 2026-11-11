## ADDED Requirements

### Requirement: Breadcrumb de ficha self no es Mi cuenta de menú
Cuando el contexto es profile (ficha del propio user), el breadcrumb SHALL
NOT presentarse como el ítem de menú «Mi cuenta» del Module cuenta. SHALL
usar etiquetas de perfil/edición de usuario alineadas al acceso vía avatar.

#### Scenario: Breadcrumb self
- **WHEN** el actor abre `/profile`
- **THEN** el breadcrumb no usa el label de menú Mi cuenta (cuenta SaaS) como
  option de navegación de menú Cuenta
