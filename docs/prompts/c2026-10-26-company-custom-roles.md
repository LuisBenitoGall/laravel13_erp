<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-26-company-custom-roles
Rule: 10-architecture
Summary: CRUD de roles custom por empresa (manager del tenant): listar/crear/editar/borrar
roles locales con matriz de permisos acotada a módulos autorizados y activos (sin
AdminOnly). Protege `manager`/`guest` locales. Define el contrato de verificación de
acceso tenant que cada módulo futuro deberá rellenar.
Goals:
- UI tenant (manager): CRUD de roles de la company activa (`company_id` = CurrentCompany).
- Matriz de permisos: solo Functionalities de módulos **autorizados y activos** de esa
  empresa; sin AdminOnly; se pueden quitar acciones del catálogo, no inventar permisos
  fuera de él.
- Roles locales `manager` y `guest`: **no borrables**; la matriz del `manager` local no
  se edita (sigue el sync de autorizaciones); `guest` y los roles custom sí son
  editables en permisos.
- Definir el **contrato obligatorio** (plantilla de test y/o rule + playbook) para que,
  al implementar cada módulo futuro, se verifique si el tenant puede o no acceder
  (capas de módulo + permiso de rol). Este change **define** el contrato; **cada módulo
  futuro lo rellena** (no hay que implementar la batería por todos los módulos aquí).
NonGoals:
- Asignación User↔rol en UI (change posterior; onboarding → manager ya vigente;
  `AssignCompanyRole` manager|guest sigue como esté).
- Roles de plataforma / Super Admin (ya cubierto en `platform-roles`).
- Consola Platform ancha, kit Form completo, maestros Fase 1.
- Cambiar las tres capas de módulos ni el sync del manager local (techo modular intacto).
- Implementar los tests de acceso de todos los módulos existentes; solo el contrato /
  plantilla para los futuros.
Skills:
- tenancy-rbac
- v1-reference
- laravel-specialist
- laravel-patterns
Notes:
- Prerrequisito archivado: `c2026-10-17-platform-roles` (plantillas universales,
  materialización, matriz plataforma).
- Dos entornos (acordado): plataforma = Super Admin; tenant = manager con módulos de
  su empresa.
- Techo tenant: módulo autorizado + activado ⇒ acceso a todas las funcionalidades del
  módulo a nivel de empresa; el recorte fino es vía roles custom (este change).
- Ya existen `EnsureModuleIsActive`, R-TEN-05 y sync manager; el contrato nuevo debe
  complementar (no sustituir) esos mecanismos. Detalle Pest plantilla vs rule Cursor vs
  ítem playbook V-xx lo delimita 10-architecture sin diluir la obligatoriedad en módulos
  futuros.
- Referencia visual v1 Configuración→Roles; stack v2 Inertia/React; acciones
  `viewAny/view/create/update/delete`.


## ADDED Requirements

### Requirement: CRUD de roles custom por empresa (manager)
El sistema SHALL permitir al manager de la company activa listar, crear, editar y
borrar roles Spatie con `company_id` de esa company. SHALL impedir borrar los roles
locales `manager` y `guest`. SHALL impedir editar la matriz de permisos del rol local
`manager` (gobernada por el sync). SHALL permitir editar la matriz de `guest` y de
roles custom.

#### Scenario: Crear rol custom en la company
- **WHEN** un manager crea un rol con nombre (y descripción opcional) en su company
- **THEN** el rol queda con `company_id` de la company activa

#### Scenario: No borrar manager ni guest locales
- **WHEN** un manager intenta eliminar el rol local `manager` o `guest`
- **THEN** la operación se rechaza

#### Scenario: Matriz del manager local no editable
- **WHEN** un manager abre la edición del rol local `manager`
- **THEN** no puede alterar sus permisos vía esta UI (solo lectura o equivalente)

#### Scenario: Editar guest o custom
- **WHEN** un manager guarda permisos en `guest` o en un rol custom
- **THEN** `role_has_permissions` de ese rol local refleja la selección

---

### Requirement: Matriz acotada a módulos autorizados y activos
El sistema SHALL mostrar en la matriz del tenant solo permisos de Functionalities
cuyo Module esté autorizado y activo para la company (excluyendo AdminOnly). SHALL
permitir quitar o poner acciones del catálogo visible; SHALL NOT permitir asignar
permisos fuera de ese conjunto.

#### Scenario: Módulo no activo no aparece
- **WHEN** un módulo está autorizado pero apagado (o no autorizado) para la company
- **THEN** sus funcionalidades no aparecen en la matriz de roles del tenant

#### Scenario: AdminOnly ausente
- **WHEN** un manager edita un rol de su company
- **THEN** no se muestran módulos AdminOnly

#### Scenario: Recorte de acción
- **WHEN** el manager desmarca p. ej. `facturas.delete` en un rol custom y guarda
- **THEN** ese rol local deja de tener ese permiso

---

### Requirement: Contrato de verificación de acceso tenant por módulo futuro
El sistema (estándares / plantilla de test / rule del harness) SHALL exigir que cada
módulo nuevo aporte la verificación de si un usuario tenant puede o no acceder
(respetando capas de módulo y permisos de rol). Este change SHALL publicar ese
contrato; los módulos futuros SHALL rellenarlo al implementarse.

#### Scenario: Contrato publicado en el change
- **WHEN** se cierra este change
- **THEN** existe plantilla y/o regla de playbook/harness que describe qué debe
  demostrar cada módulo futuro sobre acceso tenant

#### Scenario: Módulo futuro sin rellenar el contrato
- **WHEN** un change de módulo no aporta la verificación exigida por el contrato
- **THEN** el DoD / revisión no se considera completo (falla CI o gate de 25/30 según
  decida 10)


## MODIFIED Requirements

### Requirement: Roles manager y guest por empresa
Sin cambiar materialización ni sync: se aclara que los roles locales `manager`/`guest`
siguen no borrables en el entorno tenant, que el manager no edita la matriz del
`manager` local, y que pueden existir roles custom adicionales de la company gestionados
por el manager.

#### Scenario: Defaults locales intactos
- **WHEN** se crea una Company
- **THEN** siguen existiendo roles locales `manager` y `guest`; el sync del manager no
  cambia por este CRUD
