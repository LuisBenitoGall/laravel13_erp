# Spec Delta

## Purpose

Catálogo global de grupos contables Admin (`AccountingAccountType`, PGC España):
CRUD bajo Configuración, vista show de referencia/asistente, seeder jerárquico
con i18n fiscal, sin tenancy ni status Active/Inactive.

## ADDED Requirements

### Requirement: Catálogo global AccountingAccountType (PGC)
El sistema SHALL persistir grupos contables como catálogo global sin
`company_id`, con soft delete y campos autoreference, code, name y mode nullable.
SHALL NOT usar status Active/Inactive.

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

### Requirement: CRUD Configuración / Grupos contables
El sistema SHALL exponer bajo Configuración → Grupos contables un Index
ServerTable con code, name y level, Acciones Edit modal y Delete confirm, y
Create por modal. En Edit, code SHALL ser inmutable. Autorización vía
Functionality `accounting-account-types` y permisos
`accounting-account-types.*`.

#### Scenario: Usuario autorizado abre Index
- **WHEN** un usuario con `accounting-account-types.viewAny` (o Super Admin)
  abre Configuración → Grupos contables
- **THEN** ve el Index ServerTable

#### Scenario: Edit no cambia code
- **WHEN** el usuario edita un grupo existente
- **THEN** puede cambiar name pero no code

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin viewAny y sin Super Admin fuerza el Index
- **THEN** recibe 403

### Requirement: Vista show asistente PGC
El sistema SHALL exponer una vista show de referencia/asistente con la jerarquía
de grupos contables (equivalente funcional a v1 show), autorizada con el mismo
permiso de catálogo.

#### Scenario: SA abre show
- **WHEN** un usuario autorizado abre la vista show de grupos contables
- **THEN** ve la jerarquía de referencia del PGC

### Requirement: Seeder PGC con i18n fiscal
El sistema SHALL incluir un seeder portado del v1 que construye la jerarquía PGC
España y resuelve nombres vía claves i18n `fiscal.*` (como el seeder v1),
documentado en README § Seeders.

#### Scenario: Seed en entorno limpio
- **WHEN** se ejecuta el seeder de accounting account types
- **THEN** existen los grupos jerárquicos del PGC con codes y names coherentes
