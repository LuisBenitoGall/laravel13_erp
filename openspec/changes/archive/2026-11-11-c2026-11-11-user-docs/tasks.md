# Tasks

## 1. Schema + modelo

- [x] 1.1 Migración `user_docs` (user_id, path, original_name, mime,
      size_bytes, softDeletes, unique user+path+deleted_at). Verificar
      migrate.
- [x] 1.2 Modelo `UserDoc` + `User::docs()` + factory + config
      max_per_user/max_kilobytes. Verificar relation/factory.
- [x] 1.3 Cargar docs en `UserEditPageData`. Verificar props Edit.

## 2. Backend dominio

- [x] 2.1 Policy UserDoc (upload/download/delete) = self o
      updateCompanyUser sobre owner. Verificar 403.
- [x] 2.2 Actions Upload/Delete + FormRequest (mimes, 20 MB, cupo 30) +
      delete quita fichero disco `local`. Verificar Feature.
- [x] 2.3 Controller Domains/Admin + rutas profile + company-users
      (store/destroy/download). Verificar route:list + download auth.
- [x] 2.4 Test perímetro (analog R-TEN-05) + rechazo formato/peso/límite
      30 + download 403. Verificar Feature.

## 3. Frontend tab

- [x] 3.1 Tab Documentos tras Imágenes: Dropzone (accept docs, 20 MB) +
      listado download/delete. Verificar UI.
- [x] 3.2 i18n claves tab/dropzone/errores/límites. Verificar keys.

## 4. Docs + calidad

- [x] 4.1 Actualizar `docs/modules/Admin.md` (tab Documentos). Verificar
      fila.
- [x] 4.2 Nota ops php.ini ≥20 MB si aplica; Pint + PHPStan + Feature en
      verde.

## Workflow follow-up

- `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Share: change futuro (brief Notes).
