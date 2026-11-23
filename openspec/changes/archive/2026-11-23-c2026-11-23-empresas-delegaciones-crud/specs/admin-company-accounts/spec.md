# Spec Delta — admin-company-accounts

## MODIFIED Requirements

### Requirement: company_accounts con una Active por empresa
El sistema SHALL persistir `company_accounts` (company, Account, fechas, price,
payment_date, status) con soft delete y SHALL NOT incluir guardian. SHALL
garantizar como máximo una fila Active por empresa. Al crear una Company por el
flujo genérico (Mis empresas / CreateCompany) SHALL crear el vínculo inicial
Active usando la Account default del catálogo. El alta de **Delegación** SHALL
NOT crear `company_accounts` ni llamar a la asignación de Account default.

#### Scenario: Alta Company con default
- **WHEN** se crea una Company por el flujo genérico y existe Account default
- **THEN** queda un company_accounts Active ligado a esa Account

#### Scenario: Alta Delegación sin cuenta
- **WHEN** se crea una Delegación
- **THEN** no se crea fila en company_accounts para esa Company

#### Scenario: No dos Active
- **WHEN** se intenta dejar dos company_accounts Active en la misma empresa
- **THEN** el sistema rechaza o cancela la anterior de modo que quede como
  máximo una Active

#### Scenario: Aislamiento tenant
- **WHEN** un actor de company A lista el histórico
- **THEN** no ve company_accounts de company B
