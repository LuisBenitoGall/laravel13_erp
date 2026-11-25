## Purpose

Maestro global de cotizaciones (`ExchangeRate`) bajo Configuración / Conversión
monedas: alta manual por par dirigido único (sin historial por fecha),
resolución = rate actual y norma de snapshot aplazada para documentos
comerciales futuros.

## Requirements

### Requirement: Catálogo global ExchangeRate
El sistema SHALL persistir cotizaciones como catálogo global sin `company_id`,
con soft delete, par dirigido origen→destino y `rate` a 6 decimales. SHALL NOT
usar `rate_date`. SHALL garantizar unicidad por (origen, destino) entre
registros no eliminados.

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

#### Scenario: Unicidad por par y fecha
- **WHEN** ya existe EUR→USD y se intenta crear otra fila EUR→USD
- **THEN** no coexisten dos filas activas del mismo sentido (unique por
  origen+destino, sin fecha)

### Requirement: Par único sin historial por fecha
El sistema SHALL persistir cada par dirigido (origen, destino) una sola vez,
sin `rate_date`. SHALL NOT permitir un segundo alta del mismo par unordered
(p. ej. EUR/USD si ya existe EUR→USD y/o USD→EUR). Los rates se actualizan
in-place vía Edit.

#### Scenario: Alta de un par nuevo
- **WHEN** el actor guarda el par EUR/USD con sus tasas
- **THEN** existen las filas dirigidas correspondientes y un único par lógico
  EUR/USD en el Index

#### Scenario: Intento de repetir el mismo par
- **WHEN** ya existe el par EUR/USD y se intenta Create otra vez
- **THEN** la petición se rechaza con error de validación y no se duplica el
  par

#### Scenario: Actualizar tasas
- **WHEN** el actor edita las tasas del par EUR/USD
- **THEN** se actualizan los valores del mismo par sin crear otra fila de
  historial

### Requirement: CRUD Configuración / Conversión monedas
El sistema SHALL exponer bajo Configuración → Conversión monedas un Index
ServerTable de **pares** (par, rate A→B, rate B→A; sin fecha de cotización)
con Acciones Edit y Delete confirm, y Create/Edit con par de monedas y dos
rates. Autorización vía Functionality `exchange-rates` y permisos
`exchange-rates.*`. El acceso al Index SHALL exigir `exchange-rates.view`.

#### Scenario: Usuario autorizado abre Conversión monedas
- **WHEN** un usuario con `exchange-rates.view` (o Super Admin) abre
  Configuración → Conversión monedas
- **THEN** ve el Index ServerTable de exchange-rates (una fila por par)

#### Scenario: Sin permiso view
- **WHEN** un usuario sin `exchange-rates.view` y sin Super Admin fuerza la URL
  del Index
- **THEN** recibe 403

#### Scenario: Alta de ambos sentidos
- **WHEN** el actor guarda el par EUR/USD con rate EUR→USD y USD→EUR
- **THEN** existen dos filas (EUR→USD y USD→EUR) y una sola fila de par en el
  Index

#### Scenario: Un solo sentido
- **WHEN** el actor guarda solo el rate A→B (B→A vacío)
- **THEN** existe la fila A→B y no se crea B→A; el par unordered queda
  ocupado

### Requirement: Rate positivo y monedas distintas Active
El sistema SHALL exigir rate > 0 con precisión de 6 decimales, monedas origen
y destino distintas, y en alta/edición seleccionar solo Currency Active no
trashed. SHALL NOT permitir origen = destino.

#### Scenario: Mismo origen y destino
- **WHEN** se intenta guardar un rate con from = to
- **THEN** la petición se rechaza con error de validación

#### Scenario: Rate no positivo
- **WHEN** se envía rate ≤ 0
- **THEN** la petición se rechaza con error de validación

### Requirement: Inverso calculado y editable
Al informar un sentido, la UI SHALL permitir calcular el inverso (`1/rate`)
como ayuda. El valor inverso SHALL permanecer editable; el actor puede
persistir un inverso distinto de `1/rate`.

#### Scenario: Rellenar inverso
- **WHEN** el actor introduce un rate A→B y pide rellenar inverso
- **THEN** el campo B→A se rellena con el inverso a 6 decimales y sigue
  editable

#### Scenario: Inverso distinto del cálculo
- **WHEN** el actor guarda un inverso distinto de `1/rate`
- **THEN** se persisten ambos valores tal como los envió

### Requirement: Purga de datos de prueba del modelo con fecha
Al aplicar este change, el sistema SHALL eliminar los registros previos de
`exchange_rates` (datos de prueba / historial por fecha) antes de imponer la
nueva unicidad.

#### Scenario: Tras aplicar la migración
- **WHEN** se ejecuta la migración de unicidad sin fecha
- **THEN** no quedan filas del historial de prueba por `rate_date`

### Requirement: Vigencia = rate actual del par
Para un par dirigido, el sistema SHALL resolver la cotización como la fila
vigente única de ese sentido (valores actuales del maestro). SHALL NOT filtrar
por fecha de cotización del catálogo. SHALL NOT inventar el inverso a partir
del sentido contrario.

#### Scenario: Consulta al maestro
- **WHEN** un consumidor pide el rate del par EUR→USD
- **THEN** obtiene el rate actual de la única fila EUR→USD, si existe

#### Scenario: Sin fila del sentido
- **WHEN** no existe fila EUR→USD (aunque exista USD→EUR)
- **THEN** la resolución indica ausencia de cotización para EUR→USD

### Requirement: Snapshot futuro en operaciones (norma)
Cuando existan documentos comerciales con FX, la operación SHALL guardar el
rate aplicado en el momento (snapshot), no depender solo de la consulta viva
al maestro. Este capability SHALL documentar la norma; SHALL NOT añadir
columnas en pedidos/facturas en este change. La dimensión temporal de negocio
queda en ese snapshot, no en filas históricas del catálogo.

#### Scenario: Norma citable
- **WHEN** se abra un change futuro de pedidos/facturas con FX
- **THEN** el contrato fija que la operación congela el rate vigente del
  maestro en ese momento
