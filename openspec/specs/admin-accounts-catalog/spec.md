## Purpose

Catálogo global Admin de cuentas SaaS (`Account`): plantillas de duración
y coste bajo Configuración → Cuentas, con una default y máscara de
renovación, sin vínculo `company_accounts` aún.

## Requirements

### Requirement: Catálogo global Account
El sistema SHALL persistir Accounts como catálogo global sin
`company_id`, con soft delete y status Active/Inactive (columna string +
enum PHP). Account SHALL incluir name, slug, description nullable, rate
(decimal), duration en días (nullable), e `is_default`. SHALL NOT usar
BelongsToCompany.

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

### Requirement: CRUD Admin Configuración / Cuentas
El sistema SHALL exponer bajo Configuración → Cuentas un Index
ServerTable con Acciones (toggle status, Edit, Delete confirm) y
Create/Edit por modal. Autorización vía Functionality `accounts` y
permisos `accounts.*`. El acceso al Index SHALL exigir `accounts.view`.

#### Scenario: SA abre Cuentas
- **WHEN** un Super Admin con permiso de Cuentas entra a la ruta del
  catálogo
- **THEN** ve el listado ServerTable de Accounts

#### Scenario: Crear Account
- **WHEN** el actor autorizado crea una cuenta con name, rate y duration
- **THEN** queda persistida en el catálogo con slug y status

#### Scenario: Sin permiso view
- **WHEN** un usuario sin `accounts.view` y sin Super Admin fuerza la
  URL del Index
- **THEN** recibe 403

### Requirement: Cuenta default del catálogo
El sistema SHALL permitir marcar exactamente una Account del catálogo
como default. SHALL NOT permitir eliminar la Account que es default
mientras no se marque otra.

#### Scenario: Marcar default
- **WHEN** el actor marca una Account como default
- **THEN** esa queda como la única default del catálogo

#### Scenario: Delete de la default bloqueado
- **WHEN** el actor intenta eliminar la Account marcada como default
- **THEN** el sistema rechaza la eliminación

### Requirement: Redirect renovación Admin a Mi cuenta
El sistema SHALL redirigir `GET /admin/accounts/renewal` a Mi cuenta
(`/account`), donde vive el histórico y la renovación real de
`company_accounts`.

#### Scenario: Abrir renovación Admin
- **WHEN** el actor autorizado abre la ruta Admin de renovación de cuenta
- **THEN** es redirigido a `/account` (Mi cuenta)
