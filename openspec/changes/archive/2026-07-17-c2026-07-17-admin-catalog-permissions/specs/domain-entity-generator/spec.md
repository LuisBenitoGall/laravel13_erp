## ADDED Requirements

### Requirement: Generador con soporte de catálogos globales
El generador `make:domain-entity` SHALL admitir un flag `--global` que produzca entidades
sin tenancy (sin `company_id`, sin `BelongsToCompany`), reemplazando el test de
aislamiento de tenant por uno que confirme visibilidad compartida entre empresas, según
`tooling.md` §1.

#### Scenario: Entidad global generada sin tenancy
- **WHEN** se ejecuta `make:domain-entity` con `--global` sobre una entidad de prueba
- **THEN** el modelo generado no usa `BelongsToCompany`, la migración no lleva
  `company_id`, y el test generado confirma que el registro es visible igual desde
  cualquier empresa
