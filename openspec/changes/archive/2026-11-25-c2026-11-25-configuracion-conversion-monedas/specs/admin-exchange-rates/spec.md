# Spec Delta

## Purpose

Maestro global de cotizaciones (`ExchangeRate`) bajo Configuración / Conversión
monedas: alta manual por par dirigido y fecha, resolución vigente ≤ D y norma
de snapshot aplazada para documentos comerciales futuros.

## ADDED Requirements

### Requirement: Catálogo global ExchangeRate
El sistema SHALL persistir cotizaciones como catálogo global sin `company_id`,
con soft delete, par dirigido origen→destino, `rate` a 6 decimales y
`rate_date`. SHALL garantizar unicidad por (origen, destino, fecha) entre
registros no eliminados.

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

#### Scenario: Unicidad por par y fecha
- **WHEN** ya existe EUR→USD en fecha D y se vuelve a guardar el mismo sentido
  y fecha con otro rate
- **THEN** se actualiza el rate existente y no se crea una segunda fila

### Requirement: CRUD Configuración / Conversión monedas
El sistema SHALL exponer bajo Configuración → Conversión monedas un Index
ServerTable de cotizaciones dirigidas (origen, destino, rate, fecha) con
Acciones Edit y Delete confirm, y Create/Edit con par de monedas, fecha y dos
rates (A→B y B→A). Autorización vía Functionality `exchange-rates` y permisos
`exchange-rates.*`. El acceso al Index SHALL exigir `exchange-rates.view`.

#### Scenario: Usuario autorizado abre Conversión monedas
- **WHEN** un usuario con `exchange-rates.view` (o Super Admin) abre
  Configuración → Conversión monedas
- **THEN** ve el Index ServerTable de exchange-rates

#### Scenario: Sin permiso view
- **WHEN** un usuario sin `exchange-rates.view` y sin Super Admin fuerza la URL
  del Index
- **THEN** recibe 403

#### Scenario: Alta de ambos sentidos
- **WHEN** el actor guarda para fecha D el par EUR/USD con rate EUR→USD y
  USD→EUR
- **THEN** existen dos filas (EUR→USD y USD→EUR) con `rate_date` = D

#### Scenario: Un solo sentido
- **WHEN** el actor guarda solo el rate A→B (B→A vacío) para fecha D
- **THEN** existe la fila A→B y no se crea B→A

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

### Requirement: Vigencia = última cotización ≤ fecha de operación
Para un par dirigido y una fecha de operación D, el sistema SHALL resolver la
cotización de mayor `rate_date` ≤ D. Si no existe ninguna ≤ D, SHALL devolver
ausencia de cotización. SHALL NOT inventar el inverso a partir del sentido
contrario.

#### Scenario: Sin cotización el día D pero sí antes
- **WHEN** existe EUR→USD solo en fecha D−3 y se resuelve para operación en D
- **THEN** se obtiene el rate de D−3

#### Scenario: Tras anotar una cotización nueva
- **WHEN** se guarda EUR→USD en fecha D y antes solo había D−3
- **THEN** la vigente para D es la de D

#### Scenario: Sin historial ≤ D
- **WHEN** no existe ninguna fila EUR→USD con rate_date ≤ D
- **THEN** la resolución indica ausencia de cotización

### Requirement: Snapshot futuro en operaciones (norma)
Cuando existan documentos comerciales con FX, la operación SHALL guardar el
rate aplicado en el momento (snapshot), no depender solo de la consulta viva
al maestro. Este capability SHALL documentar la norma; SHALL NOT añadir
columnas en pedidos/facturas en este change.

#### Scenario: Norma citable
- **WHEN** se abra un change futuro de pedidos/facturas con FX
- **THEN** el contrato ya fija snapshot del rate como el precio de producto
