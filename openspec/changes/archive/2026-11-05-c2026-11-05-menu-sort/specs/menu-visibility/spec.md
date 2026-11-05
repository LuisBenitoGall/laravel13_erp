# Spec Delta: menu-visibility

## ADDED Requirements

### Requirement: Ordenación de menú por sort
El sistema SHALL ordenar las raíces del menú (Modules) y las Functionalities
hijas por `sort` ASC con valores NULL al final (NULLS LAST) y desempate por
`slug` ASC. SHALL NOT usar solo `slug` como criterio primario.

#### Scenario: Modules ordenados por sort
- **WHEN** hay Modules visibles con distinto `sort`
- **THEN** el menú lateral los muestra en orden de `sort` (menor primero), y a
  igualdad de `sort` por `slug`

#### Scenario: Functionalities ordenadas por sort
- **WHEN** un Module visible tiene Functionalities con distinto `sort`
- **THEN** el submenú las muestra en orden de `sort`, y a igualdad de `sort` por
  `slug`

#### Scenario: NULL sort al final
- **WHEN** hay Modules (o Functionalities) visibles con `sort` NULL junto a
  otros con `sort` numérico
- **THEN** los de `sort` NULL quedan después de los que tienen valor, y entre
  sí se desempata por `slug`
