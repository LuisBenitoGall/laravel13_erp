## MODIFIED Requirements

### Requirement: CRUD Admin Configuración / Unidades
El sistema SHALL exponer bajo Configuración → Unidades un Index ServerTable con
Acciones (toggle status, Edit, Delete confirm) y Create/Edit por modal. Index
SHALL NOT mostrar columna Estado textual si existe toggle en Acciones.
Autorización vía Functionality `units` y permisos `units.*`. El acceso al Index
SHALL exigir `units.view`.

#### Scenario: Usuario autorizado abre Unidades
- **WHEN** un usuario con `units.view` (o Super Admin) abre Configuración →
  Unidades
- **THEN** ve el Index ServerTable de units

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `units.view` y sin Super Admin fuerza la URL del
  Index
- **THEN** recibe 403

#### Scenario: Toggle status
- **WHEN** el usuario autorizado confirma el toggle de status en Acciones
- **THEN** el registro pasa entre Active e Inactive y el listado refleja el cambio
