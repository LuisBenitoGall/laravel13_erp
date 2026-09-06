## Purpose

Detección automática de artefactos faltantes por entidad vía test CI (`EntityCompletenessTest`) y comando `module:audit`.

## Requirements

### Requirement: Backstop de completitud
El sistema SHALL detectar automáticamente cuando a una entidad le falta alguno de sus
artefactos "Siempre", tanto en CI (test de completitud) como bajo demanda (comando
`module:audit`), según `tooling.md` §2.

#### Scenario: CI detecta un artefacto faltante
- **WHEN** un modelo de dominio no tiene su Policy, Factory, FormRequest, Resource o
  Controller hermano
- **THEN** el test de completitud falla señalando el fichero exacto ausente

#### Scenario: Auditoría legible bajo demanda
- **WHEN** se ejecuta `artisan module:audit <domain> <entity>`
- **THEN** se muestra una tabla en consola con cada artefacto y su estado ✓/✗

#### Scenario: Sin modelos de dominio el backstop no falla en falso
- **WHEN** aún no existe ningún modelo bajo `app/Domains/*/Models`
- **THEN** el test de completitud pasa (conjunto vacío) y `module:audit` sin argumentos
  reporta que no hay entidades que auditar
