<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-21-admin-accounts
Rule: 10-architecture
Summary: Catálogo global Account (Cuentas) bajo Configuración — plantillas
  de relación empresa↔ERP (duración/coste) con una cuenta default;
  máscara placeholder de renovación. Sin company_accounts aún.
Goals:
- Functionality `accounts` (o slug que fije 10) bajo Module Configuración;
  menú Configuración → Cuentas.
- Modelo `Account` (catálogo global, sin BelongsToCompany): name, slug,
  description, rate (coste), duration (días), status Active/Inactive,
  soft delete; campos alineados a v1 salvo lo excluido.
- Marcar **una** cuenta default del catálogo (para altas de empresa /
  asignación futura).
- CRUD Admin con ServerTable + políticas/permisos `accounts.*` (patrón
  catálogos Configuración).
- Máscara placeholder del formulario de renovación de cuenta (sin lógica
  de caducidad ni CTA 403 real todavía; “solo la máscara”).
- Enums PHP limpios (status Account; estados futuros de company_accounts
  documentados como Pending/Active/Expired/Cancelled para el change
  siguiente, sin implementar la tabla aún si 10 lo prefiere fuera).
NonGoals:
- Tabla/flujo completo `company_accounts` (vínculo empresa, ≤1 activa,
  job de caducidad, 403 real con mensaje + CTA).
- Vista histórico de cuentas de una empresa (después).
- Campo `guardian` de v1 (fuera por ahora).
- Confundir con Module menú `cuenta` (Mi cuenta / Mis módulos) ni con
  `AccountingAccount`.
- Facturación SaaS Cashier/Stripe.
Skills:
- openspec-propose
- tenancy-rbac
- v1-reference
- inertia-react-development
Notes:
- Origen: `/opsx-explore`. Decisiones: (1) alcance **A** catálogo;
  (2) caducidad futura = 403 + mensaje + CTA renovación — **ahora solo
  máscara**; (3) sí cuenta default; (4) guardian fuera; (5) enums OK.
- v1 ref: `Account` + Configuración/Cuentas; `company_accounts` queda
  para change posterior.
- Naming: glosario `Account` ≠ Module `cuenta` ≠ AccountingAccount.
- Ficha módulo Admin + playbook /new-module. Idioma: español.
- Tras brief: usuario lanza 10-architecture en sesión nueva.


## ADDED Requirements

### Requirement: Catálogo Account bajo Configuración
El sistema SHALL exponer un catálogo global de cuentas (`Account`) bajo
Configuración → Cuentas, con listado ServerTable y alta/edición de name,
description, rate, duration (días) y status. SHALL persistir slug. SHALL
soportar soft delete. SHALL NOT usar BelongsToCompany en Account.

#### Scenario: SA abre Cuentas
- **WHEN** un Super Admin con permiso de Cuentas entra a la ruta del
  catálogo
- **THEN** ve el listado ServerTable de Accounts

#### Scenario: Crear Account
- **WHEN** el actor autorizado crea una cuenta con name, rate y duration
- **THEN** queda persistida en el catálogo con slug y status

### Requirement: Cuenta default del catálogo
El sistema SHALL permitir marcar exactamente una Account del catálogo
como default (para uso en altas/asignaciones futuras).

#### Scenario: Marcar default
- **WHEN** el actor marca una Account como default
- **THEN** esa queda como la única default del catálogo

### Requirement: Máscara de renovación de cuenta
El sistema SHALL exponer una máscara/placeholder del formulario de
renovación de cuenta (sin asignar aún company_accounts ni aplicar
bloqueo por caducidad).

#### Scenario: Abrir máscara renovación
- **WHEN** el actor autorizado abre la ruta de renovación de cuenta
- **THEN** ve la máscara placeholder (próximamente / sin persistir
  vínculo)


## MODIFIED Requirements

_(ninguno obligatorio — 10 enlaza menú/ficha Admin / glosario)_
