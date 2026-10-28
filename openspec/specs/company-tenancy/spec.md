## Purpose

Company shell, pertenencia `user_companies` (incluido Super Admin), sesión/modal post-login, listados de Company por vínculo, y flujo SA sin company → alta de empresa / post-login a Dashboard.

## Requirements

### Requirement: Company shell y pertenencia usuario↔empresa
El sistema SHALL mantener empresas (`Company`) como único tipo de actor organizativo
(todas iguales), con pertenencia N:M usuario↔empresa, y SHALL impedir el acceso al ERP
sin al menos una vinculación a empresa activa — **incluido Super Admin**. Super Admin
bypasa permisos/módulos (Gate) y, en AdminOnly, el middleware de activación por company
(véase menu-visibility), pero solo ve datos de las companies a las que está vinculado.
SHALL aplicar ese perímetro también a los listados/tablas de `Company` (incluidas las
vistas de operador).

#### Scenario: Login con una sola empresa
- **WHEN** un usuario con acceso al ERP está vinculado a exactamente una empresa activa
- **THEN** la sesión de trabajo se establece automáticamente en esa empresa

#### Scenario: Login con varias empresas
- **WHEN** un usuario con acceso al ERP está vinculado a más de una empresa activa
- **THEN** debe elegirse una empresa en un modal bloqueante antes de usar el ERP

#### Scenario: Usuario sin empresa
- **WHEN** un usuario (incluido Super Admin) existe en `users` pero no está vinculado a
  ninguna empresa activa
- **THEN** no puede acceder al ERP y se le dirige al flujo de alta de Company (puede
  existir como contacto/lead fuera del acceso ERP)

#### Scenario: Super Admin y datos por empresa
- **WHEN** un Super Admin vinculado solo a un subconjunto de companies opera en el ERP
- **THEN** bypasa permisos/módulos/funcionalidades pero solo ve datos de las companies a
  las que está vinculado

#### Scenario: Listado de companies respeta vínculo
- **WHEN** un Super Admin (u otro usuario) solicita el listado/table de companies
- **THEN** solo obtiene companies presentes en su `user_companies`

#### Scenario: Cambio de empresa solo si pertenece
- **WHEN** un usuario intenta fijar la empresa de sesión a una company a la que no está
  vinculado
- **THEN** la operación se rechaza y no cambia `CurrentCompany`

### Requirement: Super Admin con el mismo flujo de pertenencia a company
El sistema SHALL exigir a Super Admin, igual que al resto de usuarios, ≥1 company activa
vinculada para usar el ERP. SHALL, si un Super Admin autenticado no tiene company activa
vinculada, redirigirlo al flujo de alta/creación de empresa (onboarding existente), no a
un atajo AdminOnly. SHALL hacer que el post-login y el post-selección de empresa de
Super Admin no fuerzan un destino AdminOnly distinto (p. ej. `admin.modules.index`) que
provoque 403 por módulo inactivo; el destino SHALL alinearse al del resto (Dashboard tras
tener company de sesión).

#### Scenario: SA sin empresa → crear empresa
- **WHEN** un Super Admin inicia sesión sin companies activas vinculadas
- **THEN** es redirigido a donde puede crear una company (onboarding/alta existente)

#### Scenario: SA con empresa → flujo normal
- **WHEN** un Super Admin con ≥1 company activa entra al ERP
- **THEN** sigue selección/sesión de empresa como el resto y aterriza en Dashboard (o
  destino común equivalente) sin 403 de módulo admin por el solo hecho de ser SA
