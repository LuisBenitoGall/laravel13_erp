# Proposal

## Why

La ficha de usuario ya gestiona contactos e imágenes; faltan documentos
personales (pdf/word/excel) con almacenamiento privado, descarga autenticada
y límites SaaS. El compartir queda para un change posterior.

## What Changes

- Tab **Documentos** en `Users/Edit` (tras Imágenes): Dropzone + listado,
  descargar, eliminar.
- Schema/modelo `user_docs` / `UserDoc` (docs del User, no de la company);
  softDeletes; disco privado `local` bajo `users/{id}/docs/…`.
- Validación: pdf, doc/docx, xls/xlsx; 20 MB/fichero; máx. 30 docs/user.
- Descarga solo por ruta autorizada (sin URL pública).
- Authz: self o `company-users.update` + perímetro; documento sigue del owner.
- Dominio Admin (Policy/Actions/Requests/controller); reutilizar Dropzone.
- Glosario `UserDoc`; ficha Admin; module-map.

## Capabilities

### New Capabilities

- `user-docs`: documentos personales del User (upload, listado, download
  autenticado, límites; sin share).

### Modified Capabilities

- `user-edit`: tab Documentos usable tras Imágenes.

## Impact

- Migración `user_docs`; modelo/factory; rutas profile + company-users.
- Front: panel tab Documentos; i18n.
- Tests Feature; docs Admin.
- PHP/webserver: `upload_max_filesize` / `post_max_size` ≥ 20 MB (nota ops).
