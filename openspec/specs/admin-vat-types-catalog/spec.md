## Purpose

Catálogo global de tipos de IVA Admin (`VatType`) por Country: CRUD bajo
Configuración, soft delete para historial comercial, seeder de tipos España
desde v1.

## Requirements

### Requirement: Catálogo global VatType por Country
El sistema SHALL persistir `VatType` como catálogo global sin `company_id`, con
soft delete y status Active/Inactive (string + enum PHP). Cada VatType SHALL
tener `country_id` obligatorio hacia Country. SHALL incluir name, rate (%) y
equivalence_surcharge nullable.

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

#### Scenario: Country obligatorio
- **WHEN** se intenta crear un VatType sin country_id
- **THEN** la validación falla

### Requirement: Selector de país completo
En Create/Edit, el sistema SHALL ofrecer selector de Country con todos los
países no soft-deleted, incluidos Inactive.

#### Scenario: País Inactive seleccionable
- **WHEN** existe un Country Inactive y el usuario abre Create de VatType
- **THEN** ese país aparece en el selector

### Requirement: CRUD Configuración / Tipos de IVA
El sistema SHALL exponer bajo Configuración → Tipos de IVA un Index ServerTable
plano con columna País (y filtro), Acciones (toggle, Edit, Delete confirm) y
Create/Edit por modal. Index SHALL NOT mostrar columna Estado. Autorización vía
Functionality `vat-types` y permisos `vat-types.*`.

#### Scenario: Usuario autorizado abre Tipos de IVA
- **WHEN** un usuario con `vat-types.viewAny` (o Super Admin) abre Configuración
  → Tipos de IVA
- **THEN** ve el Index con país visible

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `vat-types.viewAny` y sin Super Admin fuerza la URL
- **THEN** recibe 403

#### Scenario: Toggle status
- **WHEN** el usuario autorizado confirma el toggle de status
- **THEN** el registro pasa entre Active e Inactive

### Requirement: Default Active-first en Index Tipos de IVA
El endpoint `table` SHALL, cuando el cliente no envía sorts, ordenar Active
antes que Inactive y luego por name. SHALL NOT prefijar Active-first si hay
sorts del cliente.

#### Scenario: Sin sort del cliente
- **WHEN** se solicita el `table` sin sorts
- **THEN** Active primero y, dentro de cada grupo, por name

#### Scenario: Con sort del cliente
- **WHEN** el cliente envía un sort
- **THEN** se aplica ese sort sin prefijo Active-first obligatorio

### Requirement: Seeder tipos IVA España
El sistema SHALL incluir un seeder que cargue tipos desde datos v1 asignados a
Country España (id 66), preserve IDs de tipo si el origen los trae, documentado
en README § Seeders, sin ejecutarse al abrir Index.

#### Scenario: Seed con geografía
- **WHEN** existe Country España y se ejecuta el seeder
- **THEN** existen los tipos con country_id = España

#### Scenario: Index no importa
- **WHEN** un usuario abre el Index sin haber sembrado
- **THEN** el Index no crea filas automáticamente
