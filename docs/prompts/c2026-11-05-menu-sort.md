<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-05-menu-sort
Rule: 10-architecture
Summary: Ordenar el menú lateral (Modules y Functionalities) por la columna
  `sort` ya presente en BD (con valores sembrados), con desempate por `slug`.
  Dejar el contrato explícito en spec.
Goals:
- El menú lateral SHALL ordenar raíces (Modules) e hijos (Functionalities) por
  `sort` (no por `slug` como criterio primario).
- Criterio: `sort` ASC, **NULLS LAST**, desempate `slug` ASC (ambos confirmados
  Luis).
- Change nuevo (no engordar `menu-modules-realign`); el orden debe quedar bien
  reflejado en el spec OpenSpec.
- Columnas `sort` ya migradas en `modules` y `functionalities`; no hace falta
  inventar la migración — ya existen y hay valores.
NonGoals:
- (No se listaron NonGoals explícitos en el debate; el NonGoal del brief
  `menu-modules-realign` «no cambiar orden con campo sort» queda **anulado**
  por este change.)
Skills:
- openspec-propose
- (menú / MenuCatalogCache — tenancy-rbac solo si 10 lo considera necesario)
Notes:
- Origen: `/opsx-explore` tras realign; Luis: «te sigues haciendo lío en la
  ordenación… he incluido sort… ya puedes ordenar según sort».
- Hallazgo explore: `MenuCatalogCache` sigue `orderBy('slug')` en Modules y
  Functionalities; `MenuBuilder` preserva ese orden; el aside no define el orden.
- Criterio de orden cerrado con Luis: `sort` ASC, NULLS LAST, desempate `slug`.
- Explore propuso sin UI de reordenado / edición de `sort` (valores ya en BD).
  Luis no lo contradijo ni lo afirmó; 10 no inventar alcance UI sin preguntar.
- **Proceso**: explore saltó el brief y ejecutó `/opsx-propose` en el mismo hilo
  creando ya `openspec/changes/c2026-11-05-menu-sort/`. Este fichero es el brief
  canónico a posteriori. 10-architecture (sesión nueva) debe **revisar/reescribir**
  esos artefactos contra este brief, no asumir que el propose previo es válido.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


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


## MODIFIED Requirements

<!-- Ninguno nombrado explícitamente en el debate; 10 enlaza la capability
     adecuada (p. ej. menu-visibility) al redactar el delta. -->
