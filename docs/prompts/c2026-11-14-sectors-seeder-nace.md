<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-14-sectors-seeder-nace
Rule: 10-architecture
Summary: Enriquecer `SectorsSeeder` con las secciones NACE Rev. 2 / CNAE
  (A–S, sin T ni U), nombres ES/EN/CA, y ejecutarlo para poblar `sectors`.
  Sin tocar el CRUD de Sectores.
Goals:
- Sustituir/ampliar la lista del seeder (hoy 8 genéricos) por las **19
  secciones** NACE A–S (omitir T hogares y U extraterritoriales).
- Cada sector: slug estable + `name` traducible `es` / `en` / `ca`
  (locales ya soportados).
- Mantener upsert por slug (restore si soft-deleted; actualizar name;
  status Active) como el seeder actual.
- Ejecutar `php artisan db:seed --class=SectorsSeeder` tras el cambio.
NonGoals:
- Tocar CRUD UI/API/Policy de Sectores.
- Atribución Company↔Sector (ya existe).
- Divisiones/grupos/clases NACE (88+); solo secciones.
- Secciones T y U.
- Brand u otros catálogos.
Skills:
- openspec-propose
- testing-best-practices
Notes:
- Origen: `/opsx-explore`; Luis: omitir T/U; solo seeder, no CRUD.
- Fuente: NACE Rev. 2 / CNAE-2009 secciones (INE/Idescat).
- Inventario acordado (slug → ES / EN / CA):
  - agricultura-ganaderia-silvicultura-pesca
  - industrias-extractivas
  - industria-manufacturera
  - energia-gas-vapor-aire
  - agua-saneamiento-residuos
  - construccion (reusar slug actual)
  - comercio-reparacion-vehiculos
  - transporte-almacenamiento
  - hosteleria
  - informacion-comunicaciones
  - finanzas-seguros
  - inmobiliarias
  - profesionales-cientificas-tecnicas
  - administrativas-servicios-auxiliares
  - administracion-publica-defensa
  - educacion
  - sanidad-servicios-sociales
  - artisticas-recreativas-entretenimiento
  - otros-servicios
- Slugs viejos del seeder corto (`comercio`, `tecnologia`, `servicios`,
  etc.) que no mapeen 1:1 a NACE: no borrar filas ajenas en BD en este
  change salvo decisión de 10; el seeder añade/actualiza por slug de la
  lista NACE. (Si 10 quiere marcar obsoletos Inactive: anotar en design.)
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

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


## MODIFIED Requirements

<!-- N/A: no se cambia el contrato del CRUD; solo datos seed. -->
