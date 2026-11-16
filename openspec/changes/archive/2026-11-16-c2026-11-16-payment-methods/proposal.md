# Proposal

## Why

Hace falta el catálogo global de **métodos de pago** en Configuración
(base para facturación/tesorería). El pivot por company y la cuenta
contable quedan para después de `accounting_accounts`.

## What Changes

- Entidad global `PaymentMethod` (`name` JSON traducible + `status`;
  soft delete; sin `company_id`; sin `code`).
- CRUD Configuración → Métodos de pago (ServerTable + modales LangTabs;
  Functionality `payment-methods` / `payment-methods.*`).
- Menú / Realign / glosario / Admin.md / module-map.
- **Dirección futura (no implementar):** `company_payment_methods`
  (selección + `accounting_account_id`).

## Capabilities

### New Capabilities

- `admin-payment-methods-catalog`: catálogo global PaymentMethod + CRUD
  Configuración.

### Modified Capabilities

- `menu-core-pillars`: ítem Métodos de pago bajo `configuracion`.

## Impact

- Migración/modelo/Policy/Actions; rutas `/admin/payment-methods`.
- UI Index + modales; seed Functionality bajo Configuración.
- Tests Feature CRUD + authz (global: R-TEN-05 N/A en entidad).
