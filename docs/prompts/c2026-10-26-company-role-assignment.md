<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-26-company-role-assignment
Rule: 10-architecture
Summary: Cerrar el hueco de asignación User↔rol en el tenant: extender la Action y la UI
de miembros de company para asignar cualquier rol local (manager, guest y custom), no
solo manager|guest. Primer change del cierre de restos de Fase 0 (luego Form kit MVP y
consola Platform MVP).
Goals:
- Extender `AssignCompanyRole` (hoy solo `manager`|`guest`) para asignar también roles
  custom locales de la company (`company_id` = esa empresa).
- Extender la UI existente `Partners/Companies/Members` (select por miembro) para listar
  y asignar todos los roles locales de la company, no solo manager/guest.
- Mantener el modelo actual de **un rol de empresa a la vez** por usuario en esa company
  (al asignar uno se deja el anterior de esa company), coherente con el select actual.
- Quién asigna: el mismo gate ya vigente (`CompanyRoleGate` / manager + Super Admin).
NonGoals:
- CRUD de roles (ya en company-custom-roles / platform-roles).
- Asignación de roles de plataforma / Super Admin desde esta UI.
- Invitar usuarios nuevos / alta de miembros (solo asignar rol a vinculados existentes).
- Form kit MVP y consola Platform MVP (changes siguientes del cierre Fase 0).
- Cashier, cuotas, RGPD, “ver todas las companies” sin vínculo.
Skills:
- tenancy-rbac
- laravel-specialist
- laravel-patterns
Notes:
- Cierre Fase 0 acordado en orden: (A) este change → (B) form-kit-mvp (Field +
  Input/Textarea/Number/Select/Date/Checkbox/Switch; **sin** Wysiwyg/Dropzone/LangTabs
  en ese MVP) → (C) platform-console-mvp (listado tenants + elegibilidad módulos; sin
  Stripe).
- Prerrequisito: company-custom-roles archivado (roles custom + workspace/company-roles).
- UI y ruta ya existen: `CompanyMemberController` + `Members.tsx` +
  `PUT .../members/{user}/role`. Ampliar opciones y validación; no inventar segunda
  pantalla salvo que 10 lo justifique.
- Action actual lanza si el rol no es manager|guest; hay que aceptar cualquier rol local
  de la company (nombre Spatie + team id).
- i18n snake_case; tests Feature (asignar custom, rechazo rol de otra company, authz).


## ADDED Requirements

### Requirement: Asignación de roles locales de company a miembros
El sistema SHALL permitir a un actor autorizado (manager de la company o Super Admin vía
gate vigente) asignar a un usuario ya vinculado a la company cualquiera de los roles
Spatie locales de esa company (incluidos custom), no solo `manager` y `guest`. SHALL
mantener un único rol de empresa activo por usuario en esa company al reasignar.

#### Scenario: Asignar rol custom a miembro
- **WHEN** un manager elige un rol custom local de su company para un miembro vinculado
- **THEN** ese usuario tiene ese rol en el team de la company y deja de tener el rol
  local anterior de esa company (si lo había)

#### Scenario: Seguir pudiendo asignar manager y guest
- **WHEN** un manager asigna `manager` o `guest` a un miembro vinculado
- **THEN** la asignación funciona como hasta ahora (con el mismo gate)

#### Scenario: No asignar rol de otra company ni plataforma
- **WHEN** se intenta asignar un rol cuyo `company_id` no es la company del miembro (o es
  un rol de plataforma)
- **THEN** la operación se rechaza

#### Scenario: UI de miembros muestra todos los roles locales
- **WHEN** un actor autorizado abre la pantalla de miembros de la company
- **THEN** el selector de rol incluye manager, guest y los roles custom locales de esa
  company


## MODIFIED Requirements

### Requirement: Roles manager y guest por empresa
La asignación a usuarios vinculados deja de estar limitada a `manager`|`guest`: SHALL
permitir asignar cualquier rol local de la company; la materialización y el sync del
manager no cambian.

#### Scenario: Asignar rol local distinto de manager/guest
- **WHEN** un actor autorizado asigna un rol custom local a un usuario vinculado
- **THEN** el usuario queda con ese rol en la company y no se crea un rol nuevo
