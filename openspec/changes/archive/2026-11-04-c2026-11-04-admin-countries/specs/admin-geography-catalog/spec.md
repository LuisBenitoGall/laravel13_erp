# Spec Delta

## Purpose

Catálogo global de geografía Admin (Country → Province → Town): CRUD bajo
Configuración / Países al estilo v1, cascada JSON y seeder desde datos v1, sin
tenancy por company.

## ADDED Requirements

### Requirement: Catálogo global Country / Province / Town
El sistema SHALL persistir países, provincias y poblaciones (`Town`) como catálogo
global sin `company_id`, con soft delete y status Active/Inactive (columna string +
enum PHP). Province SHALL pertenecer a Country; Town SHALL pertenecer a Province.
Country SHALL incluir name, slug, code, alfa2, alfa3 y flag nullable.

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

#### Scenario: Jerarquía
- **WHEN** se listan provincias de un país o poblaciones de una provincia
- **THEN** solo aparecen las hijas de ese padre (excluyendo soft-deleted por defecto)

### Requirement: CRUD Admin Configuración / Países (flujo v1)
El sistema SHALL exponer bajo Configuración → Países un Index ServerTable de países
con Acciones (toggle status, ir a provincias, Edit, Delete confirm) y alta/edición
adaptada a Inertia/React. Desde un país SHALL abrirse el Index de provincias; desde
una provincia, el Index de poblaciones (`Town`). Autorización vía Functionality
`countries` y permisos `countries.*` también para provincias y poblaciones.

#### Scenario: Usuario autorizado abre Países
- **WHEN** un usuario con `countries.viewAny` (o Super Admin) abre Configuración →
  Países
- **THEN** ve el Index ServerTable de países

#### Scenario: Drill a provincias y poblaciones
- **WHEN** el usuario elige provincias en Acciones de un país y luego poblaciones en
  Acciones de una provincia
- **THEN** navega a los Indexes anidados correspondientes

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `countries.viewAny` y sin Super Admin fuerza la URL del
  Index de países
- **THEN** recibe 403

#### Scenario: Toggle status
- **WHEN** el usuario autorizado confirma el toggle de status en Acciones
- **THEN** el registro pasa entre Active e Inactive y el listado refleja el cambio

### Requirement: Endpoints de cascada geográficos
El sistema SHALL exponer endpoints JSON autenticados para listar provincias de un
país y poblaciones de una provincia. La cascada SHALL devolver solo registros con
status Active y no soft-deleted (alineado a v1 filtrando activos).

#### Scenario: Provincias por país
- **WHEN** un usuario autenticado solicita la cascada de un Country existente
- **THEN** se devuelven sus provincias Active

#### Scenario: Poblaciones por provincia
- **WHEN** un usuario autenticado solicita la cascada de una Province existente
- **THEN** se devuelven sus towns Active

### Requirement: Seeder con datos v1
El sistema SHALL incluir un seeder que cargue países, provincias y poblaciones desde
los datos de v1, mapeando status 1→Active y 0→Inactive, preservando los IDs de v1.

#### Scenario: Seed en entorno limpio
- **WHEN** se ejecuta el seeder geográfico en base limpia
- **THEN** existen los registros portados desde v1 con los mismos IDs de origen
