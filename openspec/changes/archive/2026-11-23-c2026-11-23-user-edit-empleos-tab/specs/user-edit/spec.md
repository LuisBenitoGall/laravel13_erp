# Spec Delta — user-edit

## ADDED Requirements

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

## MODIFIED Requirements

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
