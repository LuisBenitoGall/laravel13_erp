# Design

## Context

See proposal.md — Why. Ficha con tabs Perfil / Contraseña / Emails-tels /
Empresas; avatar placeholder en readout y topbar (iniciales). Kit Form sin
Dropzone; `react-dropzone` ya listado en `libraries.md` (por módulo). Authz
contactos = self o `updateCompanyUser`.

## Goals / Non-Goals

**Goals:** tab Imágenes; `UserImage` + storage; Dropzone kit; featured→avatar
(readout+topbar); public∧is_public; límites 20/1MB; dominio Admin.

**Non-Goals:** docs no-imagen; límites de pago; Wysiwyg; CRUD Empresas.

**E2E:** no aplica (`e2e.md` §4).

**Excepciones a standards:** ninguna.

## Decisions

1. **Capabilities**  
   `user-images` durable; deltas en `user-edit`, `form-kit`, `shell-topbar`.

2. **Schema**  
   Migration aportada tal cual (incl. `featured_user_id` generated + unique).
   SoftDeletes. Path relativo en columna `image` (p. ej.
   `users/{id}/images/{uuid}.ext`). Disco `public`; `php artisan storage:link`
   documentado en tasks.

3. **Modelo / dominio**  
   `App\Models\UserImage`; `User::images()`, `User::featuredImage()`.
   Actions: `UploadUserImages` (multi, ≤20 y cupo restante), `DeleteUserImage`
   (soft delete; si era featured → sin featured), `MarkUserImageFeatured`
   (desmarca otras; schema refuerza), `UpdateUserImagePublic` (toggle).
   Controller Domains/Admin; rutas profile + company-users nested.
   FormRequest: files image mimes + max:1024 (KB); count ≤20; count total
   user ≤20. Policy como emails sobre owner User.

4. **Flag `public` (UI)**  
   Brief exige regla de visibilidad pero no listó control; **decisión:**
   toggle/icono en cada card para `public`. Sin él el flag quedaría siempre
   false. Helper `UserImage::isPubliclyVisible(User $owner): bool` =
   `$this->public && $owner->is_public`. Contextos públicos futuros
   (directorio, portal) lo consultan; **avatar de sesión / ficha edit**
   muestran featured aunque `public=false` (no es exposición pública).

5. **Dropzone kit**  
   `Components/Form/Dropzone.tsx`: props `accept`, `maxSize` (bytes),
   `maxFiles`, `multiple`, `disabled`, `onFiles` / integración upload Inertia
   (FormData). UI: zona drag/click, texto i18n, errores cliente (tipo/peso/
   cantidad) vía toast o slot. Tab Imágenes: accept image types, 1MB, 20.
   Actualizar `frontend.md` catálogo (Dropzone deja de pending). Instalar
   `react-dropzone` en este change.  
   *Alt. descartada:* Dropzone solo ad-hoc en Users (brief: kit genérico).

6. **Avatar shared**  
   HandleInertia / shared auth user: `avatar_url` nullable (URL featured o
   null). `UserMenu` muestra `<img>` si hay URL, si no iniciales.
   `UserProfileReadout` prop `avatarUrl`. Al mutar images del self → reload
   shared o visit que refresque props.

7. **Primera imagen**  
   No auto-featured al subir (default false); el user marca estrella.
   *Alt. descartada:* auto-featured si es la primera (no debatido).

8. **Glosario / module-map**  
   `UserImage` (Imagen de usuario). Admin entidades + UserImage.
   Ficha Admin: fila tab Imágenes.

9. **V-01..V-20**  
   Aplica: migración, modelo/factory, Policy, Actions, Requests, rutas,
   i18n, storage, tests Feature (authz perímetro, límites, featured,
   public∧is_public, soft delete). No: menú/Module nuevos, BelongsToCompany
   en UserImage (FK user), ServerTable, E2E §4.

## Risks / Trade-offs

- [unique `(user_id,image,deleted_at)`] → en MySQL varios soft-deleted con
  `deleted_at` NULL no aplican; filas deleted con mismo path+timestamp raro.
  Mitigación: filenames UUID.  
- [storage huérfano] → soft delete no borra fichero ya; task opcional de
  cleanup fuera de alcance salvo borrar archivo en Delete Action
  **decisión:** al soft-delete, eliminar fichero del disco en la Action
  (evita basura; unique path no se reutiliza).  
- [20 hardcode] → constante/config `user_images.max_per_user=20` para futuro
  upgrade.  
- [perfil mínima en shell-topbar] → requirement obsoleto (ya `/profile`
  ficha); este change actualiza avatar + enlace a ficha real.

## Migration Plan

1. npm react-dropzone + Dropzone kit.  
2. Migración + modelo.  
3. Backend upload/featured/public/delete.  
4. Tab UI + avatar shared/readout/topbar.  
5. Docs + tests.  
Rollback: drop table; quitar Dropzone del kit si hace falta.

## Open Questions

Ninguna.
