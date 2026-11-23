# Spec Delta — hr-employees

## ADDED Requirements

### Requirement: Proyección de periodos en ficha User (lectura multi-company)
El sistema SHALL permitir listar, en la ficha Edit de User, todos los periodos
`Employee` del sujeto con `withoutGlobalScope('company')` (filtro `user_id`),
en solo lectura, cuando el change/spec de user-edit lo declare. SHALL NOT
mutar periodos desde esa proyección. SHALL NOT exigir permiso `employees.view`
para esa lectura (authz = acceso a la ficha). Esta proyección SHALL alinearse
con la excepción de lecturas ancladas a User documentada en kernel /
architecture (R-TEN-10).

#### Scenario: Listado cross-company por user_id
- **WHEN** un actor autorizado a la ficha solicita la tabla de periodos del User
- **THEN** recibe filas de todas las companies del sujeto (no solo CurrentCompany)

#### Scenario: Sin mutación desde la proyección
- **WHEN** el actor usa el tab Empleos de la ficha User
- **THEN** no puede crear, renovar, editar ni cancelar periodos desde ahí
