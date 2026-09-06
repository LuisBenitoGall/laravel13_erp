## ADDED Requirements

### Requirement: Generador make:domain-entity
El sistema SHALL proporcionar un comando `make:domain-entity {domain} {entity}` que, con
sus flags de `tooling.md` §1, genere de una vez todos los artefactos "Siempre" de la
matriz de `module-playbook.md`, sin sobreescribir ficheros existentes salvo `--force`.

#### Scenario: Generación completa de una entidad
- **WHEN** se ejecuta `make:domain-entity` contra un dominio/entidad de prueba con un
  flag activado (p. ej. `--status`)
- **THEN** se crean migración, modelo, enum, policy, actions, requests, resource,
  controller, factory, test feature, tipos TS y páginas React correspondientes,
  conformes a los templates

#### Scenario: No sobreescritura
- **WHEN** se ejecuta el generador sobre una entidad cuyos ficheros ya existen
- **THEN** no se sobreescribe ningún fichero salvo que se pase `--force` explícito

#### Scenario: Limpieza tras test del generador
- **WHEN** el test automatizado del generador termina (éxito o fallo)
- **THEN** no queda ninguna entidad de ejemplo permanente en el árbol del proyecto
