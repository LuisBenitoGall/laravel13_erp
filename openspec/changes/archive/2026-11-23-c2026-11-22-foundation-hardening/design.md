# Design

## Context

Hoy: `UploadUserImages` / `CreateMediaAsset*` escriben en disco `public`;
`UserImage::url()` y `MediaAsset::url()` exponen `/storage/...` sin auth.
Docs de usuario ya usan disco `local` + stream autenticado
(`UserDocController`). Login: `Auth::attempt` solo email+password.
`UpdateCompanyUserStatus` ya persiste `users.status` global (producto A).
Brief: `docs/prompts/c2026-11-22-foundation-hardening.md`. Auditoría:
`docs/audits/c2026-11-22-foundation-security-audit.md`.

E2E §4: N/A (hardening auth/storage). V-01..V-20 entidad nueva: N/A.

## Goals / Non-Goals

**Goals:**

- Binarios user image + MediaAsset no descargables anónimos vía URL estática.
- Mismo patrón operativo que UserDoc: disco privado + ruta app + authz.
- Login niega inactivos; desactivar company-users = status global + copy UI.
- Migrar ficheros ya en `public` al disco privado sin romper paths BD.

**Non-Goals (design-level):**

- Endpoint anónimo para imágenes con `public=true` / perfiles públicos (F futuro).
- Scope listado galería a solo `CurrentCompany` (F-06).
- Status por membership / soft-disable por vínculo.
- Reescritura masiva de HTML Wysiwyg ya guardado con `/storage/...`.
- F-04, F-07–F-11.

## Decisions

1. **Disco `local` (no signed URL como default)**  
   Alinear con UserDoc: `Storage::disk('local')` bajo los mismos path
   relativos actuales (`users/{id}/images/…`, `gallery/…`). Entrega vía
   `GET` autenticado que hace `response()->file` / stream (inline para
   preview img; attachment solo si hace falta descarga).  
   Alternativa descartada: solo temporary signed URLs — útiles para
   compartir externo, pero el ERP necesita `<img src>` estable en sesión;
   signed cortos rompen caché/lightbox y copiar URL caduca. Signed queda
   como escape hatch opcional **no** requerido en este change.

2. **Rutas de entrega**  
   - User images: rutas hermanas a las mutaciones existentes
     (`profile/images/{userImage}/file`,
     `company-users/{user}/images/{userImage}/file`) con la **misma authz**
     que ver/mutar esa ficha (self / `company-users` + perímetro).  
   - Galería: `gallery/{media_asset}/file` (o `show` binario) con Policy
     `view` / perímetro ya usado en Index.  
   `UserImage::url()` / `MediaAsset::url()` SHALL devolver la URL de esas
   rutas (relativa o `route()`), nunca `/storage/...`.

3. **Flag `public` / `isPubliclyVisible`**  
   Se mantienen como semántica de producto futuro; **este change no abre**
   GET anónimo aunque `public=true`. La entrega autenticada aplica a todos
   los binarios. El requisito de “visibilidad pública” del spec se interpreta
   como metadato de elegibilidad, no como URL estática abierta.

4. **Wysiwyg / “copiar URL”**  
   La URL copiable y la del picker pasan a la ruta autenticada. En sesión
   ERP las `<img>` cargan con cookie. Contenido histórico con `/storage/...`
   puede romperse hasta reinsertar (ver Risks).

5. **Login + status**  
   Incluir `status => true` en el array de `Auth::attempt` (columna boolean
   casteada) **o** post-attempt: si `! $user->status` → logout + error
   i18n distinto o genérico de credenciales. Preferencia: attempt con
   `status` + mismo mensaje de credenciales (no enumerar cuentas) salvo
   que ya exista clave i18n de cuenta inactiva en el proyecto — entonces
   usarla. No crear sesión usable para inactivo.

6. **F-03 = A**  
   Sin cambio de Action de status: ya es global. UI: copy/confirm en
   toggle fila y bulk desactivar aclarando que afecta al acceso ERP
   global (todas las companies). Tests: tras desactivar, login falla.

7. **Migración de ficheros**  
   Comando Artisan idempotente (p. ej. `erp:migrate-public-media-to-local`)
   que copia/mueve paths existentes de `public` → `local` si existen en
   public y faltan en local; opcionalmente borra del public tras éxito.
   Paths en BD no cambian. Documentar en README/tasks ejecución post-deploy
   local. No hace falta migration SQL.

8. **Tests**  
   Feature: guest GET file → no 200 binario; actor autorizado → 200;
   login inactivo; desactivar → login falla; activo sigue OK.

## Risks / Trade-offs

- [HTML Wysiwyg con `/storage/gallery/...`] → Mitigation: documentar;
  reinsertar desde picker; no scraper de contenidos en este change.
- [Avatares/topbar apuntan a `/storage`] → Mitigation: cualquier prop que
  use `url()` se actualiza al cambiar el modelo; grep de `/storage/` en
  front de imágenes.
- [symlink `public/storage` sigue sirviendo ficheros no migrados] →
  Mitigation: comando de migración + no escribir más en `public`; tras
  migrate, borrar copias public.
- [Mensaje login “cuenta inactiva” vs anti-enumeración] → Mitigation:
  preferir error genérico de credenciales salvo i18n ya existente.
- [Imágenes `public=true` dejan de ser anónimas] → Mitigation: aceptado
  (F-01); perfil público anónimo = change futuro.

## Migration Plan

1. Añadir rutas + controllers stream; cambiar Actions a disk `local`;
   actualizar `url()`.  
2. Front: URLs vía modelo/props (sin hardcode `/storage`).  
3. Login status + i18n.  
4. Copy UI desactivar.  
5. Comando migrate ficheros + ejecutar en local.  
6. Tests Pest. Rollback: revertir código; ficheros en `local` siguen
   accesibles si se vuelve a `url()` antigua solo tras copiar de vuelta
   (no automático).

## Open Questions

Ninguna bloqueante del brief.

## DX notes (post-apply)

- F-11 Pest Browser + sockets queda fuera de este change (sin ampliar
  alcance). Comando local ejecutado:
  `php artisan erp:migrate-public-media-to-local --delete-public`.
