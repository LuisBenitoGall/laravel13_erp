## MODIFIED Requirements

### Requirement: CRUD Admin Configuración / Países (flujo v1)
El sistema SHALL exponer bajo Configuración → Países un Index ServerTable de países
con Acciones (toggle status, ir a provincias, Edit, Delete confirm) y alta/edición
adaptada a Inertia/React. Desde un país SHALL abrirse el Index de provincias; desde
una provincia, el Index de poblaciones (`Town`). Autorización vía Functionality
`countries` y permisos `countries.*` también para provincias y poblaciones. El
acceso al Index/dashboard SHALL exigir `countries.view` (no `countries.viewAny`).

#### Scenario: Usuario autorizado abre Países
- **WHEN** un usuario con `countries.view` (o Super Admin) abre Configuración →
  Países
- **THEN** ve el Index ServerTable de países

#### Scenario: Drill a provincias y poblaciones
- **WHEN** el usuario elige provincias en Acciones de un país y luego poblaciones en
  Acciones de una provincia
- **THEN** navega a los Indexes anidados correspondientes

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `countries.view` y sin Super Admin fuerza la URL del
  Index de países
- **THEN** recibe 403

#### Scenario: Toggle status
- **WHEN** el usuario autorizado confirma el toggle de status en Acciones
- **THEN** el registro pasa entre Active e Inactive y el listado refleja el cambio
