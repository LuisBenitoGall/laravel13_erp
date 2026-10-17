## ADDED Requirements

### Requirement: Registro en dos pasos (User luego Company)
El sistema SHALL permitir el alta self-service en dos pasos: primero User, después
Company; sin Company activa vinculada no hay acceso al ERP. Un usuario autenticado sin
company SHALL permanecer en sesión y ser dirigido al alta de Company (incluido tras
login), no desloguearse únicamente por carecer de company.

#### Scenario: Registro de usuario
- **WHEN** un visitante completa email único, password con confirmación y name, y acepta
  aviso y términos
- **THEN** se crea el User, inicia sesión y se le conduce al paso de crear Company (sin
  entrar al ERP)

#### Scenario: Usuario sin company tras login
- **WHEN** un User autenticado no tiene ninguna company activa vinculada (tras login o
  tras el paso 1)
- **THEN** no accede al ERP y se le muestra el flujo bloqueante de alta de Company

#### Scenario: Alta de company en onboarding
- **WHEN** el usuario envía nif (único), al menos name o tradename, opcionalmente logo
  válido, y los datos pasan validación
- **THEN** se crea la Company en status Active, con slug autogenerado, queda como manager
  vinculado, y entra al ERP con esa empresa en sesión

#### Scenario: NIF duplicado
- **WHEN** el nif indicado ya existe en companies (no soft-deleted)
- **THEN** se rechaza el alta con error de validación

#### Scenario: Logo opcional con formatos permitidos
- **WHEN** se adjunta un logo
- **THEN** solo se aceptan jpg, webp, png, gif o svg; si no se adjunta, la company se
  crea igual
