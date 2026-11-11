# Design

## Context

See proposal.md — Why. UserImage + Dropzone ya en ficha; disco Laravel
`local` = `storage/app/private`. Authz ficha = self o `updateCompanyUser`.
Brief: sin share en este change; decisiones de share futuro documentadas
en Notes del brief.

## Goals / Non-Goals

**Goals:** tab Documentos; `UserDoc` + storage privado; upload/list/download/
delete; límites 20 MB / 30; Domains/Admin; reutilizar Dropzone.

**Non-Goals:** share/revocar/bandeja destinatario; 500 MB; chunked/S3;
cambiar images/emails/tels/Empresas.

**E2E:** no aplica (`e2e.md` §4).

**Excepciones a standards:** ninguna.

## Decisions

1. **Capability `user-docs`**  
   Comportamiento durable; `user-edit` solo delta de tabs.

2. **Schema `user_docs`**  
   - `id`, `user_id` FK cascadeOnDelete  
   - `path` string(191) — relativo en disco `local`  
   - `original_name` string(255) — nombre para UI/download  
   - `mime` string(127)  
   - `size_bytes` unsignedBigInteger  
   - softDeletes, timestamps  
   - unique `(user_id, path, deleted_at)` (mismo criterio que images)  
   - index `user_id`  
   Path fichero: `users/{id}/docs/{uuid}.{ext}`.  
   *Alt. descartada:* disco `public` (brief: privado + ruta auth).

3. **Disco**  
   `Storage::disk('local')` (root `storage/app/private`). Download vía
   `Storage::download` / response stream en controller; nunca
   `Storage::url` público.

4. **Modelo / dominio**  
   `App\Models\UserDoc`; `User::docs()`.  
   Actions: `UploadUserDocs` (multi hasta cupo restante y ≤30 total;
   máx ficheros por request razonable, p. ej. min(10, cupo)),
   `DeleteUserDoc` (soft delete + borrar fichero del disco).  
   Policy: `upload`/`delete`/`download` = self o `updateCompanyUser` sobre
   owner (misma puerta que images).  
   FormRequest: mimes `pdf,doc,docx,xls,xlsx` (o mimetypes equivalentes);
   `max:20480` (KB = 20 MB); count vs cupo.  
   Config: `user_docs.max_per_user=30`, `user_docs.max_kilobytes=20480`.  
   Controller Domains/Admin; rutas:
   - profile: store / destroy / download  
   - company-users: análogas nested.

5. **UI**  
   `UserDocsPanel`: Dropzone (accept docs, maxSize 20MB, maxFiles acorde
   cupo) + listado (nombre, tamaño, fecha) + download (link/GET auth) +
   delete confirm. Sin share UI. Tab tras `images`, antes de `empresas`.
   Props desde `UserEditPageData`.

6. **Ops upload**  
   Documentar en tasks/Notes que Laragon/PHP debe permitir ≥20 MB
   (`upload_max_filesize`, `post_max_size`). No es change de infra CI
   salvo que los tests usen ficheros pequeños (&lt;1 MB) — **decisión:**
   tests con fixtures pequeños; validación `max:20480` cubierta con mock
   size o archivo límite en Feature donde sea práctico.

7. **Share futuro (no implementar)**  
   Pivot `user_doc_shares`; solo same-tenant; solo owner; cascade al
   delete doc; bandeja destinatario TBD. No tablas en este change.

8. **Glosario / module-map**  
   `UserDoc` (Documento de usuario). Admin + UserDoc. Ficha Admin fila.

9. **V-01..V-20**  
   Aplica: migración, modelo/factory, Policy, Actions, Requests, rutas,
   i18n, storage privado, tests (authz perímetro, límites, download 403,
   soft delete). No: menú/Module nuevos, BelongsToCompany en UserDoc,
   ServerTable, E2E §4, share.

## Risks / Trade-offs

- [20 MB vs defaults PHP] → fallos opacos si php.ini bajo. Mitigación:
  nota ops + validación app.  
- [soft delete + file unlink] → igual images: borrar fichero en Delete
  Action.  
- [manager descarga docs personales] → brief: misma authz que mutar ficha;
  aceptado (gestor de company-users puede descargar al gestionar).  
- [nombre UserDoc vs Document] → glosario `UserDoc` (personal, no doc
  comercial del ERP).

## Migration Plan

1. Migración + modelo/factory.  
2. Backend upload/delete/download + Policy.  
3. Tab UI + i18n.  
4. Docs + tests.  
Rollback: drop `user_docs`; limpiar `storage/app/private/users/*/docs`.

## Open Questions

Ninguna.
