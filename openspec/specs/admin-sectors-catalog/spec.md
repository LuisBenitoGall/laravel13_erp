## Purpose

Catálogo global de sectores (`Sector`) bajo Configuración: CRUD con
traducciones, sin company_id, y vínculo N:N para que cada Company elija
sus sectores.

## Requirements

### Requirement: Catálogo global Sector
El sistema SHALL persistir sectores como catálogo global sin `company_id`,
con soft delete, status Active/Inactive (columna string + enum PHP) y
nombre traducible por locale (Spatie / JSON). SHALL generar slug único en
servidor.

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

#### Scenario: Nombre traducido
- **WHEN** se crea o edita un sector con nombres por locale
- **THEN** persisten las traducciones y el listado muestra el name del
  locale activo

### Requirement: CRUD Configuración / Sectores
El sistema SHALL exponer bajo Configuración → Sectores un Index
ServerTable con Acciones (toggle status, Edit, Delete confirm) y
Create/Edit por modal con campos traducibles. Index SHALL NOT mostrar
columna Estado textual si existe toggle en Acciones. Autorización vía
Functionality `sectors` y permisos `sectors.*`. El acceso al Index SHALL
exigir `sectors.view`.

#### Scenario: Usuario autorizado abre Sectores
- **WHEN** un usuario con `sectors.view` (o Super Admin) abre
  Configuración → Sectores
- **THEN** ve el Index ServerTable de sectors

#### Scenario: Sin permiso view
- **WHEN** un usuario sin `sectors.view` y sin Super Admin fuerza la URL
  del Index
- **THEN** recibe 403

#### Scenario: Toggle status
- **WHEN** el usuario autorizado confirma el toggle de status en Acciones
- **THEN** el registro pasa entre Active e Inactive y el listado refleja
  el cambio

### Requirement: Default Active-first en Index Sectores
El endpoint `table` de sectors SHALL, cuando el cliente no envía sorts,
ordenar Active antes que Inactive y, como criterio secundario, por name.
SHALL NOT prefijar Active-first cuando el cliente envía sorts.

#### Scenario: Sin sort del cliente
- **WHEN** se solicita el `table` de sectors sin sorts
- **THEN** aparecen primero los Active y, dentro de cada grupo, por name

### Requirement: Company atribuye N sectores
Una Company SHALL poder vincularse a cero o más sectores del catálogo
(N:N). La atribución SHALL gestionarse desde el tab Sectores del Edit de
Company (no desde Create ni desde el tab Info). Solo sectores Active
SHALL ser seleccionables. Authz del sync: `company-sectors.update` +
perímetro de vínculo (no bastará el mero `companies.update`).

#### Scenario: Sync sectores
- **WHEN** un actor autorizado con `company-sectors.update` guarda un
  conjunto de sector ids Active en el Edit de una company vinculada
- **THEN** el pivot refleja exactamente ese conjunto

#### Scenario: Create sin sectores
- **WHEN** se crea una company
- **THEN** no se exige atribuir sectores en el Create
