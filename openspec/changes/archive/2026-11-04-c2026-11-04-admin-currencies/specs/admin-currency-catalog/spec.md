# Spec Delta

## Purpose

Catálogo global de monedas Admin (`Currency`): CRUD bajo Configuración / Monedas
al estilo v1 adaptado a v2, y seeder desde datos v1 con IDs preservados, sin
tenancy por company.

## ADDED Requirements

### Requirement: Catálogo global Currency
El sistema SHALL persistir monedas como catálogo global sin `company_id`, con soft
delete y status Active/Inactive (columna string + enum PHP). Currency SHALL
incluir name, slug, code nullable, number nullable y symbol nullable (max 8).

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

### Requirement: CRUD Admin Configuración / Monedas
El sistema SHALL exponer bajo Configuración → Monedas un Index ServerTable de
monedas (name, code, number, symbol) con Acciones (toggle status, Edit, Delete
confirm) y Create/Edit por modal. Index SHALL NOT mostrar columna Estado textual
si existe toggle en Acciones. Autorización vía Functionality `currencies` y
permisos `currencies.*`.

#### Scenario: Usuario autorizado abre Monedas
- **WHEN** un usuario con `currencies.viewAny` (o Super Admin) abre Configuración
  → Monedas
- **THEN** ve el Index ServerTable de currencies

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `currencies.viewAny` y sin Super Admin fuerza la URL del
  Index de monedas
- **THEN** recibe 403

#### Scenario: Toggle status
- **WHEN** el usuario autorizado confirma el toggle de status en Acciones
- **THEN** el registro pasa entre Active e Inactive y el listado refleja el cambio

### Requirement: Code y number inmutables en Edit
El sistema SHALL permitir en Edit cambiar name (y regenerar slug) y symbol.
SHALL NOT permitir cambiar code ni number de una moneda existente.

#### Scenario: Edit no muta code/number
- **WHEN** el usuario edita una moneda existente enviando solo name y/o symbol
- **THEN** se persisten name/slug/symbol y code/number permanecen iguales

#### Scenario: Intento de cambiar code en update
- **WHEN** una petición de update incluye un code distinto al persistido
- **THEN** el code almacenado no cambia (campo ignorado o rechazado sin mutar)

### Requirement: Seeder monedas v1 con habituales Active
El sistema SHALL incluir un seeder que cargue monedas desde datos v1, preserve
los IDs de origen, marque como Active las habituales EUR, USD, GBP y CHF, e
Inactive el resto. El seeder SHALL quedar documentado en README § Seeders y
SHALL NOT ejecutarse automáticamente al abrir el Index.

#### Scenario: Seed en entorno limpio
- **WHEN** se ejecuta el seeder de monedas en base limpia
- **THEN** existen los registros portados con los mismos IDs de origen y EUR,
  USD, GBP y CHF están Active

#### Scenario: Index no importa
- **WHEN** un usuario abre el Index de monedas sin haber sembrado
- **THEN** el Index no crea filas automáticamente (sin import lateral en GET)
