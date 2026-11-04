# Spec Delta

## Purpose

Catálogo global de unidades Admin (`Unit`): CRUD bajo Configuración / Unidades
al estilo monedas (ServerTable + modales), seeder desde datos v1, sin tenancy
por company.

## ADDED Requirements

### Requirement: Catálogo global Unit
El sistema SHALL persistir unidades como catálogo global sin `company_id`, con
soft delete y status Active/Inactive (columna string + enum PHP). Unit SHALL
incluir name (único entre no soft-deleted), slug y symbol nullable (max 4).
SHALL NOT persistir `translations`.

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

### Requirement: CRUD Admin Configuración / Unidades
El sistema SHALL exponer bajo Configuración → Unidades un Index ServerTable con
Acciones (toggle status, Edit, Delete confirm) y Create/Edit por modal. Index
SHALL NOT mostrar columna Estado textual si existe toggle en Acciones.
Autorización vía Functionality `units` y permisos `units.*`.

#### Scenario: Usuario autorizado abre Unidades
- **WHEN** un usuario con `units.viewAny` (o Super Admin) abre Configuración →
  Unidades
- **THEN** ve el Index ServerTable de units

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `units.viewAny` y sin Super Admin fuerza la URL del
  Index
- **THEN** recibe 403

#### Scenario: Toggle status
- **WHEN** el usuario autorizado confirma el toggle de status en Acciones
- **THEN** el registro pasa entre Active e Inactive y el listado refleja el cambio

### Requirement: Default Active-first en Index Unidades
El endpoint `table` de units SHALL, cuando el cliente no envía sorts, ordenar
Active antes que Inactive y, como criterio secundario, por `name`. SHALL NOT
prefijar Active-first cuando el cliente envía sorts.

#### Scenario: Unidades sin sort del cliente
- **WHEN** se solicita el `table` de units sin sorts
- **THEN** aparecen primero los Active y, dentro de cada grupo de status, por name

#### Scenario: Unidades con sort del cliente
- **WHEN** el cliente envía un sort
- **THEN** se aplica ese sort sin prefijo Active-first obligatorio

### Requirement: Seeder unidades v1 con status del JSON
El sistema SHALL incluir un seeder que cargue unidades desde datos v1, preserve
los IDs de origen, respete status Active/Inactive del origen, y quede documentado
en README § Seeders. SHALL NOT ejecutarse automáticamente al abrir el Index.

#### Scenario: Seed en entorno limpio
- **WHEN** se ejecuta el seeder de unidades en base limpia
- **THEN** existen los registros portados con los mismos IDs y el status del JSON

#### Scenario: Index no importa
- **WHEN** un usuario abre el Index de unidades sin haber sembrado
- **THEN** el Index no crea filas automáticamente
