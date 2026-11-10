# Spec Delta

## Purpose

Ficha Edit unificada de User (perfil, contraseña self-only, tabs extensibles),
servida desde Mi cuenta y desde Usuarios empresa, con resumen RO reutilizable.

## ADDED Requirements

### Requirement: Vista Edit de usuario unificada con tabs
El sistema SHALL exponer una vista Edit de usuario compartida por `/profile` y
por el Edit de Usuarios empresa, con ActionBar, RecordSummary (alta, última
actualización, y rol de la company de sesión en solo lectura cuando el
contexto sea company-users) y tabs extensibles. Tabs: Perfil (usable),
Contraseña (solo self), Emails y teléfonos (vacío), Empresas (vacío). Submit
SHALL ser por tab.

#### Scenario: Misma página en dos rutas
- **WHEN** el actor abre `/profile` o el Edit de un user desde Usuarios empresa
- **THEN** se renderiza la misma vista de ficha con cabecera y tabs

#### Scenario: Tabs vacíos presentes
- **WHEN** se abre la ficha
- **THEN** existen las pestañas Emails y teléfonos y Empresas sin CRUD aún

### Requirement: Tab Perfil con resumen RO y formulario
El tab Perfil SHALL mostrar dos columnas: izquierda un componente reutilizable
de resumen solo lectura (avatar placeholder, datos personales, emails y
teléfonos); derecha el formulario con name, surname, email principal,
`is_public`, fecha de nacimiento, nif y `status` (sin `is_admin`). Un submit
del tab SHALL persistir esos campos según authz.

#### Scenario: Layout dos columnas
- **WHEN** el actor abre el tab Perfil
- **THEN** ve resumen RO a la izquierda (con avatar placeholder) y el
  formulario a la derecha

#### Scenario: Guardar perfil
- **WHEN** envía el formulario del tab Perfil con datos válidos y autorización
- **THEN** se persisten los campos del tab (incluidos `is_public` y `status`)

### Requirement: Tab Contraseña solo para el propio usuario
El sistema SHALL permitir la gestión de contraseña únicamente cuando el user
editado es el actor en sesión. SHALL NOT permitir que ningún otro actor
(incluido Super Admin) cambie la contraseña de un tercero por esta ficha.
Campos: contraseña actual, nueva y confirmación.

#### Scenario: Self ve Contraseña usable
- **WHEN** el actor edita su propio user
- **THEN** el tab Contraseña es usable

#### Scenario: Ajeno sin Contraseña usable
- **WHEN** el actor edita a otro user
- **THEN** no puede cambiar la contraseña de ese user vía esta ficha

#### Scenario: Self cambia contraseña
- **WHEN** el propio usuario envía current_password, password y
  password_confirmation válidos
- **THEN** la contraseña queda actualizada

### Requirement: Authz is_public y status con gancho de permiso
El propio usuario SHALL poder cambiar `is_public` y `status` en su ficha. Un
rol con permiso (slug TBD) también SHALL poder. La implementación SHALL dejar
el punto de chequeo listo para el permiso futuro sin fijar el slug final como
constitución.

#### Scenario: Self cambia is_public o status
- **WHEN** el propio usuario actualiza `is_public` o `status` en el tab Perfil
- **THEN** el cambio se persiste

#### Scenario: Gancho de permiso
- **WHEN** exista el permiso futuro y un actor no-self lo tiene
- **THEN** el mismo camino de autorización puede conceder el cambio sin
  rediseñar la ficha

### Requirement: Authz ficha ajena company-users
Abrir y guardar el Perfil de un user ajeno desde Usuarios empresa SHALL exigir
`company-users.update` y que el member pertenezca a la company de sesión.

#### Scenario: Update ajeno sin permiso
- **WHEN** un actor sin `company-users.update` (y sin bypass aplicable de
  update) fuerza update de perfil ajeno
- **THEN** recibe 403

#### Scenario: Update ajeno fuera de perímetro
- **WHEN** el user no está vinculado a la company de sesión
- **THEN** la operación se rechaza
