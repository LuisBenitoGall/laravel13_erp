## Purpose

Roles Spatie `manager` y `guest` por empresa al crear Company (desde plantillas universales no borrables); vínculo del creador como manager; asignación mínima manager|guest; roles custom adicionales gestionados por el manager. El sync del manager local no lo limita la matriz de la plantilla ni el CRUD tenant.

## Requirements

### Requirement: Roles manager y guest por empresa
El sistema SHALL, al crear cada Company, materializar desde las plantillas universales
los roles Spatie `manager` y `guest` asociados a esa empresa (`company_id`), vincular
al usuario creador en `user_companies`, asignarle el rol `manager`, y dejar `guest`
con los permisos de su plantilla (vacía por defecto). SHALL tratar `manager` y `guest`
como roles universales de serie no borrables a nivel de plantilla **y** no borrables
como roles locales de la company. SHALL impedir que el manager del tenant altere los
permisos del rol local `manager` (el sync de autorizaciones sigue gobernándolos). SHALL
permitir roles Spatie adicionales (`company_id` de la company) gestionados por el
manager. SHALL permitir asignar `manager` o `guest` a un usuario ya vinculado a la
company sin crear roles adicionales. SHALL NOT limitar el sync de permisos del
`manager` local mediante la matriz de la plantilla universal.

#### Scenario: Alta de company genera roles obligatorios
- **WHEN** un usuario crea una Company nueva
- **THEN** existen los roles `manager` y `guest` con `company_id` de esa company, el
  creador está en `user_companies` y tiene el rol `manager`

#### Scenario: Guest sin permisos por defecto
- **WHEN** se materializa el rol `guest` de una company desde plantilla vacía
- **THEN** no tiene ninguna entrada en `role_has_permissions`

#### Scenario: Asignar guest a usuario vinculado
- **WHEN** un actor autorizado asigna el rol `guest` a un usuario ya vinculado a la
  company
- **THEN** ese usuario tiene el rol `guest` de esa company (con `company_id` correcto) y
  no se crea ningún rol nuevo

#### Scenario: Plantillas universales no borrables
- **WHEN** Super Admin intenta borrar la plantilla universal `manager` o `guest`
- **THEN** la operación se rechaza

#### Scenario: Roles locales manager y guest no borrables por el tenant
- **WHEN** un manager intenta eliminar el rol local `manager` o `guest` de su company
- **THEN** la operación se rechaza

#### Scenario: Sync del manager local intacto respecto al CRUD tenant
- **WHEN** el manager usa el CRUD de roles de empresa
- **THEN** los permisos del rol local `manager` siguen gobernados solo por el sync de
  autorizaciones Basic/Optional
