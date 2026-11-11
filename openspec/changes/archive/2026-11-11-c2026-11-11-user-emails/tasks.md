# Tasks

## 1. Schema + modelo

- [x] 1.1 Migración: eliminar unique/`unique(user_id)` en `user_emails`.
      Verificar N inserts mismo user.
- [x] 1.2 Completar `UserEmail` (fillable, relación) + `User::emails()` +
      factory. Verificar relation.
- [x] 1.3 Cargar emails reales en `UserEditPageData` (y readout). Verificar
      props Edit.

## 2. Backend dominio

- [x] 2.1 Policy `UserEmail` (create/delete) = self o updateCompanyUser sobre
      owner. Verificar 403.
- [x] 2.2 Actions Create/Delete + FormRequest (email unique, ≠ principal,
      description nullable). Verificar Feature alta/rechazo/delete.
- [x] 2.3 Controller en `Domains/Admin`; rutas profile + company-users;
      eliminar stub `app/Http/Controllers/UserEmailController`. Verificar
      route:list.

## 3. Frontend tab

- [x] 3.1 Panel contacts: 2 columnas; emails cards + modal alta; teléfonos
      placeholder. Verificar UI.
- [x] 3.2 Acciones mailto, copiar, eliminar (confirm). Sin Edit. Verificar
      source/comportamiento.
- [x] 3.3 i18n claves emails adicionales / observaciones / errores. Verificar
      keys.

## 4. Docs + calidad

- [x] 4.1 Actualizar `docs/modules/Admin.md` (tab emails). Verificar fila.
- [x] 4.2 Pint + PHPStan + Feature afectados en verde.

## Workflow follow-up

- `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
