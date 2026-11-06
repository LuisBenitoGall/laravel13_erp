## MODIFIED Requirements

### Requirement: CRUD Configuración / Tipos de IVA
El sistema SHALL exponer bajo Configuración → Tipos de IVA un Index ServerTable
plano con columna País (y filtro), Acciones (toggle, Edit, Delete confirm) y
Create/Edit por modal. Index SHALL NOT mostrar columna Estado. Autorización vía
Functionality `vat-types` y permisos `vat-types.*`. El acceso al Index SHALL
exigir `vat-types.view`.

#### Scenario: Usuario autorizado abre Tipos de IVA
- **WHEN** un usuario con `vat-types.view` (o Super Admin) abre Configuración
  → Tipos de IVA
- **THEN** ve el Index con país visible

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `vat-types.view` y sin Super Admin fuerza la URL
- **THEN** recibe 403

#### Scenario: Toggle status
- **WHEN** el usuario autorizado confirma el toggle de status
- **THEN** el registro pasa entre Active e Inactive
