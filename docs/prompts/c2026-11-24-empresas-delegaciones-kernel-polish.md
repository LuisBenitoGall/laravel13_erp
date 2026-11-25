<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-24-empresas-delegaciones-kernel-polish
Rule: 10-architecture
Summary: Cerrar el hilo Empresas/Delegaciones documentando en el kernel (y R-* /
  glosario / adaptadores always-on que citen tenancy) el modelo matriz–
  delegación–workplace y las reglas aplazadas de sociedad en documentos
  comerciales; pulir la ficha Edit de Delegación con panel de solo lectura de
  centros de la matriz que la tienen vinculada y copy que distinga sede legal
  de la hija vs sedes operativas de la matriz.
Goals:
- Enmendar `docs/standards/kernel.md` (y `architecture.md` R-TEN-* / glosario /
  `.cursor/rules/01-kernel.mdc` / mención mínima en `CLAUDE.md` si contradicen)
  para dejar **constitución escrita** del modelo de grupo ya implementado y de
  las reglas de documentos **aplazadas** (dropdown de sociedad), de modo que
  al llegar a presupuestos/pedidos no se reabra el debate.
- Contenido normativo mínimo a dejar explícito (10-architecture redacta el
  texto canónico; no improvisar otro modelo):
  1. **Matriz**: Company con `parent_id` null; es la empresa de sesión SaaS;
     aparece en switcher / select de empresa; alta genérica crea
     `company_accounts`.
  2. **Delegación**: Company con `parent_id` = matriz; tiene NIF; mismo “ser
     empresa” en datos; **no** aparece en el switcher ni se elige como
     CurrentCompany por ese dropdown; alta vía CRUD Delegaciones **sin**
     `company_accounts`.
  3. **Workplace de la matriz**: eje operativo del grupo en sesión matriz;
     mapeo N:M a Delegaciones hijas con como máximo una **preferida**
     (opcional) por workplace.
  4. **Workplace de la Delegación**: sede(s) cuyo `company_id` es la hija
     (domicilio / operativa de esa society); distinto del mapeo N:M de sedes
     de la matriz.
  5. **Documentos comerciales (aplazado — solo documentar, no implementar):**
     en presupuesto/pedido venta, “nosotros” = `orders.provider_id`; en compra,
     “nosotros” = `orders.customer_id` (ambas FK a `companies.id`). Default =
     matriz de sesión; UI con dropdown de Delegaciones hijas (y la preferida
     del workplace del documento como default si existe mapeo); el usuario
     puede cambiar la sociedad hasta **antes** de facturar; tras facturar
     queda bloqueada. Albarán hereda la sociedad del pedido. Listados desde
     la matriz: por defecto ven documentos propios **y** de hijas; filtro
     multi-delegación.
  6. Dejar claro que (5) **no** se implementa en este change; es invariante
     futura citable.
- Pulido UX Edit Delegación:
  - Panel **solo lectura** que liste los Workplaces de la **matriz** que
    tienen esta Delegación en el mapeo N:M (y si está marcada preferida).
  - Copy / ayuda visible que distinga: (a) centros de trabajo **de esta
    Delegación** (ya gestionables en la ficha) vs (b) centros **de la matriz**
    que la vinculan vía mapeo.
- Tests Pest del panel RO (perímetro: solo workplaces de la matriz con vínculo
  a esa hija) y comprobación de que el kernel/adaptadores citan el modelo sin
  contradicción residual (“toda Company es tenant de sesión” sin matiz).
NonGoals:
- Implementar dropdown de sociedad en presupuestos/pedidos, herencia en
  albaranes, bloqueo post-factura, ni listados/filtro de documentos de grupo.
- Almacenes, catálogo de productos, CustomerProviders, obras, contabilidad o
  tesorería.
- Cambiar reglas de switcher, alta sin `company_accounts`, o el mapeo N:M
  Workplace↔Delegación ya archivados (salvo leerlos / exponer el panel RO).
- Fusionar Workplace y Delegación; permitir entrar en Delegación como
  CurrentCompany vía atajos nuevos.
