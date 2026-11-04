## Purpose

Catálogo global de bancos (dominio Banks): CRUD bajo Configuración / Bancos al
estilo monedas (ServerTable + modales), seeder desde datos enriched v1, sin
tenancy por company.

## Requirements

### Requirement: Catálogo global Bank (dominio Banks)
El sistema SHALL persistir bancos como catálogo global sin `company_id`, en el
dominio Banks, con soft delete y status Active/Inactive (columna string + enum
PHP). Bank SHALL incluir name, slug, tradename, lei, eu_code, supervisor_code,
swift, swift_source y swift_updated_at.

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

### Requirement: CRUD Configuración / Bancos
El sistema SHALL exponer bajo Configuración → Bancos un Index ServerTable con
Acciones (toggle status, Edit, Delete confirm) y Create/Edit por modal. Index
SHALL NOT mostrar columna Estado textual si existe toggle en Acciones.
Autorización vía Functionality `banks` y permisos `banks.*`.

#### Scenario: Usuario autorizado abre Bancos
- **WHEN** un usuario con `banks.viewAny` (o Super Admin) abre Configuración →
  Bancos
- **THEN** ve el Index ServerTable de banks

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `banks.viewAny` y sin Super Admin fuerza la URL del
  Index
- **THEN** recibe 403

#### Scenario: Toggle status
- **WHEN** el usuario autorizado confirma el toggle de status en Acciones
- **THEN** el registro pasa entre Active e Inactive y el listado refleja el cambio

### Requirement: Default Active-first en Index Bancos
El endpoint `table` de banks SHALL, cuando el cliente no envía sorts, ordenar
Active antes que Inactive y, como criterio secundario, por `name`. SHALL NOT
prefijar Active-first cuando el cliente envía sorts.

#### Scenario: Bancos sin sort del cliente
- **WHEN** se solicita el `table` de banks sin sorts
- **THEN** aparecen primero los Active y, dentro de cada grupo de status, por name

#### Scenario: Bancos con sort del cliente
- **WHEN** el cliente envía un sort
- **THEN** se aplica ese sort sin prefijo Active-first obligatorio

### Requirement: Seeder bancos v1 con status del JSON
El sistema SHALL incluir un seeder que cargue bancos desde datos enriched v1,
preserve los IDs de origen, respete status Active/Inactive del origen, y quede
documentado en README § Seeders. SHALL NOT ejecutarse automáticamente al abrir el
Index.

#### Scenario: Seed en entorno limpio
- **WHEN** se ejecuta el seeder de bancos en base limpia
- **THEN** existen los registros portados con los mismos IDs y el status del JSON

#### Scenario: Index no importa
- **WHEN** un usuario abre el Index de bancos sin haber sembrado
- **THEN** el Index no crea filas automáticamente
