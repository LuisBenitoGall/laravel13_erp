Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-11-user-docs
Origen: informe 25-architecture-review (**APPROVED WITH OBSERVATIONS**).
Estas son correcciones **LOW** (no hay CRITICAL/HIGH). No reabran diseño.
Contrato: `openspec/changes/c2026-11-11-user-docs/`
(proposal, design, specs `user-docs` / `user-edit`).
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni los deltas de specs.
No archives. No escribas tests/Browser/ (E2E N/A). No amplíes
alcance (share, S3, chunked, emails/phones/images, Empresas, Spatie nuevos).

## Contexto

El change está aprobado con observaciones. Backend de upload/download/delete,
disco `local`, perímetro SA y Feature `UserDocsTest` (5/5) están bien.
Este prompt solo limpia los LOW del informe 25.

## Qué hacer (LOW)

### 1. `manage_docs` real (no hardcode `true`)

En `UserEditPageData`, dejar de fijar `'manage_docs' => true`.

Calcularlo con la **misma puerta** que `UserDocPolicy` (self **o**
`updateCompanyUser` sobre el owner), p. ej.:

- `Gate::forUser($actor)->allows('upload', [UserDoc::class, $user])`, o
- helper mínimo reutilizando la lógica de Policy / patrón cercano a
  `UserProfileAuthorization` **solo si encaja sin inventar framework**.

Efecto UI: Dropzone + delete + (tras §2) download solo si el actor puede
gestionar docs. El servidor ya 403; esto alinea props con authz.

**No** exijas cambiar en este prompt `manage_emails` / `manage_phones` /
`manage_images` (siguen el hardcode histórico; fuera de alcance).

Evidencia: ampliar `UserDocsTest` (o assert Inertia en Edit company-users)
con actor **sin** `company-users.update` que abra la ficha ajena si esa
ruta es alcanzable → `can.manage_docs === false`. Si sin `update` no se
puede ni abrir Edit, documenta en el test/comentario y al menos verifica
self → `manage_docs true` y SA/manager con update → true.

### 2. Gating de descarga en UI

En `UserDocsPanel.tsx`, el botón/enlace de descarga hoy está **fuera** de
`canManage` (delete sí está dentro). Spec/design: download = misma authz
que gestionar la ficha.

- Mostrar download solo si `canManage` (o el mismo flag que upload/delete).
- Sin inventar segundo permiso ni UI de “solo lectura docs”.

### 3. Perímetro duplicado (mínimo, solo docs)

`UploadUserDocs` y `DeleteUserDoc` duplican `assertBelongsToCompany`.

- Extrae un helper/trait **mínimo** compartido por esas dos Actions de
  docs (p. ej. método privado en clase support del dominio Admin, o trait
  local `AssertsUserBelongsToCompany`).
- **No** refactorices emails/phones/images en este change salvo que el
  helper quede genérico y el uso en docs sea el único call-site ahora
  (dejar call-sites viejos sin tocar está bien).

### 4. Nota ops PHP fuera de i18n UI

La clave `user_docs_php_ini_nota` en `lang/{es,en,ca}.json` no es copy de
pantalla (ops). Ya está la nota en `docs/modules/Admin.md`.

- Elimina la clave de los tres JSON si no se usa en React/Blade.
- **No** borres la mención ops de `Admin.md`.

## Verificación

```text
php artisan test --compact tests/Feature/Admin/UserDocsTest.php
```

Pint paths PHP dirty (`vendor/bin/pint --dirty --format agent`).
Sin inventar UI/look & feel; solo gating coherente con el panel actual.

## Fuera de alcance

- Share / pivot / bandeja.
- Cambiar disco, límites 20 MB / 30, mimes.
- Unificar `manage_*` de emails/phones/images.
- Archive / 30-testing formal / E2E.
- Reabrir HIGH inexistentes.

## Gate de salida

- [x] `manage_docs` refleja self o `updateCompanyUser` (no `true` fijo).
- [x] Download UI gated igual que mutaciones del panel.
- [x] `assertBelongsToCompany` de docs sin duplicar cuerpo en Upload/Delete.
- [x] Clave i18n ops eliminada; `Admin.md` conserva nota PHP ≥20 MB.
- [x] `UserDocsTest` verde; volver a 25 solo si Luis lo pide (opcional en
      LOW; si no, seguir a 30-testing con observaciones cerradas).
