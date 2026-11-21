# Spec Delta

## Purpose

Vínculo SaaS empresa–Account (`company_accounts`): una Active por
empresa, caducidad con bloqueo 403, alta con Account default, e
histórico/renovación en Cuenta → Mi cuenta.

## ADDED Requirements

### Requirement: company_accounts con una Active por empresa
El sistema SHALL persistir `company_accounts` (company, Account,
fechas, price, payment_date, status) con soft delete y SHALL NOT
incluir guardian. SHALL garantizar como máximo una fila Active por
empresa. Al crear una Company SHALL crear el vínculo inicial Active
usando la Account default del catálogo.

#### Scenario: Alta Company con default
- **WHEN** se crea una Company y existe Account default
- **THEN** queda un company_accounts Active ligado a esa Account

#### Scenario: No dos Active
- **WHEN** se intenta dejar dos company_accounts Active en la misma
  empresa
- **THEN** el sistema rechaza o cancela la anterior de modo que quede
  como máximo una Active

#### Scenario: Aislamiento tenant
- **WHEN** un actor de company A lista el histórico
- **THEN** no ve company_accounts de company B

### Requirement: Caducidad bloquea acceso con 403 y CTA
Cuando la company de sesión no tiene cuenta Active vigente, el sistema
SHALL responder 403 en rutas ERP protegidas, con mensaje explicativo y
CTA hacia Mi cuenta para renovar. SHALL permitir el acceso a Mi cuenta
(y renovación) para que el actor pueda recuperar el acceso.

#### Scenario: Acceso con cuenta caducada
- **WHEN** un usuario vinculado intenta operar el ERP con una company
  cuya cuenta Active ha caducado
- **THEN** recibe 403 con mensaje y CTA a renovación en Mi cuenta

#### Scenario: Mi cuenta accesible sin Active vigente
- **WHEN** la cuenta de la company está caducada y el actor abre Mi
  cuenta
- **THEN** puede ver el histórico y el flujo de renovación (no 403 de
  gate en esa ruta)

### Requirement: Histórico y renovación en Cuenta / Mi cuenta
El sistema SHALL exponer bajo Cuenta → Mi cuenta el histórico
ServerTable de company_accounts de la empresa en sesión y permitir
renovar la cuenta a usuarios con acceso a Mi cuenta. La renovación
SHALL crear/activar un nuevo vínculo respetando ≤1 Active.

#### Scenario: Ver histórico en Mi cuenta
- **WHEN** un usuario con acceso a Mi cuenta abre Cuenta → Mi cuenta
- **THEN** ve el histórico de cuentas de la empresa activa

#### Scenario: Renovar desde Mi cuenta
- **WHEN** un usuario con acceso a Mi cuenta completa la renovación
- **THEN** queda registrada la nueva relación y como máximo una Active
