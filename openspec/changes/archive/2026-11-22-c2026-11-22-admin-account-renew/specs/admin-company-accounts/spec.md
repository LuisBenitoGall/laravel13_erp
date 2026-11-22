# Spec Delta

## ADDED Requirements

### Requirement: Grid catálogo de Accounts reutilizable
El sistema SHALL exponer un componente reutilizable de selección/grid
de Accounts del catálogo (Active) usable en la renovación admin y
previsto para el alta de empresa en No Admin.

#### Scenario: Selección en renovación
- **WHEN** el actor está en la vista de renovación
- **THEN** elige entre Accounts Active mediante el componente grid/
  selector reutilizable


## MODIFIED Requirements

### Requirement: Caducidad bloquea acceso con 403 y CTA
Cuando la company de sesión no tiene cuenta Active vigente, el sistema
SHALL responder 403 en rutas ERP protegidas, con mensaje explicativo y
CTA hacia la ruta admin de renovación de cuenta. SHALL permitir el
acceso al histórico y a la renovación bajo `/admin` para que el actor
pueda recuperar el acceso. SHALL NOT permitir navegar el resto del ERP
sin Active vigente.

#### Scenario: Acceso con cuenta caducada
- **WHEN** un usuario vinculado intenta operar el ERP con una company
  cuya cuenta Active ha caducado
- **THEN** recibe 403 con mensaje y CTA a la renovación admin

#### Scenario: Mi cuenta accesible sin Active vigente
- **WHEN** la cuenta de la company está caducada y el actor abre el
  histórico o la renovación admin
- **THEN** puede ver el histórico y el flujo de renovación (no 403 de
  gate en esas rutas)

#### Scenario: Acceso sin ninguna cuenta
- **WHEN** la empresa no tiene company_accounts Active vigente (ausente
  o caducada) y el actor intenta navegar el ERP
- **THEN** ve Acceso bloqueado con CTA a renovar/crear cuenta

### Requirement: Histórico y renovación en Cuenta / Mi cuenta
El sistema SHALL exponer bajo Cuenta → Mi cuenta el histórico
ServerTable de company_accounts de la empresa en sesión en una ruta
admin (`/admin/account` o equivalente), con CTA hacia la renovación/
upgrade en ruta admin dedicada de semántica obvia
(`/admin/account-renew` o equivalente). La renovación SHALL usar el
grid/selector reutilizable de Accounts Active, SHALL estar disponible
también con Active vigente (upgrade) y SHALL crear/activar un nuevo
vínculo respetando ≤1 Active.

#### Scenario: Ver histórico en Mi cuenta
- **WHEN** un usuario con acceso a Mi cuenta abre Cuenta → Mi cuenta
- **THEN** ve el histórico de cuentas de la empresa activa y un CTA
  para renovar o mejorar

#### Scenario: Renovar desde Mi cuenta
- **WHEN** un usuario con acceso a Mi cuenta completa la renovación
  (o upgrade con Active vigente) desde la ruta admin de renovación
- **THEN** queda registrada la nueva relación y como máximo una Active

#### Scenario: Acceso a renovación sin Active vigente
- **WHEN** la empresa no tiene cuenta Active vigente y el actor sigue
  el CTA desde Acceso bloqueado
- **THEN** puede abrir la renovación y completar alta/renovación
