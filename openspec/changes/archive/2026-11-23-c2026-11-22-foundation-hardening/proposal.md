# Proposal

## Why

La auditoría de cimientos (`docs/audits/c2026-11-22-foundation-security-audit.md`)
marca como prioritarios el almacenamiento anónimo de imágenes (user + galería) y
el login sin comprobar `users.status`. Antes de módulos de negocio hay que
cerrar F-01, F-05, F-02 y fijar F-03 como status global (decisión de producto).

## What Changes

- **BREAKING:** Imágenes de usuario dejan de servirse por URL estática de disco
  `public` (`/storage/...`). Pasan a disco privado + ruta autenticada con authz
  (mismo patrón que docs de usuario).
- **BREAKING:** `MediaAsset` de galería deja de ser descargable anónimamente por
  URL estática de disco `public`. Mismo patrón de storage privado / ruta app.
- Login SHALL rechazar users con `users.status` inactivo (sin sesión usable).
- Desactivar desde Usuarios empresa (fila o masivo) queda documentado y
  comunicado en UI como acción **global** sobre `users.status` (no membership);
  alineado con el bloqueo de login. Sin modelar status por vínculo en este change.
- Migración/comando de ficheros existentes `public` → disco privado cuando aplique.

## Capabilities

### New Capabilities

_(ninguna)_

### Modified Capabilities

- `user-images`: storage privado + entrega autenticada; ya no URL anónima `/storage`.
- `admin-gallery`: storage privado + entrega autenticada; URL copiable deja de ser
  estática pública anónima.
- `admin-ui-login`: login exige `users.status` activo.
- `company-users-index`: desactivar = status global + bloquea acceso ERP; copy UI.

## Impact

- Backend: `UploadUserImages`, `DeleteUserImage`, `UserImage::url()`, controllers
  de descarga; Actions/controllers `MediaAsset` (`Create`/`Copy`/`Delete`/`url`);
  `AuthenticatedSessionController` / `LoginRequest`; `UpdateCompanyUserStatus`
  (comportamiento ya global — confirmar + UI).
- Frontend: preview/avatar/lightbox de imágenes user; galería (miniatura, copiar
  URL) deben apuntar a rutas app autenticadas (o signed de corta vida si se
  elige en design).
- i18n: mensajes login inactivo; confirmación/copy de desactivar global.
- Tests Pest: acceso anónimo 401/403/404 a binarios; login inactivo; desactivar
  → no login.
- Fuera de alcance (oleadas posteriores): F-04 PII quick-view, F-06 scope
  galería CurrentCompany, F-07/F-08 higiene `can`/FormRequest, F-09 registro,
  F-10 BelongsToCompany null, F-11 Pest sockets.
