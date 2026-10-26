## Purpose

Consola operador MVP para Super Admin: listado de tenants del perímetro vinculado y flujo a elegibilidad de módulos; sin facturación SaaS.

## Requirements

### Requirement: Consola plataforma MVP (operador Super Admin)
El sistema SHALL exponer una experiencia de operador para Super Admin que liste las
companies a las que está vinculado y permita navegar a la gestión de elegibilidad de
módulos de esas companies. SHALL NOT incluir facturación SaaS ni cuotas. SHALL NOT
listar companies ajenas al vínculo `user_companies` del operador.

#### Scenario: Listado de tenants del perímetro
- **WHEN** Super Admin abre el listado de tenants/companies de la consola plataforma
- **THEN** solo ve companies vinculadas a su usuario

#### Scenario: Acceso a elegibilidad desde el perímetro
- **WHEN** Super Admin elige una company de su perímetro para gestionar autorizaciones
  de módulo
- **THEN** puede autorizar o revocar con las Actions vigentes de las tres capas

#### Scenario: Company no vinculada inaccesible
- **WHEN** Super Admin intenta autorizar/revocar (o abrir elegibilidad) de una company
  a la que no está vinculado
- **THEN** la operación se rechaza (403/404)

#### Scenario: Sin billing en el MVP
- **WHEN** se revisa el entregable de este change
- **THEN** no hay Cashier/Stripe ni pantallas de plan de pago
