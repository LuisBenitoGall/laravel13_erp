<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-24-empresas-workplace-delegation-map
Rule: 10-architecture
Summary: Mapeo operativo N:M entre centros de trabajo (Workplace de la matriz)
  y Delegaciones (Companies hijas), con sociedad preferida opcional por sede
  como default futuro para documentos; sin tocar pedidos ni listados de grupo.
Goals:
- Relación N:M Workplace ↔ Delegación (Company con parent_id = matriz): un
  workplace de la company de sesión (matriz) puede vincularse a N delegaciones
  hijas; una delegación puede usarse desde N workplaces.
- UI de gestión del mapeo (p. ej. en Edit de Workplace y/o panel desde
  Delegaciones) acotada al perímetro: workplaces de la matriz en sesión;
  delegaciones = hijas de esa matriz.
- Permitir marcar una delegación **preferida** (o equivalente) por workplace
  como valor por defecto para oleadas posteriores (pedidos); la elección
  manual en documentos sigue fuera de este change.
- Tests Pest de vínculo, perímetro y preferida única por workplace.
NonGoals:
- Dropdown sociedad en presupuestos/pedidos, herencia albarán, bloqueo
  post-factura.
- Listados de documentos del grupo / filtro multi-delegación.
- Cambiar switcher, CRUD Delegaciones, o company_accounts.
- Entrar en delegación como CurrentCompany.
- Fusionar Workplace y Delegación en una sola entidad.
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
Notes:
- Origen: `/opsx-explore` post CRUD Delegaciones archivado
  (`c2026-11-23-empresas-delegaciones-crud`). Oleada 2 del plan: puente sede ↔
  sociedad antes (o como base) del dropdown en pedidos.
- Workplace sigue BelongsToCompany de la matriz; Delegación = Company hija.
  No inventar requisitos de pedidos aquí.
- Change **nuevo**.


## ADDED Requirements

### Requirement: Mapeo N:M Workplace–Delegación
El sistema SHALL permitir vincular cada Workplace de la matriz de sesión a cero
o más Delegaciones (Companies con `parent_id` = esa matriz), y cada Delegación
a cero o más de esos Workplaces. SHALL NOT permitir vincular workplaces o
companies ajenas al perímetro de la matriz en sesión.

#### Scenario: Vincular sede a varias delegaciones
- **WHEN** un actor autorizado asocia un workplace de la matriz A a las
  delegaciones D1 y D2 (hijas de A)
- **THEN** quedan persistidos ambos vínculos

#### Scenario: Perímetro
- **WHEN** se intenta vincular un workplace o una company fuera de la matriz
  de sesión / no hija
- **THEN** se rechaza

### Requirement: Delegación preferida por Workplace
El sistema SHALL permitir como máximo una Delegación marcada como preferida
por Workplace (default futuro para documentos). SHALL NOT exigir preferida
(puede no haber ninguna).

#### Scenario: Una preferida
- **WHEN** el actor marca D2 como preferida en un workplace que ya tenía D1
  preferida
- **THEN** solo D2 queda preferida

#### Scenario: Sin preferida
- **WHEN** un workplace tiene vínculos pero ninguna preferida
- **THEN** el estado es válido

---

## MODIFIED Requirements

### Requirement: CRUD Workplace (workplaces)
El Edit (y/o Create) de Workplace de la matriz SHALL permitir gestionar los
vínculos a Delegaciones del mapeo anterior, sin cambiar el resto del MVP de
dirección/featured/status.

#### Scenario: Gestión de vínculos en ficha sede
- **WHEN** el actor autorizado edita un workplace de la matriz
- **THEN** puede añadir/quitar delegaciones vinculadas y la preferida
