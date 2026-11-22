# Tasks

## 1. Storage privado — UserImage

- [x] 1.1 Cambiar `UploadUserImages` / `DeleteUserImage` a disco `local` (paths
  relativos actuales) y verificar subida+borrado contra `local` (no
  `public`)
- [x] 1.2 Añadir rutas GET de entrega (profile + company-users) + método
  stream en `UserImageController` con la misma authz que la ficha; verificar
  guest → no 200 binario y actor autorizado → 200
- [x] 1.3 Actualizar `UserImage::url()` a la ruta autenticada y grep front
  (`/storage/`) en preview/avatar/lightbox; verificar props/UI usan la nueva
  URL
- [x] 1.4 Tests Pest Feature: anónimo denegado; autorizado OK; perimeter/
  self vs ajeno 403 — verde con `php artisan test --compact --filter=UserImage`

## 2. Storage privado — MediaAsset

- [x] 2.1 Cambiar `CreateMediaAsset` / `CreateMediaAssetCopy` /
  `DeleteMediaAsset` a disco `local`; verificar create/copy/delete en `local`
- [x] 2.2 Añadir GET entrega autenticada (Policy/perímetro Index) +
  `MediaAsset::url()` a esa ruta; verificar guest denegado y autorizado 200
- [x] 2.3 Actualizar picker JSON / copy URL / miniaturas front a URL
  autenticada; verificar no hay `/storage/` en respuestas de galería
- [x] 2.4 Tests Pest Feature galería file + picker URL — verde con filtro
  MediaAsset/Gallery

## 3. Migración de ficheros existentes

- [x] 3.1 Comando Artisan idempotente public→local para paths de
  `user_images` y `media_assets`; verificar dry-run/run mueve o copia y no
  rompe paths BD
- [x] 3.2 Ejecutar el comando en entorno local de desarrollo y verificar que
  las URLs nuevas sirven ficheros migrados

## 4. Login exige status activo

- [x] 4.1 En login (`Auth::attempt` con `status` o post-check + logout)
  denegar inactivos sin sesión usable; i18n acorde al design; verificar
  manual/Pest: inactivo no entra
- [x] 4.2 Test Pest: user activo con credenciales OK sigue flujo
  post-login; inactivo no — verde con filtro Login

## 5. Desactivar global + copy UI (F-03 = A)

- [x] 5.1 Confirmar `UpdateCompanyUserStatus` / bulk siguen status global;
  añadir copy/confirm i18n en fila y masivo aclarando impacto ERP global;
  verificar strings en es (+en/ca si el proyecto las exige en el mismo PR)
- [x] 5.2 Test Pest: tras desactivar desde company-users, login de ese user
  falla — verde

## 6. Cierre calidad

- [x] 6.1 `vendor/bin/pint --dirty --format agent` + tests afectados del
  change en verde
- [x] 6.2 Nota DX opcional en design/tasks (no código): F-11 Pest Browser +
  sockets queda fuera; sin ampliar alcance

### Nota DX (6.2)

F-11 (Pest Browser + sockets / harness E2E) queda fuera de este change;
sin ampliar alcance. Oleada posterior.

## 7. Correcciones 25 (RETURN TO IMPLEMENTATION)

- [x] 7.1 All-users: contexto `directory` + GET `all-users/{user}/images/{userImage}/file`
  (viewAnyAllUser + is_public); UserQuickViewData recibe imageContext
- [x] 7.2 Company-users RO: `UserImagePolicy::view` vía viewCompanyUser (mutaciones intactas)
- [x] 7.3 Gallery file alineado a `gallery.view` (+ perímetro)
- [x] 7.4 Test Feature comando `erp:migrate-public-media-to-local`

## Workflow follow-up

- Reauditoría 25-architecture-review tras este fix.
- 30-testing + `/opsx-archive` cuando DoD OK.
- Oleadas posteriores: F-04, F-06, F-07/F-08, F-09–F-11.
