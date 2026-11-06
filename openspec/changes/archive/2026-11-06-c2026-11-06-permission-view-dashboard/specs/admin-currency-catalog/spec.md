## MODIFIED Requirements

### Requirement: CRUD Admin Configuración / Monedas
El sistema SHALL exponer bajo Configuración → Monedas un Index ServerTable de
monedas (name, code, number, symbol) con Acciones (toggle status, Edit, Delete
confirm) y Create/Edit por modal. Index SHALL NOT mostrar columna Estado textual
si existe toggle en Acciones. Autorización vía Functionality `currencies` y
permisos `currencies.*`. El acceso al Index SHALL exigir `currencies.view`.

#### Scenario: Usuario autorizado abre Monedas
- **WHEN** un usuario con `currencies.view` (o Super Admin) abre Configuración
  → Monedas
- **THEN** ve el Index ServerTable de currencies

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `currencies.view` y sin Super Admin fuerza la URL del
  Index de monedas
- **THEN** recibe 403

#### Scenario: Toggle status
- **WHEN** el usuario autorizado confirma el toggle de status en Acciones
- **THEN** el registro pasa entre Active e Inactive y el listado refleja el cambio
