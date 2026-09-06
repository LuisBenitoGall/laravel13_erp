## ADDED Requirements

### Requirement: Kernel de tenancy y soporte transversal
El sistema SHALL proporcionar los mecanismos base de tenancy y auditoría
(`BelongsToCompany`, `Auditable`, `CurrentCompany`, `NumberingService`,
`TableRequest`/`TableBuilder`) como piezas reutilizables según
`templates-backend.md` §12, verificadas con modelos/tablas de prueba, sin depender de
ninguna entidad de negocio real. Además SHALL registrar el middleware
`EnsureModuleIsActive` (alias de ruta `module:`) listo para el change de Admin.

#### Scenario: Aislamiento de tenant verificado con fixture
- **WHEN** un test crea dos registros de un modelo de prueba con `BelongsToCompany`, cada
  uno perteneciente a una "empresa" distinta (fixture)
- **THEN** una consulta desde el contexto de una de las empresas solo devuelve sus
  propios registros

#### Scenario: Numeración concurrente sin duplicados
- **WHEN** dos procesos solicitan a la vez el siguiente número de un tipo de documento de
  prueba vía `NumberingService`
- **THEN** cada uno recibe un número distinto y consecutivo, sin colisiones (bloqueo
  `FOR UPDATE`)

#### Scenario: Helper de test fija el tenant
- **WHEN** un test Pest usa `withCurrentCompany($company)`
- **THEN** `CurrentCompany` resuelve ese tenant durante el test y el global scope de
  `BelongsToCompany` filtra en consecuencia
