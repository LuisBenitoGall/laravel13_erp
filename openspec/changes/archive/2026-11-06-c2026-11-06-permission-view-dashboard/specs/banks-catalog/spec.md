## MODIFIED Requirements

### Requirement: CRUD Configuración / Bancos
El sistema SHALL exponer bajo Configuración → Bancos un Index ServerTable con
Acciones (toggle status, Edit, Delete confirm) y Create/Edit por modal. Index
SHALL NOT mostrar columna Estado textual si existe toggle en Acciones.
Autorización vía Functionality `banks` y permisos `banks.*`. El acceso al Index
SHALL exigir `banks.view`.

#### Scenario: Usuario autorizado abre Bancos
- **WHEN** un usuario con `banks.view` (o Super Admin) abre Configuración →
  Bancos
- **THEN** ve el Index ServerTable de banks

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `banks.view` y sin Super Admin fuerza la URL del
  Index
- **THEN** recibe 403

#### Scenario: Toggle status
- **WHEN** el usuario autorizado confirma el toggle de status en Acciones
- **THEN** el registro pasa entre Active e Inactive y el listado refleja el cambio
