Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-11-user-companies-tab
Origen: informe 25-architecture-review (**RETURN TO IMPLEMENTATION**).
Contrato: `openspec/changes/c2026-11-11-user-companies-tab/`
— mandan `design.md` (NonGoals + Decision 7) y specs
`user-companies-membership` / `user-edit`.
Si este prompt y los artefactos chocan, mandan design + specs (no
`tasks.md` si se desvía).
No reescribas proposal.md, design.md ni los deltas de specs para
“legalizar” el edit.
No archives. No escribas tests/Browser/ (E2E N/A). No amplíes alcance
(Empleo, alta de vínculos, borrar User/Company, otros tabs).

## Problema (HIGH) — edición inventada fuera de contrato

Listado ServerTable, detach, última company, switch de sesión (menor
`companies.id`) / logout self y perímetro SA están bien encaminados
(`UserCompaniesMembershipTest` 7/7).

Falla el **alcance**:

- Brief / `design.md` NonGoals: sin alta/**edición** de vínculos ni de
  `position`/`department` en UI de este change.
- Decision 7: position/department **solo lectura** en tabla; **sin form
  edit**.
- Specs ADDED: listado + detach + sesión; **no** hay requirement de
  update.
- `tasks.md` §3.1 dice «edit modal» → artefacto desalineado; **no**
  autoriza ampliar el contrato.

Código fuera de contrato hoy:

- `UpdateUserCompany`, `UpdateUserCompanyRequest`
- `UserCompanyPolicy::update`
- `UserCompanyController::updateForProfile` /
  `updateForCompanyUser`
- rutas `profile.companies.update` /
  `admin.company-users.companies.update`
- modal edit en `UserCompaniesPanel` (`updateUrl`, Dialog, Pencil)
- test «self actualiza position y department…»
- i18n / fila `Admin.md` que documentan el modal como entregable

## Qué hacer (obligatorio HIGH)

### 1. Retirar el write path de position/department

Eliminar por completo (sin dejar código muerto ni rutas huérfanas):

1. Action `UpdateUserCompany` y FormRequest `UpdateUserCompanyRequest`.
2. Métodos `updateForProfile` / `updateForCompanyUser` del controller.
3. Registro de rutas update (web + admin).
4. Ability `update` de `UserCompanyPolicy` (dejar `viewAny` + `delete`).
5. UI: quitar modal, `useForm` de edit, `updateUrl`, acción fila editar
   (`Pencil`). Mantener ServerTable + confirm de **delete/detach** si
   `canManage`.
6. Props en `Users/Edit`: no pasar `updateUrl`.
7. Tests: borrar o reescribir el Feature de update; el de UI debe
   afirmar ServerTable + delete **sin** Dialog/edit/`updateUrl`.
8. i18n: quitar claves solo del edit/modal si quedan huérfanas
   (p. ej. `editar_vinculo_empresa`, `vinculo_empresa_actualizado` si no
   se usan).
9. `docs/modules/Admin.md`: fila membership → listado RO de
   position/department + detach; **sin** «edit modal».
10. `openspec/.../tasks.md` §3.1: alinear el texto al contrato (ServerTable
    + delete confirm; position/department solo lectura; sin CTA alta /
    sin edit). No marques como hecho un edit que design prohíbe.

Columnas de tabla (nombre, posición, departamento, fecha alta) y
formatter `—` para null: **se mantienen** (solo lectura).

### 2. No tocar lo que ya cumple contrato

Conservar sin regresiones:

- Migración / modelo `UserCompany` + pivot fields.
- `*/table` profile + company-users (todas las memberships).
- `DetachUserCompany` + logout / switch sesión (menor id, Active).
- Perímetro company-users (`abort_unless` + tests SA A↛B).
- Tab Empresas usable (ya no placeholder).
- Glosario membership ≠ Empleo.

**No** unifiques aún `DetachCompanyUser` vs `DetachUserCompany` (MEDIUM
del informe; fuera de este fix salvo que sea trivial y no amplíe
alcance).

**No** pidas a 10 ampliar design para quedarte el modal. Si Luis/10
decidieran edición más adelante, será otro change/amend; este RETURN es
**strip**.

### 3. Verificación

```text
php artisan test --compact tests/Feature/Admin/UserCompaniesMembershipTest.php
```

Pint paths PHP dirty (`vendor/bin/pint --dirty --format agent`).
Comprobar `php artisan route:list` (o assert en test): **no** existen
rutas `*.companies.update`.

## UI / estilos

Al quitar el modal: no rediseñes el panel. Misma ServerTable / row
actions que el patrón functionalities; solo delete confirm.
Catálogo: `docs/standards/frontend.md`. Sin looks nuevos.

## Fuera de alcance

- Alta de vínculos desde el tab.
- Entidad Empleo / RRHH.
- Refactor global de detach del index company-users.
- emails / phones / images / docs.
- Archive / 30-testing / E2E.

## Gate de salida

- [x] Cero rutas/Action/Request/Policy/UI/tests de update membership.
- [x] Tab: ServerTable RO + detach; position/department visibles, no
      editables.
- [x] `tasks.md` §3.1 y `Admin.md` alineados con design (sin edit modal).
- [x] Features detach / sesión / perímetro / table siguen verdes.
- [x] Volver a 25-architecture-review (no archive).
