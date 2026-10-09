## ADDED Requirements

### Requirement: Company shell y pertenencia usuario↔empresa
El sistema SHALL mantener empresas (`Company`) como único tipo de actor organizativo
(todas iguales), con pertenencia N:M usuario↔empresa, y SHALL impedir el acceso al ERP
sin al menos una vinculación a empresa activa. Super Admin bypasa permisos/módulos pero
solo ve datos de las companies a las que está vinculado.

#### Scenario: Login con una sola empresa
- **WHEN** un usuario con acceso al ERP está vinculado a exactamente una empresa activa
- **THEN** la sesión de trabajo se establece automáticamente en esa empresa

#### Scenario: Login con varias empresas
- **WHEN** un usuario con acceso al ERP está vinculado a más de una empresa activa
- **THEN** debe elegirse una empresa en un modal bloqueante antes de usar el ERP

#### Scenario: Usuario sin empresa
- **WHEN** un usuario existe en `users` pero no está vinculado a ninguna empresa
- **THEN** no puede acceder al ERP (puede existir como contacto/lead fuera del acceso)

#### Scenario: Super Admin y datos por empresa
- **WHEN** un Super Admin vinculado solo a un subconjunto de companies opera en el ERP
- **THEN** bypasa permisos/módulos/funcionalidades pero solo ve datos de las companies a
  las que está vinculado

#### Scenario: Cambio de empresa solo si pertenece
- **WHEN** un usuario intenta fijar la empresa de sesión a una company a la que no está
  vinculado
- **THEN** la operación se rechaza y no cambia `CurrentCompany`
