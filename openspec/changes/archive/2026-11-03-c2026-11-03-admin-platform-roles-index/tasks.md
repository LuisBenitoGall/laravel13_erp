# Tasks

## 1. Backend Resource / table (reutilizar)

- [x] 1.1 Extender `PlatformRoleResource` con `can_delete` (false si
      `isProtectedTemplate()` o si tiene usuarios) y verificar JSON del
      `admin.roles.table`
- [x] 1.2 Confirmar que `platformManaged` sigue excluyendo Super Admin del table;
      feature assertion: ninguna fila `name === 'Super Admin'`
- [x] 1.3 R-TEN-05: N/A para roles de plataforma (`company_id` NULL); documentar en
      test o nota de suite (roles tenant = otro change)

## 2. Frontend Index

- [x] 2.1 Reescribir `Admin/Roles/Index`: columnas name, description, universal
      (sí/no i18n); `rowActions` **Edit en todas las filas** + **Delete** con confirm
      solo si `can_delete`; `persistKey` coherente; smoke/Inertia
- [x] 2.2 ActionBar: control Crear rol **disabled** / stub (D1), sin `href` a Create;
      icono Plus + label i18n; verificar visible y no navega a Create
- [x] 2.3 i18n: claves usadas (universal, eliminar, crear/editar, confirmaciones) sin
      literales sueltos en Index

## 3. Feature tests

- [x] 3.1 Index/table: Super Admin no aparece; `can_delete` false y
      `permissions_readonly` false en manager/guest
- [x] 3.2 Delete: rol no protegido sin usuarios OK; manager/guest rechazado
- [x] 3.3 Stub Crear documentado en DoD front (2.2); Create no es DoD de este change

## 4. Ficha / vinculaciones

- [x] 4.1 `docs/modules/Admin.md`: Index (Edit+Delete condicional, CTA Crear stub;
      plantillas no borrables / matriz editable; Super Admin fuera)
- [x] 4.2 V-07/V-08/V-09: sin Functionality nueva (reutiliza `roles`); menú ya apunta
      a Index; V-09 N/A módulo admin
- [x] 4.3 E2E rol 40: N/A (`e2e.md` §4)

## 5. Calidad

- [x] 5.1 Pint + PHPStan en PHP tocado; feature Platform Roles Index verde

## 6. Corrección 25 (RETURN TO IMPLEMENTATION)

- [x] 6.1 Index Observable: ServerTable Acciones `rowActions` (Edit+Delete
      condicional), Crear stub disabled; assets / smoke UI
- [x] 6.2 `PlatformRoleTableRequest`: `$filterable`/`$sortable` alineados con
      `headerFilterKind` del Index (`name`, `description`, `universal`)
- [x] 6.3 Feature: filter `name` whitelistado altera resultado; asserts fuente Index
      endurecidos (sin columna `accion` legacy / sin href Create)

## 7. Matiz contrato — plantillas editables (implementado; ratificado por 10)

- [x] 7.1 Spec/design: plantillas `manager`/`guest` no borrables; sí Edit + matriz
      guardable; manager local sigue por sync
- [x] 7.2 Index Acciones: Edit en todas las filas; Delete solo si `can_delete`
- [x] 7.3 `UpdatePlatformRole` + Resource: matriz plantilla guardable;
      `permissions_readonly` no bloquea plantillas
- [x] 7.4 Tests + ficha Admin alineados

## 8. Ratificación 10 (contrato templates)

- [x] 8.1 Enmendar proposal/design/specs/tasks a una sola verdad (Edit+plantillas
      editables; Crear stub; sync local intacto); sin `openspec new change`
- [x] 8.2 Revisar código de referencia vs contrato: **sin hueco de implementación
      detectado** → no tasks residuales para 20; hand-off a **25** re-auditoría
      (Index + Edit matriz manager/guest + Delete protegido + Crear stub)
- [x] 8.3 `openspec validate` del change en verde

## Workflow follow-up

- **Siguiente:** agente **25-architecture-review** (re-auditoría estricta con UI).
- Si 25 RETURN → 20 solo con tasks nuevas que 25/10 dejen explícitas.
- Si 25 APPROVED → 30 `/opsx-archive` (sin 40).
- Change posterior: activar CTA Crear; tenant roles con condiciones.
