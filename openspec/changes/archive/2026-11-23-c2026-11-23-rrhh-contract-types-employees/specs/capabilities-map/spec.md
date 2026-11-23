# Spec Delta

## Purpose

Índice on-demand de capabilities/standards/skills para que los agents carguen
solo el contexto pertinente en cada change, con RRHH como ejemplo de referencia.

## ADDED Requirements

### Requirement: Mapa de capabilities publicado
El repositorio SHALL publicar un mapa de capabilities (índice breve) que
indique, al menos para el patrón de un módulo nuevo como RRHH, qué standards,
specs/capabilities y skills cargar. SHALL estar enlazado desde el índice de
standards. RRHH oleada 1 SHALL figurar como ejemplo de referencia.

#### Scenario: Mapa usable
- **WHEN** un agente 10/20 arranca un change de módulo posterior
- **THEN** puede seguir el mapa para cargar solo el contexto pertinente
  (RRHH figura como ejemplo de referencia)

#### Scenario: Visible en índice standards
- **WHEN** se consulta `docs/standards/README.md`
- **THEN** el mapa aparece como documento del canon
