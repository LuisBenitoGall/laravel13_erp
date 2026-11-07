## MODIFIED Requirements

### Requirement: CRUD Configuración / Grupos contables
El sistema SHALL exponer bajo Configuración → Grupos contables un Index
ServerTable con code, name y level, Acciones Edit modal y Delete confirm, y
Create por modal. En Edit, code SHALL ser inmutable. Autorización vía
Functionality `accounting-account-types` y permisos
`accounting-account-types.*`. El acceso al Index/show de catálogo SHALL exigir
`accounting-account-types.view`.

#### Scenario: Usuario autorizado abre Index
- **WHEN** un usuario con `accounting-account-types.view` (o Super Admin)
  abre Configuración → Grupos contables
- **THEN** ve el Index ServerTable

#### Scenario: Edit no cambia code
- **WHEN** el usuario edita un grupo existente
- **THEN** puede cambiar name pero no code

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `accounting-account-types.view` y sin Super Admin
  fuerza el Index
- **THEN** recibe 403
