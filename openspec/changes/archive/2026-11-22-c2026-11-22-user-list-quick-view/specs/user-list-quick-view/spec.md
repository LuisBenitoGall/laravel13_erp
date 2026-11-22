# Spec Delta

## Purpose

Vista rápida de usuario desde listados Admin: panel derecho con
`UserProfileReadout`, disparador en el nombre, cambio de fila sin cerrar y
cierre solo con X.

## ADDED Requirements

### Requirement: Panel de vista rápida con UserProfileReadout
El sistema SHALL proveer un panel lateral derecho que muestre
`UserProfileReadout` del user seleccionado desde un listado. Cambiar la
selección a otro user SHALL actualizar el contenido del panel sin cerrarlo.
El panel SHALL cerrarse solo mediante el botón X (SHALL NOT cerrarse por clic
fuera).

#### Scenario: Abrir ficha
- **WHEN** el actor dispara ver detalle de un user
- **THEN** se abre el panel derecho con la ficha rápida de ese user

#### Scenario: Abrir ficha no recarga el listado
- **WHEN** el actor abre o cambia la vista rápida desde el Index
- **THEN** el ServerTable no se destruye ni vuelve a pedir `/table` solo por
  ese cambio de estado del panel

#### Scenario: Cambiar user sin cerrar
- **WHEN** el panel está abierto y el actor dispara ver detalle de otro user
- **THEN** el panel muestra la ficha del nuevo user sin haberse cerrado

#### Scenario: Cierre solo con X
- **WHEN** el panel está abierto y el actor pulsa X
- **THEN** el panel se cierra
- **WHEN** el panel está abierto y el actor hace clic fuera del panel
- **THEN** el panel permanece abierto

### Requirement: Vista rápida Usuarios empresa con Editar condicional
Desde el Index Usuarios empresa, el panel SHALL cargar el user vinculado a la
company de sesión. SHALL mostrar botón Editar al Edit de company-users solo
si el actor tiene permiso de update de usuarios de empresa; si no, SHALL NOT
mostrar Editar.

#### Scenario: Editar con permiso
- **WHEN** el actor con permiso de update abre la vista rápida en Usuarios
  empresa
- **THEN** el panel ofrece Editar hacia el Edit de ese user

#### Scenario: Sin Editar sin permiso
- **WHEN** el actor sin permiso de update abre la vista rápida en Usuarios
  empresa
- **THEN** el panel no ofrece Editar

### Requirement: Vista rápida Todos los usuarios solo lectura
Desde el Index Todos los usuarios, el panel SHALL mostrar la ficha rápida de
un user `is_public` y SHALL NOT ofrecer botón Editar.

#### Scenario: All-users sin Editar
- **WHEN** el actor abre la vista rápida desde Todos los usuarios
- **THEN** ve la ficha rápida y no ve botón Editar
