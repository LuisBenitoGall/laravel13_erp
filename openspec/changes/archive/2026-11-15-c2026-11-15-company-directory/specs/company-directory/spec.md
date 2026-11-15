# Spec Delta

## Purpose

Directorio de descubrimiento de companies Active (NIF + ≥1 sector) bajo
Module `empresas`: aside de sectores con filtro OR, grid de cards ordenable
y paginado, y ficha Show solo informativa sin CTAs de contacto o alta
cliente/proveedor.

## ADDED Requirements

### Requirement: Directorio de empresas en menú Empresas
El sistema SHALL exponer bajo Module `empresas` un Directorio en
`/admin/company-directory` (Functionality `company-directory`). El acceso
de menú SHALL quedar usable con `empresas.access` (Basic), sin exigir un
permiso Spatie adicional que frene a roles básicos (`.view` sembrado a
esos roles).

#### Scenario: Entrada de menú
- **WHEN** un usuario con `empresas.access` (sesión con company) obtiene
  el menú
- **THEN** ve Directorio bajo Empresas y puede abrir
  `/admin/company-directory`

### Requirement: Filtro por sectores (OR) y grid
El Directorio SHALL mostrar un aside con checkboxes de sectores Active y
un grid de resultados a la derecha. Con N sectores marcados, SHALL
devolver companies que tengan **al menos uno** de ellos (OR). SHALL
permitir ordenar por nombre (asc/desc) y más recientes, con paginación.

#### Scenario: Filtro OR
- **WHEN** el actor marca dos sectores
- **THEN** el grid incluye companies Active con NIF que tengan cualquiera
  de esos sectores

#### Scenario: Sin filtro de sector
- **WHEN** no hay sectores marcados
- **THEN** el grid lista el universo elegible (Active + NIF + ≥1 sector)

### Requirement: Universo Active + NIF + sector
El Directorio SHALL incluir solo companies con status Active, NIF
presente y al menos un sector vinculado. SHALL NOT usar `is_public`.

#### Scenario: Excluye sin NIF o sin sector o no Active
- **WHEN** una company es draft, o sin NIF, o sin sectores
- **THEN** no aparece en el Directorio

### Requirement: Card a ficha informativa
Cada card SHALL navegar a una ficha Show informativa de la company.
SHALL NOT mostrar en esa ficha (ni en el card) botón de contactar ni de
incluir como cliente/proveedor en este change.

#### Scenario: Abrir ficha
- **WHEN** el actor hace click en una card
- **THEN** ve la ficha informativa de esa company sin CTAs de
  contacto/cliente/proveedor

### Requirement: Excepción ServerTable en Directorio
El Index del Directorio SHALL usar layout aside + grid de cards (descubrimiento),
no `<ServerTable>` / contrato `*/table`.

#### Scenario: Sin Tabulator Index
- **WHEN** el actor abre `/admin/company-directory`
- **THEN** ve el grid de cards con aside de sectores, no un Index Tabulator
