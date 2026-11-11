## MODIFIED Requirements

### Requirement: Vista Edit de usuario unificada con tabs
El sistema SHALL exponer una vista Edit de usuario compartida por `/profile` y
por el Edit de Usuarios empresa, con ActionBar, RecordSummary (alta, última
actualización, y rol de la company de sesión en solo lectura cuando el
contexto sea company-users) y tabs extensibles. Tabs: Perfil (usable),
Contraseña (solo self), Emails y teléfonos (columnas emails y teléfonos
operables), Imágenes (usable), Empresas (vacío). Submit SHALL ser por tab
donde aplique formulario.

#### Scenario: Misma página en dos rutas
- **WHEN** el actor abre `/profile` o el Edit de un user desde Usuarios empresa
- **THEN** se renderiza la misma vista de ficha con cabecera y tabs

#### Scenario: Tabs vacíos presentes
- **WHEN** se abre la ficha
- **THEN** el tab Emails y teléfonos e Imágenes son usables; Empresas sigue
  sin CRUD

### Requirement: Tab Perfil con resumen RO y formulario
El tab Perfil SHALL mostrar dos columnas: izquierda un componente reutilizable
de resumen solo lectura (avatar = featured del user o placeholder, datos
personales, emails y teléfonos); derecha el formulario con name, surname,
email principal, `is_public`, fecha de nacimiento, nif y `status` (sin
`is_admin`). Un submit del tab SHALL persistir esos campos según authz.

#### Scenario: Layout dos columnas
- **WHEN** el actor abre el tab Perfil
- **THEN** ve resumen RO a la izquierda (avatar featured o placeholder) y el
  formulario a la derecha

#### Scenario: Guardar perfil
- **WHEN** envía el formulario del tab Perfil con datos válidos y autorización
- **THEN** se persisten los campos del tab (incluidos `is_public` y `status`)
