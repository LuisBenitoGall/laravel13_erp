<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-25-conversion-monedas-par-unico
Rule: 10-architecture
Summary: Corregir el maestro Conversión monedas: un par de monedas existe una sola
  vez (sin historial por fecha en el catálogo). Se actualizan sus tasas in-place;
  el inverso se calcula pero es editable; prohibido repetir el mismo par. La
  operación futura (fuera de alcance) hará snapshot del valor vigente. Purgar
  datos de prueba actuales.
Goals:
- Un par unordered (p. ej. EUR/USD) = un único registro lógico en el maestro;
  el sistema SHALL NOT permitir dar de alta el mismo par otra vez.
- Quitar el uso de negocio de `rate_date` como eje de historial/vigencia en el
  catálogo (la fecha “no sirve de gran cosa” en el maestro): el par va
  actualizando sus valores y esos son los vigentes.
- Ambos sentidos siguen existiendo en UI/persistencia: al informar un rate se
  **calcula el inverso** (`1/rate`) pero el inverso **se puede modificar** a
  mano (no queda bloqueado).
- Index / Create / Edit alineados a “una fila = un par” (sin columnas/fechas de
  historial de cotización en el listado).
- Resolver cotización vigente = rate actual del par (sin “última ≤ D”); la
  norma de **snapshot en la operación** se mantiene como invariante aplazada
  (la operación guarda el valor que había en ese momento).
- Eliminar los datos existentes de `exchange_rates` (son de prueba) y dejar el
  catálogo listo con la nueva unicidad.
- Tests: unique por par (no repetir), upsert/edición in-place, cálculo de
  inverso editable, authz existente.
NonGoals:
- Implementar snapshot en presupuestos/pedidos/facturas (solo norma citable).
- API / proveedor externo de tipos de cambio.
- Monedas por Company / Delegación.
- Redondeo de importes convertidos más allá de la precisión del rate ya fijada
  (6 decimales).
- Bid/ask, multi-tipo, moneda por workplace.
- Reabrir el catálogo `Currency` más allá de consumirlo.
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
Notes:
- Origen: `/opsx-explore` sobre el CRUD ya implementado de
  `c2026-11-25-configuracion-conversion-monedas` (UI par+fecha, unique
  `(from,to,rate_date)`, Resolve ≤ D). Luis corrige el modelo de producto
  (2026-11-25 noche): el par solo existe una vez; no historial por fecha en el
  maestro; la operación es quien congela el valor.
- Respuestas explícitas: (1) calcular inverso pero poder modificarlo; (2)
  eliminar datos existentes de prueba; el sistema no debe permitir repetir
  pares.
- Código/UI actuales (Index agrupado por par+fecha, upsert por día, columna
  fecha) quedan **obsoletos** respecto a este contrato; 10 decide el diseño
  de migración (drop `rate_date` vs dejarla sin semántica) sin ampliar
  NonGoals.
- Misión explore: **solo este brief** → sin artefactos OpenSpec en esta
  sesión; `/opsx-propose` en sesión nueva cuando Luis lo pida.


## ADDED Requirements

### Requirement: Par único sin historial por fecha en el maestro
El sistema SHALL persistir cada par de monedas una sola vez. SHALL NOT permitir
un segundo alta del mismo par. SHALL NOT modelar vigencia del catálogo como
serie de cotizaciones por `rate_date`. Los valores del par se actualizan
in-place y son los vigentes para quien consulte el maestro.

#### Scenario: Alta de un par nuevo
- **WHEN** el actor guarda el par EUR/USD con sus tasas
- **THEN** existe un único registro lógico EUR/USD en el maestro

#### Scenario: Intento de repetir el mismo par
- **WHEN** ya existe EUR/USD y se intenta crear otra vez el mismo par
- **THEN** el sistema lo rechaza (o lo trata como edición del existente,
  según design) y no deja dos pares distintos

#### Scenario: Actualizar tasas
- **WHEN** el actor edita las tasas del par EUR/USD
- **THEN** se actualizan los valores del mismo par; no se crea una fila de
  otra “fecha”

### Requirement: Inverso calculado y editable
Al informar un sentido, el sistema SHALL poder calcular el inverso (`1/rate`)
como ayuda. El valor inverso SHALL permanecer editable; el actor puede
guardarlo distinto de `1/rate`.

#### Scenario: Rellenar inverso
- **WHEN** el actor introduce 1 EUR = 1,085000 USD y pide rellenar inverso
- **THEN** el campo USD→EUR se rellena con el inverso a 6 decimales y sigue
  siendo editable

#### Scenario: Inverso distinto del cálculo
- **WHEN** el actor guarda un inverso distinto de `1/rate`
- **THEN** se persisten ambos valores tal como los envió

### Requirement: Purga de datos de prueba
El change SHALL eliminar los registros actuales de cotizaciones de prueba
antes o al aplicar la nueva unicidad, de modo que no queden pares duplicados
por fecha del modelo anterior.

#### Scenario: Tras aplicar
- **WHEN** se aplica la migración/ajuste de unicidad
- **THEN** la tabla de cotizaciones no conserva el historial de prueba por
  fechas del modelo viejo

### Requirement: Snapshot en operación (norma aplazada)
Cuando existan documentos comerciales con FX, la operación SHALL guardar el
rate aplicado en ese momento (snapshot). Este change SHALL documentar la
norma; SHALL NOT implementar columnas en pedidos/facturas.

#### Scenario: Norma citable
- **WHEN** se abra un change futuro de pedidos/facturas con FX
- **THEN** el contrato fija que la operación congela el rate vigente del
  maestro en ese momento

---

## MODIFIED Requirements

### Requirement: Vigencia / resolución de cotización (admin-exchange-rates)
Deja de aplicarse “última cotización con rate_date ≤ D”. La cotización
aplicable desde el maestro SHALL ser la del par único vigente (valores
actuales). La dimensión temporal de negocio queda en el snapshot de la
operación, no en filas históricas del catálogo.

#### Scenario: Consulta al maestro
- **WHEN** un consumidor pide el rate del par EUR→USD
- **THEN** obtiene el rate actual del único par EUR/USD (sentido pedido), sin
  filtrar por fecha de cotización del catálogo

### Requirement: Index Conversión monedas
El Index SHALL listar un fila por par (sin presentar historial por fecha de
cotización). Create/Edit operan sobre ese par único.

#### Scenario: Listado
- **WHEN** SA abre Conversión monedas tras el cambio
- **THEN** ve como máximo una fila por par de monedas (p. ej. una EUR/USD)
