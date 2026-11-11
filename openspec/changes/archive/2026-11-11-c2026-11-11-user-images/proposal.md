# Proposal

## Why

La ficha de usuario no gestiona imágenes ni avatar real; el kit Form aún no
tiene Dropzone (marcado pendiente). Hace falta tab Imágenes, `UserImage`,
avatar featured en readout/topbar, y un Dropzone reutilizable para el ERP.

## What Changes

- Tab **Imágenes** en `Users/Edit` (tras Emails y teléfonos): Dropzone +
  grid/galería, eliminar, featured, toggle `public`.
- Schema/modelo `user_images` / `UserImage` (migration aportada; un featured
  por user; storage `public` en `users/{id}/images/…`).
- Validación: jpg/jpeg/png/gif/webp, 1 MB, máx. 20 por subida y por user.
- Visibilidad pública: `image.public && user.is_public`.
- Featured = avatar en readout y topbar/menú.
- Componente `Form/Dropzone` genérico (`react-dropzone`); documentar en
  frontend.md / templates.
- Authz igual contactos (self o `company-users.update` + perímetro).
- Glosario `UserImage`; ficha Admin; module-map.

## Capabilities

### New Capabilities

- `user-images`: imágenes del User (upload, grid, featured/avatar, public,
  límites, storage).

### Modified Capabilities

- `user-edit`: tab Imágenes; readout usa avatar featured.
- `form-kit`: añade Dropzone; deja de excluirlo.
- `shell-topbar`: avatar de sesión muestra imagen featured si existe.

## Impact

- Migración `user_images`; modelo/factory; Policy/Actions/Requests/controller.
- npm `react-dropzone` (canon libraries.md).
- `UserProfileReadout`, `UserMenu`/`AppTopbar`, HandleInertia shared props.
- Tests Feature; docs Admin + frontend catalog.
