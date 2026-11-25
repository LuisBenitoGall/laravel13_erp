<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-25-configuracion-conversion-monedas
Rule: 10-architecture
Summary: Functionality global «Conversión monedas» bajo Configuración (SA):
  maestro de cotizaciones por par dirigido y fecha, alta/edición manual con
  ambos sentidos (p. ej. EUR→USD y USD→EUR), listado ServerTable; vigente =
  última cotización existente (aunque sea de hace días) hasta que se cambie a
  mano. Sin API automática ni monedas por Company en este change.
Goals:
- Functionality `exchange-rates` (o slug que fije 10) bajo Module
  `configuracion`, label UI **Conversión monedas**, permisos Spatie estándar;
  acceso alineado a Configuración (SA / capas AdminOnly + AND).
- Persistencia de cotizaciones: par dirigido `from_currency_id` →
  `to_currency_id`, `rate` (multiplicador: 1 unidad origen → rate unidades
  destino), `rate_date` (día). Unique `(from, to, rate_date)`. Monedas del
  catálogo global `Currency` Active (u existentes no soft-deleted según design).
- Precisión del rate: **6 decimales**.
- Pares **según necesidad** (no matriz completa n×(n−1)); el SA da de alta los
  pares que use el ERP (p. ej. EUR↔USD).
- UI listado + create/edit: para un par y una fecha, **dos campos** (A→B y
  B→A) que al guardar escriben/actualizan **dos filas** (sentidos
  independientes). Botón/acción opcional “Rellenar inverso” (`1/rate`) para
  ayudar; tras editar, no recalcular solo el otro sentido.
- **Vigencia / uso futuro (norma citable, sin implementar docs):** la
  cotización aplicable a una operación en fecha D es la registrada con
  `rate_date` ≤ D más reciente para ese par dirigido (aunque sea de días
  atrás). Mientras el SA no cambie/anote otra cotización, la que hay es la
  buena. En la operación futura se **snapshot** del rate (como precio de
  producto), no reconsulta viva al catálogo.
- Index `<ServerTable>` usable (par, fecha, rates o columnas por sentido —
  design elige presentación amable).
- Tests Pest: CRUD, unique por par+fecha+sentido, resolución “última ≤ D”,
  permisos Configuración.
NonGoals:
- Actualización automática vía API/servicio externo (oleada posterior; el
  modelo from/to/rate/date debe permitirlo sin rediseño).
- Monedas de uso por Company/Delegación (opción A acordada; change **después**
  de este maestro).
- Tipos de cambio en presupuestos/pedidos/facturas, snapshot en documentos,
  redondeo de importes convertidos (solo se fija precisión del rate).
- Bid/ask bancario, multi-tipo (spot/average), moneda por workplace.
- Cambiar el catálogo `Currency` más allá de consumirlo.
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
Notes:
- Origen: `/opsx-explore` post Delegaciones/opción A workplaces; antes de
  company_currencies porque habrá conversión. Usuario: no versado en FX —
  decisiones 1/2/5 por recomendación explore; 3/4/6 y “tomar la que exista
  aunque sea de hace días / vigente hasta cambiar a mano” confirmados por
  Luis (2026-11-25).
- Catálogo `Currency` global ya existe (Configuración → Monedas).
- Entidad naming: preferir término glosario tipo ExchangeRate / CurrencyConversion
  (10 decide inglés normativo + label «Conversión monedas»).
- Orden de producto acordado: (1) este maestro → (2) monedas por Company →
  (3) docs con default + snapshot → (4) API.
- Misión explore: **solo brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: Maestro Conversión monedas bajo Configuración
El sistema SHALL exponer bajo Configuración el ítem **Conversión monedas**
(Functionality global AdminOnly/SA) con Index ServerTable y alta/edición manual
de cotizaciones. SHALL NOT depender de CurrentCompany para el catálogo de
cotizaciones (catálogo global, como Currency).

#### Scenario: Acceso SA
- **WHEN** un Super Admin (o actor con acceso a Configuración + permiso view)
  abre el menú Configuración
- **THEN** ve Conversión monedas y puede listar cotizaciones

#### Scenario: Usuario sin Configuración
- **WHEN** un usuario sin acceso a Configuración / sin permiso
- **THEN** no ve el ítem ni opera el CRUD

### Requirement: Cotización por par dirigido, fecha y rate
El sistema SHALL persistir cotizaciones con moneda origen, moneda destino,
fecha y rate (6 decimales). SHALL garantizar unicidad por
(origen, destino, fecha). SHALL permitir pares según necesidad (no exigir
todos los cruces del catálogo).

#### Scenario: Alta de un par en ambos sentidos
- **WHEN** el actor guarda para fecha D el par EUR/USD con rate EUR→USD y
  USD→EUR
- **THEN** existen dos filas (EUR→USD y USD→EUR) con `rate_date` = D

#### Scenario: No duplicar mismo sentido y fecha
- **WHEN** ya existe EUR→USD en fecha D y se intenta otra fila igual
- **THEN** se rechaza o se actualiza según design (unique respetado)

### Requirement: Vigencia = última cotización ≤ fecha de operación
Para un par dirigido y una fecha de operación D, la cotización aplicable SHALL
ser la de mayor `rate_date` ≤ D. Si no se ha anotado una nueva, la existente
(aunque sea anterior) SHALL seguir siendo la vigente. Este change SHALL
implementar/consultar esa regla al menos en dominio/tests (helper/Action de
resolución); SHALL NOT aún enganchar documentos comerciales.

#### Scenario: Sin cotización el día D pero sí antes
- **WHEN** existe EUR→USD solo en fecha D−3 y se resuelve para operación en D
- **THEN** se obtiene el rate de D−3

#### Scenario: Tras anotar una cotización nueva
- **WHEN** se guarda EUR→USD en fecha D y antes solo había D−3
- **THEN** la vigente para D (y posteriores hasta otro cambio) es la de D

### Requirement: Snapshot futuro en operaciones (solo norma)
Cuando existan documentos comerciales, la operación SHALL guardar el rate
aplicado en el momento (snapshot), no depender solo de la consulta viva al
maestro. Este change SHALL documentarlo en design/spec como invariante
aplazada; SHALL NOT implementar columnas en pedidos/facturas.

#### Scenario: Norma citable
- **WHEN** se abra un change futuro de pedidos/facturas con FX
- **THEN** el contrato ya fija snapshot del rate como el precio de producto

---

## MODIFIED Requirements

### Requirement: Pilares / menú Configuración (menu-core-pillars)
El Module `configuracion` SHALL incluir el ítem Conversión monedas hacia el
Index del maestro de cotizaciones.

#### Scenario: Ítem en Configuración
- **WHEN** SA obtiene el menú Configuración
- **THEN** aparece Conversión monedas además de Monedas y el resto de
  catálogos ya existentes