- Ampliar CRUD Delegaciones más allá del panel RO + copy descritos.
Skills:
- openspec-propose
- tenancy-rbac
Notes:
- Origen: `/opsx-explore` post CRUD Delegaciones + mapeo workplace-delegation
  archivados. Usuario: no empezar pedidos aún; tubería posterior acordada
  (tras cerrar empresas/delegaciones): almacenes → productos → presupuestos →
  pedidos → albaranes → facturas → contabilidad/tesorería (no exhaustiva; en
  visión Fase 1 también figuran partners/obras — fuera de este change).
- Misión de este handoff: **solo brief** → sesión nueva `/opsx-propose` genera
  artefactos. Este change **es** la corrección/ampliación de estándar
  (kernel), no una excepción improvisada en código.
- Estado UI hoy: Edit Workplace ya tiene panel “Delegaciones vinculadas”;
  Edit Delegación gestiona workplaces con `company_id` = hija
  (`DelegationWorkplacesPanel`); **falta** el panel inverso RO matriz→vínculos
  y la constitución en kernel.
- Capacidades candidatas (decide 10): delta docs no son OpenSpec capability;
  UI → `company-delegations` y/o `workplace-delegation-map`. Specs de
  comportamiento solo para el panel RO; el texto kernel va en design/tasks de
  standards.
- Change **nuevo**.


## ADDED Requirements

### Requirement: Constitución matriz / delegación / workplace en kernel
El kernel (y R-* de architecture / glosario / adaptadores always-on que
describan tenancy) SHALL documentar el modelo de grupo: matriz vs Delegación
(sesión y switcher; `company_accounts` solo en alta de matriz/genérica),
Workplace de matriz (mapeo N:M + preferida) vs Workplace de Delegación, y la
sección de **invariantes aplazadas** de sociedad en documentos comerciales
descrita en Goals §5–6. SHALL NOT implementar esas invariantes de documentos
en este change.

#### Scenario: Kernel distingue matriz y delegación
- **WHEN** un agente lee el kernel tras este change
- **THEN** queda explícito que Delegación no es elegible como CurrentCompany
  vía switcher y que su alta no crea `company_accounts`

#### Scenario: Invariantes de documentos citables sin código
- **WHEN** se abra un change futuro de presupuestos/pedidos
- **THEN** el kernel ya fija default matriz, dropdown a hijas, preferida de
  workplace como default si existe, bloqueo post-factura, herencia albarán y
  listados de grupo con filtro — como norma a implementar entonces

### Requirement: Panel RO de sedes de matriz vinculadas a la Delegación
El Edit de Delegación SHALL mostrar un panel de solo lectura con los Workplaces
de la matriz (`company_id` = padre) que tienen esta Delegación en el mapeo N:M,
indicando si es la preferida de esa sede. SHALL NOT permitir editar el mapeo
desde ese panel (la escritura sigue en Edit Workplace). SHALL incluir copy que
distinga esos centros de los workplaces cuyo `company_id` es la propia
Delegación.

#### Scenario: Lista vínculos desde la hija
- **WHEN** la matriz tiene un workplace vinculado a la Delegación D (preferida
  o no) y un actor abre el Edit de D
- **THEN** ese workplace aparece en el panel RO con el indicador de preferida
  si aplica

#### Scenario: Sin vínculos
- **WHEN** ningún workplace de la matriz tiene a D en el mapeo
- **THEN** el panel RO se muestra vacío (o empty state), sin error

#### Scenario: Copy distintivo
- **WHEN** el actor ve el Edit de Delegación
- **THEN** el texto deja claro la diferencia entre centros de la Delegación y
  centros de la matriz que la vinculan

---

## MODIFIED Requirements

### Requirement: CRUD Delegaciones / ficha Edit (company-delegations)
La ficha Edit de Delegación SHALL incorporar el panel RO y el copy del
requisito anterior, además de la gestión ya existente de datos de la Company
hija y de sus workplaces propios.

#### Scenario: Edit Delegación con panel RO
- **WHEN** un actor autorizado abre `/admin/delegations/{id}/edit`
- **THEN** ve el panel de sedes de matriz vinculadas (RO) además del resto de
  la ficha
