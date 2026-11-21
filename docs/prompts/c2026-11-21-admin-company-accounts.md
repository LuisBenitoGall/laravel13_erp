<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-21-admin-company-accounts
Rule: 10-architecture
Summary: Vínculo empresa↔Account vía `company_accounts` (≤1 activa),
  caducidad con 403+CTA, alta Company con Account default, histórico y
  renovación en Cuenta → Mi cuenta.
Goals:
- Tabla/modelo `company_accounts` (como v1 en concepto): company_id,
  account_id, start_date, end_date, price, payment_date, status; soft
  delete; **sin** `guardian` (fuera).
- Enums limpios Pending / Active / Expired / Cancelled (nombres exactos
  a fijar por 10).
- Invariante: cada empresa **siempre** una — y no más — cuenta Active.
- Al crear Company: asignar Account **default** del catálogo (change
  accounts ya hecho).
- Job/caducidad: al caducar, la empresa y sus usuarios pierden acceso →
  **403** con mensaje explicativo y CTA hacia el formulario de
  renovación (en Mi cuenta).
- Vista con tabla e **histórico** de las cuentas de la empresa en
  **Cuenta → Mi cuenta** (sustituye/ocupa el placeholder actual de Mi
  cuenta SaaS).
- Renovación desde **Mi cuenta** (opción B); la máscara
  `/admin/accounts/renewal` se deja o redirige (10 decide el mínimo).
- Quién ve y actúa: usuarios con acceso a **Mi cuenta**
  (`profile.view` / Functionality actual de Mi cuenta — 10 alinea
  permisos sin inventar rol nuevo salvo que el kernel lo exija).
NonGoals:
- Campo `guardian` de v1.
- Facturación SaaS Cashier/Stripe.
- Cambios al catálogo `Account` salvo consumir el default.
- Confundir con Module `cuenta` naming vs AccountingAccount (ya en
  glosario).
Skills:
- openspec-propose
- tenancy-rbac
- v1-reference
- inertia-react-development
Notes:
- Origen: `/opsx-explore` tras `c2026-11-21-admin-accounts` archivado.
  Decisiones: (1) alcance **A** completo; (2) renovación **B** desde Mi
  cuenta; (3) actores = quienes tienen acceso a Mi cuenta.
- UI destino: menú Cuenta → Mi cuenta (screenshot: placeholder
  «próximamente» → pasa a histórico + renovación).
- R-FE-04 en columnas numéricas/fechas del ServerTable del histórico.
- Ficha Admin + playbook. Idioma: español.
- Tras brief: usuario lanza 10-architecture en sesión nueva.


## ADDED Requirements

### Requirement: company_accounts con una Active por empresa
El sistema SHALL persistir el vínculo empresa–Account en
`company_accounts` y SHALL garantizar como máximo una fila Active por
empresa. Al crear una Company SHALL crear el vínculo inicial usando la
Account default del catálogo.

#### Scenario: Alta Company con default
- **WHEN** se crea una Company y existe Account default
- **THEN** queda un company_accounts Active ligado a esa Account

#### Scenario: No dos Active
- **WHEN** se intenta activar una segunda cuenta para la misma empresa
- **THEN** el sistema lo rechaza o desactiva la anterior según el
  diseño de 10 (invariante: ≤1 Active)

### Requirement: Caducidad bloquea acceso con 403 y CTA
Cuando la cuenta Active de la empresa ha caducado (o pasa a Expired),
el sistema SHALL impedir el uso del ERP de esa empresa con **403**,
mensaje explicativo y CTA hacia la renovación en Mi cuenta.

#### Scenario: Acceso con cuenta caducada
- **WHEN** un usuario vinculado intenta operar con una company cuya
  cuenta Active ha caducado
- **THEN** recibe 403 con mensaje y CTA a renovación en Mi cuenta

### Requirement: Histórico y renovación en Cuenta / Mi cuenta
El sistema SHALL exponer bajo Cuenta → Mi cuenta la tabla/histórico de
`company_accounts` de la empresa en sesión y el flujo de renovación
para usuarios con acceso a Mi cuenta.

#### Scenario: Ver histórico en Mi cuenta
- **WHEN** un usuario con acceso a Mi cuenta abre Cuenta → Mi cuenta
- **THEN** ve el histórico de cuentas de la empresa activa

#### Scenario: Renovar desde Mi cuenta
- **WHEN** un usuario con acceso a Mi cuenta completa la renovación
- **THEN** queda registrada la nueva relación según reglas de ≤1 Active


## MODIFIED Requirements

_(ninguno obligatorio — 10 puede delta menú `cuenta` / profile /
contents de Mi cuenta placeholder)_
