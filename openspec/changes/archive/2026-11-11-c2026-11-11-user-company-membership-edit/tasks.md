# Tasks

## 1. Backend update path

- [x] 1.1 Restaurar Action `UpdateUserCompany` + FormRequest
      (position/department nullable; no company_id). Verificar Feature
      update self.
- [x] 1.2 `UserCompanyPolicy::update` (= canManageOwner) + métodos
      controller update profile/admin + rutas. Verificar 403 / perímetro.
- [x] 1.3 Test Feature update ajeno autorizado + rechazo no autorizado.
      Verificar suite membership.

## 2. Frontend modal

- [x] 2.1 `UserCompaniesPanel`: Pencil + Dialog + `updateUrl`; props desde
      `Users/Edit`. Verificar UI.
- [x] 2.2 i18n `editar_vinculo_empresa` / `vinculo_empresa_actualizado` (y
      campos). Verificar keys.
- [x] 2.3 Ajustar test UI que prohibía edit: ahora exige edit+detach sin
      regress ServerTable. Verificar Feature/UI assert.

## 3. Docs + calidad

- [x] 3.1 `docs/modules/Admin.md`: membership con edit modal
      position/department. Verificar fila.
- [x] 3.2 Pint + PHPStan + Feature afectados en verde.

## Workflow follow-up

- `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
