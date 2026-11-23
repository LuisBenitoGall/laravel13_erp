<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-23-empresas-delegaciones-crud
Rule: 10-architecture
Summary: CRUD de Delegaciones bajo el menú Empresas: son Companies hijas
  (`parent_id` = matriz de sesión) con NIF, tratamiento de empresa, al menos un
  workplace y vínculo del creador en `user_companies`; alta diferenciada del
  Create de empresa (sin `company_accounts`); no aparecen en el dropdown de
  cambio de empresa.
Goals:
- Functionality/CRUD Delegaciones (Index ServerTable, Create/Edit) bajo Empresas.
- Alta diferenciada: crea Company con `parent_id` = company de sesión (matriz);
  NIF; creador auto-vinculado en `user_companies`; exige ≥1 centro de trabajo
  (workplace); SHALL NOT llamar a `AssignDefaultCompanyAccount` ni crear fila en
  `company_accounts`.
- El selector/switcher de empresa de sesión SHALL listar solo matrices (companies
  sin `parent_id` / no delegaciones); las delegaciones no se eligen como
  CurrentCompany desde ese dropdown.
- Reutilizar modelo Company + jerarquía `parent_id` ya existente; no inventar
  tabla Delegacion distinta.
NonGoals:
- Dropdown de sociedad (`provider_id`/`customer_id`) en presupuestos/pedidos,
  herencia en albaranes, bloqueo post-factura.
- Listados de documentos del grupo (matriz ve pedidos/albaranes/facturas de
  hijas) ni filtro multi-delegación (decisión de producto aplazada a oleada
  posterior; no implementar en este change).
- Mapeo N:M workplace ↔ delegación más allá de lo necesario para el alta con
  ≥1 workplace.
- Cambiar el Create genérico de Company “Mis empresas” para usos que no sean
  delegación (salvo el cableado mínimo que el design necesite para no asignar
  cuenta en el flujo Delegación).
- Entrar en delegación como sesión activa vía otros atajos (fuera de alcance
  oleada 1).
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
Notes:
- Origen: `/opsx-explore` menú Empresas / Delegaciones. Contexto de producto
  (oleadas futuras, no este change): sesión operativa en matriz; pedidos venta
  `provider_id` / compra `customer_id` = nosotros (default matriz, dropdown a
  hijas hasta facturar); workplace operativo N:M con delegaciones; matriz verá
  docs propios + hijas con filtro. Oleada 1 = solo CRUD + switcher + sin
  company_accounts.
- Hoy: `parent_id` y tab Jerarquía en Edit Company existen; Create Company
  siempre asigna Account default; switcher lista companies del vínculo sin
  distinguir hijas; no hay functionality Delegaciones.
- Change **nuevo**. Capacidades candidatas: delta `company-hierarchy` /
  `company-create` / menú empresas + capability nueva o slug `delegaciones`
  según decida 10-architecture.


## ADDED Requirements

### Requirement: CRUD Delegaciones como Company hija
El sistema SHALL exponer un CRUD de Delegaciones bajo Empresas. Una Delegación
SHALL ser una Company con `parent_id` igual a la company de sesión (matriz),
con NIF, el mismo tratamiento de empresa acordado, al menos un Workplace, y el
usuario creador vinculado en `user_companies`. El alta SHALL ser un flujo
distinto al Create genérico de empresa. SHALL NOT crear `company_accounts` al
dar de alta una Delegación.

#### Scenario: Alta delegación sin company_accounts
- **WHEN** un actor autorizado crea una Delegación válida
- **THEN** existe la Company hija con `parent_id` = matriz de sesión, el
  creador está en `user_companies`, hay ≥1 workplace, y no hay fila nueva en
  `company_accounts` para esa Company

#### Scenario: Alta diferenciada
- **WHEN** el actor abre el Create de Delegaciones
- **THEN** no es el mismo flujo UI/acción que el Create de Mis empresas
  (sin confusión de alta genérica)

### Requirement: Switcher solo matrices
El dropdown de cambio de empresa de sesión SHALL mostrar solo companies que no
sean delegaciones (sin `parent_id` de hija / matrices). SHALL NOT permitir
elegir una Delegación como empresa activa desde ese dropdown.

#### Scenario: Delegación no listada
- **WHEN** el usuario tiene vinculada una matriz y una o más delegaciones hijas
- **THEN** el switcher solo ofrece la matriz (u otras matrices vinculadas), no
  las hijas

---

## MODIFIED Requirements

### Requirement: Alta Company con Account default (company-create / accounts)
El flujo de alta de **Delegación** SHALL ser excepción: no asigna Account
default ni crea `company_accounts`. El Create genérico de Company (no
delegación) mantiene la asignación de cuenta Active con Account default.

#### Scenario: Create empresa normal sigue con cuenta
- **WHEN** se crea una Company por el flujo genérico (no Delegación)
- **THEN** sigue creándose el company_accounts Active con Account default
