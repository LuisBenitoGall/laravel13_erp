# Tasks

## 1. Datos + modelo

- [x] 1.1 Migración `companies.parent_id` nullable FK + índice. Verificar
      migrate.
- [x] 1.2 Modelo `parent`/`children` + helper anti-ciclo (ancestors /
      isDescendant). Verificar unit/Feature básico.

## 2. Backend jerarquía

- [x] 2.1 Request/Action (o endpoints) asignar/quitar padre; candidatos
      `forUser`, ≠ self, no descendiente. Verificar Feature rechazo ciclo
      y fuera de vínculo.
- [x] 2.2 Adjuntar/quitar hijo (`parent_id` = editada / null); mismas
      reglas. Verificar Feature.
- [x] 2.3 Props Edit: parent, children, hierarchy_options. Verificar
      payload Inertia.

## 3. Frontend Edit

- [x] 3.1 Tab jerarquía en `Companies/Edit` (padre select + lista hijas
      add/remove); Create intacto. Verificar UI/source.
- [x] 3.2 i18n claves tab/acciones. Verificar keys.

## 4. Docs + calidad

- [x] 4.1 `docs/modules/Admin.md` (y glosario si hace falta): parent_id /
      tab jerarquía. Verificar.
- [x] 4.2 Pint + PHPStan + Feature hierarchy en verde.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Brand (anchor + descendientes): change posterior.
