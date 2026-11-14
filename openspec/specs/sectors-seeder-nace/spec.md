## Purpose

Poblar el catálogo global de sectores con las 19 secciones NACE Rev. 2
A–S (sin T ni U), traducidas ES/EN/CA, mediante `SectorsSeeder` idempotente.

## Requirements

### Requirement: Seeder NACE secciones A–S
El `SectorsSeeder` SHALL poblar el catálogo `sectors` con las 19
secciones NACE Rev. 2 A–S (sin T ni U), cada una con slug y nombre en
`es`, `en` y `ca`. SHALL usar upsert por slug (incl. restore si
soft-deleted). SHALL NOT modificar el CRUD de Sectores.

#### Scenario: Seed completo
- **WHEN** se ejecuta `SectorsSeeder`
- **THEN** existen las 19 secciones A–S con traducciones es/en/ca y
  status Active

#### Scenario: Re-ejecución idempotente
- **WHEN** se vuelve a ejecutar el seeder
- **THEN** actualiza por slug sin duplicar filas

#### Scenario: Sin T ni U
- **WHEN** se revisa el seeder
- **THEN** no incluye hogares-empleadores ni organismos
  extraterritoriales
