# Tasks

## 1. Dropzone kit + dependencia

- [x] 1.1 Instalar `react-dropzone` (canon libraries.md) y verificar package.json.
- [x] 1.2 Implementar `Components/Form/Dropzone` (accept, maxSize, maxFiles,
      multiple, errores cliente) + export. Verificar uso aislado / story o
      página de referencia.
- [x] 1.3 Actualizar `docs/standards/frontend.md` (Dropzone deja de pending)
      y templates-frontend si aplica. Verificar fila catálogo.

## 2. Schema + modelo

- [x] 2.1 Migración `user_images` (migration aportada + generated featured).
      Verificar migrate; `storage:link` documentado/ejecutado en local.
- [x] 2.2 Modelo `UserImage` + `User::images()` / featured + factory +
      helper visibilidad pública. Verificar relation/factory.
- [x] 2.3 Props images + avatar_url en `UserEditPageData` / shared auth.
      Verificar props Edit y shared.

## 3. Backend dominio

- [x] 3.1 Policy UserImage (upload/update public/featured/delete) = self o
      updateCompanyUser sobre owner. Verificar 403.
- [x] 3.2 Actions Upload/Delete/MarkFeatured/UpdatePublic + FormRequests
      (mimes, 1MB, ≤20 subida y total, country N/A). Verificar Feature.
- [x] 3.3 Controller Domains/Admin + rutas profile + company-users.
      Verificar route:list.
- [x] 3.4 Test perímetro (analog R-TEN-05): no mutar imágenes de user fuera
      de company; SA no bypass perímetro. Verificar Feature.
- [x] 3.5 Tests: featured único; public∧is_public; soft delete quita fichero;
      rechazo formato/peso/límite 20. Verificar Feature.

## 4. Frontend tab + avatar

- [x] 4.1 Tab Imágenes en Edit (orden tras emails/tels): Dropzone + grid +
      lightbox + delete/featured/public. Verificar UI.
- [x] 4.2 Readout + UserMenu/topbar consumen avatar featured. Verificar UI
      self con/sin featured.
- [x] 4.3 i18n claves tab/dropzone/errores/límites. Verificar keys.

## 5. Docs + calidad

- [x] 5.1 Actualizar `docs/modules/Admin.md` (tab Imágenes). Verificar fila.
- [x] 5.2 Pint + PHPStan + Feature afectados en verde.

## Workflow follow-up

- `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.

## Notas locales

- `php artisan storage:link` ejecutado (enlace `public/storage` → `storage/app/public`).
