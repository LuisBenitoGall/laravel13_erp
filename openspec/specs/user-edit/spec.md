## Purpose

Ficha Edit unificada de User (perfil, contraseña self-only, tabs extensibles),
servida desde `/admin/profile` (self, avatar) y desde Usuarios empresa, con
resumen RO reutilizable. Mi cuenta del menú es cuenta SaaS company
(placeholder), no esta ficha.

## Requirements

### Requirement: Vista Edit de usuario unificada con tabs
El sistema SHALL exponer una vista Edit de usuario compartida por
`/admin/profile` (self) y por el Edit de Usuarios empresa, con ActionBar,
RecordSummary (alta, última actualización, y rol de la company de sesión en
solo lectura cuando el contexto sea company-users) y tabs extensibles. Tabs:
Perfil (usable), Contraseña (solo self), Emails y teléfonos (columnas emails
y teléfonos operables), Imágenes (usable), Documentos (usable), Empresas
(usable: ServerTable de vínculos), Empleos (usable solo si el User tiene ≥1
periodo Employee: ServerTable de periodos multi-company en solo lectura).
Submit SHALL ser por tab donde aplique formulario.

#### Scenario: Misma página en dos rutas
- **WHEN** el actor abre `/admin/profile` o el Edit de un user desde
  Usuarios empresa
- **THEN** se renderiza la misma vista de ficha con cabecera y tabs

#### Scenario: Tabs vacíos presentes
- **WHEN** se abre la ficha
- **THEN** Emails y teléfonos, Imágenes, Documentos y Empresas son usables

#### Scenario: Misma ficha self y company-users con Empleos
- **WHEN** el actor abre `/admin/profile` o el Edit de company-users de un User
  con periodos
- **THEN** ambas rutas pueden mostrar el tab Empleos con el mismo criterio de
  datos multi-company en solo lectura

### Requirement: Tab Empleos en ficha User (solo lectura, multi-company)
La ficha Edit de User (self y company-users) SHALL ofrecer un tab Empleos de
solo lectura con todos los periodos `Employee` del sujeto (todas las companies,
sin CurrentCompany) cuando exista ≥1 periodo no soft-deleted. Acceso SHALL
seguir la ficha (no `employees.view`). SHALL NOT mutar periodos ni mostrar
columna de estado.

#### Scenario: Tab visible con periodos
- **WHEN** el actor abre la ficha Edit de un User (self o ajena) que tiene ≥1
  periodo Employee en cualquier company
- **THEN** aparece el tab Empleos con esos periodos (todas las companies)

#### Scenario: Columnas del listado
- **WHEN** se muestra el tab Empleos
- **THEN** la tabla incluye empresa, centro de trabajo, tipo de contrato,
  puesto, fecha inicio, fecha fin, horas semanales y salario

#### Scenario: Tab oculto sin periodos
- **WHEN** el User no tiene ningún periodo Employee
- **THEN** el tab Empleos no se muestra

#### Scenario: Solo lectura observaciones
- **WHEN** un periodo tiene observaciones
- **THEN** en acciones se puede abrir un modal de solo lectura con ese texto;
  sin acciones de editar/cancelar contrato

#### Scenario: Sin observaciones
- **WHEN** un periodo no tiene observaciones
- **THEN** no hay acción de editar/cancelar; no se ofrece modal de
  observaciones con contenido

#### Scenario: Acceso sin employees.view
- **WHEN** el actor puede abrir la ficha pero no tiene `employees.view`
- **THEN** puede ver el tab Empleos y su listado (si hay periodos)

### Requirement: Tab Perfil con resumen RO y formulario
El tab Perfil SHALL mostrar dos columnas: izquierda un componente reutilizable
de resumen solo lectura (avatar = featured del user o placeholder; cabecera
nombre/email; emails adicionales sin observación + copiar; teléfonos; empresas
vinculadas si las hay; sin NIF/fecha/activo/perfil público en el resumen);
derecha el formulario con name, surname, email principal, `is_public`, fecha
de nacimiento, nif y `status` (sin `is_admin`). Un submit del tab SHALL
persistir esos campos según authz. Este change SHALL NOT alterar el formulario
de la columna derecha.

#### Scenario: Layout dos columnas
- **WHEN** el actor abre el tab Perfil
- **THEN** ve resumen RO a la izquierda (avatar featured o placeholder) y el
  formulario a la derecha

#### Scenario: Layout dos columnas sin tocar form
- **WHEN** el actor abre el tab Perfil tras este change
- **THEN** el formulario de la derecha sigue ofreciendo los mismos campos de
  perfil y la ficha rápida refleja el contenido revisado

#### Scenario: Guardar perfil
- **WHEN** envía el formulario del tab Perfil con datos válidos y autorización
- **THEN** se persisten los campos del tab (incluidos `is_public` y `status`)

### Requirement: Ficha rápida sin NIF fecha activo ni público
La ficha rápida RO de usuario SHALL NOT mostrar NIF, fecha de nacimiento,
activo ni perfil público. SHALL mantener cabecera con avatar, nombre y email
principal.

#### Scenario: Campos ocultos en readout
- **WHEN** el actor abre el tab Perfil en `/admin/profile` o en Edit de
  Usuarios empresa
- **THEN** la ficha rápida no muestra NIF, fecha de nacimiento, activo ni
  perfil público

### Requirement: Emails adicionales copiables sin observación
En la ficha rápida, los emails adicionales SHALL mostrar solo la dirección
(sin observación/descripción) y un control que copia esa dirección al
portapapeles con toast de confirmación.

#### Scenario: Email sin observación
- **WHEN** el user tiene un email adicional con descripción
- **THEN** la ficha muestra la dirección sin el texto de observación

#### Scenario: Copiar email
- **WHEN** el actor pulsa el icono de copiar de un email adicional
- **THEN** la dirección queda en el portapapeles y se muestra un toast de
  confirmación

### Requirement: Empresas vinculadas en ficha rápida
Si el user tiene companies vinculadas, la ficha rápida SHALL listar solo el
nombre de cada una. El enlace a vista de empresa SHALL quedar vacío/no
navegable en este change. Si no hay empresas, SHALL NOT mostrar la sección.

#### Scenario: Con empresas
- **WHEN** el user tiene una o más companies vinculadas
- **THEN** la ficha lista el nombre de cada una sin navegar a una vista de
  empresa

#### Scenario: Sin empresas
- **WHEN** el user no tiene companies vinculadas
- **THEN** la ficha no muestra la sección de empresas

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

### Requirement: Breadcrumb de ficha self no es Mi cuenta de menú
Cuando el contexto es profile (ficha del propio user), el breadcrumb SHALL
NOT presentarse como el ítem de menú «Mi cuenta» del Module cuenta. SHALL
usar etiquetas de perfil/edición de usuario alineadas al acceso vía avatar.

#### Scenario: Breadcrumb self
- **WHEN** el actor abre `/admin/profile`
- **THEN** el breadcrumb no usa el label de menú Mi cuenta (cuenta SaaS) como
  option de navegación de menú Cuenta
