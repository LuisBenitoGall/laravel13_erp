<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-09-company-roles
Rule: 10-architecture
Summary: Introducir roles Spatie por empresa (Teams): al crear cada Company se generan
obligatoriamente los roles `manager` y `guest` con `company_id` de esa empresa; el
creador queda como manager; el manager recibe por sincronización los permisos de los
módulos Basic/Optional autorizados (nunca AdminOnly); el guest nace sin permisos.
Goals:
- Al crear una Company: crear roles `manager` y `guest` (nombres en inglés normativo;
  UI en español vía i18n) con `company_id` = esa empresa.
- El usuario que crea la Company queda vinculado (`user_companies`) y con rol `manager`.
- Rol `guest`: cero permisos al nacer; será el manager quien asigne permisos después
  (sin UI de creación de roles custom en este change).
- Rol `manager`: máximo dentro de su empresa. Sincronizar en `role_has_permissions` todos
  los permisos de las Functionalities de módulos Basic y Optional a los que la empresa
  esté **autorizada** (Capa 3). No incluir módulos AdminOnly / exclusivos SuperAdmin.
- Disparadores de sincronización (acordado):
  - Al **autorizar** un módulo (y al auto-autorizar Basic): añadir al rol `manager` los
    permisos de sus Functionalities.
  - Al **desautorizar** / retirar autorización: quitar del `manager` esos permisos.
  - Encender/apagar en `company_modules` (Capa 2) **no** altera los permisos del rol;
    solo afecta menú/acceso operativo ya existente.
- Seed/demo: companies de trabajo con manager asignado y permisos sincronizados según
  autorizaciones actuales.
NonGoals:
- UI para que el manager cree roles adicionales custom (change posterior).
- Registro público / onboarding self-service / consola Platform.
- Cambiar el modelo de tres capas de módulos ni el bypass de Super Admin
  (permisos globales; datos solo por vínculo a company — ya vigente).
- Red comercial (`customer_providers`) u otros maestros Partners.
Skills:
- laravel-patterns
- laravel-specialist
- php-pro
Notes:
- Prerrequisito: `c2026-10-09-company-tenancy-modules` archivado (Company, user_companies,
  company_module_authorizations, company_modules, SelectCompany, MenuBuilder endurecido).
- Referencia v1: roles `{company_id}/manager` y `{company_id}/basico`; en v2 nombres
  estables `manager`/`guest` + `company_id` vía Spatie Teams (no prefijo en el name).
- Super Admin sigue con `company_id` NULL y Gate::before; no necesita rol manager para
  bypass de permisos, pero sigue sujeto a pertenencia para datos.
- Asignación posterior de permisos al `guest` (o a users) por el manager: si hace falta
  una UI mínima de “asignar rol manager/guest a un user de la company”, 10-architecture
  la delimita; crear roles nuevos queda fuera.


## ADDED Requirements

### Requirement: Roles manager y guest por empresa
El sistema SHALL, al crear cada Company, crear los roles Spatie `manager` y `guest`
asociados a esa empresa (`company_id`), asignar `manager` al usuario creador, y dejar
`guest` sin permisos.

#### Scenario: Alta de company genera roles obligatorios
- **WHEN** un usuario crea una Company nueva
- **THEN** existen los roles `manager` y `guest` con `company_id` de esa company, el
  creador está en `user_companies` y tiene el rol `manager`

#### Scenario: Guest sin permisos
- **WHEN** se crea el rol `guest` de una company
- **THEN** no tiene ninguna entrada en `role_has_permissions`

---

### Requirement: Sincronización de permisos del manager según autorización de módulos
El sistema SHALL mantener el rol `manager` de cada company con los permisos de las
Functionalities de módulos Basic y Optional autorizados para esa company, excluyendo
AdminOnly; la sincronización ocurre al autorizar/desautorizar, no al encender/apagar.

#### Scenario: Autorizar módulo Optional añade permisos al manager
- **WHEN** Super Admin autoriza un Module Optional (Active) para una company
- **THEN** el rol `manager` de esa company obtiene los permisos de las Functionalities
  de ese módulo

#### Scenario: Desautorizar módulo quita permisos del manager
- **WHEN** se retira la autorización de un Module para una company
- **THEN** esos permisos dejan de estar en el rol `manager` de esa company

#### Scenario: AdminOnly no entra en el manager
- **WHEN** un Module es AdminOnly aunque estuviera autorizado por error de operación
- **THEN** el sync del manager no le asigna permisos de ese módulo (solo Super Admin
  opera AdminOnly vía bypass)

#### Scenario: Encender o apagar no cambia role_has_permissions
- **WHEN** se enciende o apaga un módulo autorizado en `company_modules`
- **THEN** los permisos del rol `manager` no se modifican por ese hecho


## MODIFIED Requirements

No aplica — no se modifica el contrato de menú/tres capas más allá de consumir la
autorización ya existente para alimentar el sync del manager.
