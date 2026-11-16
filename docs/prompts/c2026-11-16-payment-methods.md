<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-16-payment-methods
Rule: 10-architecture
Summary: Catálogo global Admin `PaymentMethod` (name traducible + status)
  con CRUD bajo menú Configuración. Sin pivot por company ni campos
  contables/legacy en este change.
Goals:
- Entidad global `PaymentMethod` / `payment_methods` (sin `company_id`).
- Campos MVP: `name` traducible + `status`.
- CRUD completo (Index ServerTable + Create/Edit/Delete) bajo
  Configuración (patrón catálogos SA).
- Dirección acordada a futuro (no implementar): pivot
  `company_payment_methods` (selección por empresa +
  `accounting_account_id`) tras existir `accounting_accounts`.
NonGoals:
- Tabla `company_payment_methods` / selección por empresa.
- `accounting_account_id` en el catálogo o en pivot.
- Campo `code` y flags v1 (`with_effect`, `is_basic`, destiny, type,
  print, banco…).
- Import legacy por código.
Skills:
- openspec-propose
- new-module
- tenancy-rbac
- v1-reference
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore`. Debate: global vs por company → B (global +
  pivot rico) como dirección; este change solo el catálogo.
- Motivo del pivot (futuro): filtrar opciones UX por empresa + cuenta
  contable tenant; no en este change.
- v1: `payment_methods` ya es global; olor SaaS = cuenta en la fila
  global → corregir en pivot futuro.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Catálogo global PaymentMethod
El sistema SHALL persistir métodos de pago como catálogo global Admin
(`PaymentMethod`, sin `company_id`) con `name` traducible y `status`.
SHALL NOT exigir `code` en este change.

#### Scenario: Campos MVP
- **WHEN** un actor autorizado crea o edita un método de pago
- **THEN** puede informar name multi-idioma y status; no ve code ni
  campos contables/legacy

### Requirement: CRUD bajo Configuración
El sistema SHALL exponer CRUD completo de PaymentMethod bajo el menú
Configuración (Functionality + Index `<ServerTable>`, Create, Edit,
Delete), con Policy y permisos Spatie canónicos.

#### Scenario: Entrada Configuración
- **WHEN** Super Admin (o actor con capas/permisos de Configuración)
  obtiene el menú
- **THEN** ve Métodos de pago (o label acordado) bajo Configuración y
  puede abrir el Index ServerTable


## MODIFIED Requirements

### Requirement: Ítem Métodos de pago en Configuración
(El Module `configuracion` con catálogos SA.) SHALL añadir el subítem de
menú hacia el Index de payment-methods, sin sustituir los catálogos
existentes.

#### Scenario: Menú Configuración
- **WHEN** el actor ve Configuración
- **THEN** tiene Métodos de pago como entrada distinta de Bancos,
  Sectores, Unidades, etc.
