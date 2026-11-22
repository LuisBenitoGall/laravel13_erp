Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-22-foundation-hardening
Origen: informe 25-architecture-review (**RETURN TO IMPLEMENTATION**).
Corregir **HIGH** (obligatorio) + **MEDIUM** (obligatorio) + LOW opcionales.
No reabran diseño de F-01/F-02/F-03/F-05 (disco `local`, login status,
desactivar global). Solo cierren la entrega autenticada de avatares en
consumidores quick-view / lectura RO.
Contrato: `openspec/changes/c2026-11-22-foundation-hardening/`
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni specs salvo que 10 lo pida.
No archives. E2E N/A. F-11 Browser/Playwright fuera de alcance.

## Contexto

Storage privado + rutas `*.file` + login `status` + copy desactivar OK
(Feature 37/37). 25 devolvió el change por regresión de avatares:

Hoy:

- `UserImage::url('profile'|'company')` → rutas autenticadas (bien).
- `UserQuickViewData` **siempre** hace `$featured->url('company')`.
- AllUsers quick (`viewAnyAllUser` + `is_public`) emite esa URL.
- GET `company-users/{user}/images/{userImage}/file` exige
  `UserImagePolicy::view` → self **o** `updateCompanyUser` + vínculo a
  CurrentCompany → actor all-users / user público ajeno → **403**.
- Mismo Policy: quick-view company-users RO (`can_edit: false`, sin
  update) puede ver ficha y avatar roto.

**Decisión (25):** la entrega del binario en contextos de **lectura**
(quick-view / ficha RO) MUST alinearse al permiso de **ver** ese
contexto, no solo al de mutar. Sin reabrir GET anónimo ni F-04 PII.

## Qué hacer (HIGH)

### 1. Avatar all-users quick-view

En `UserQuickViewData` + entrega UserImage:

- Dejar de forzar `url('company')` para todos los callers.
- Patrón preferido:

  **A (preferido):** `UserQuickViewData::build(..., string $imageContext)`
  (o enum/`'profile'|'company'|'directory'`) y callers:

  - CompanyUsers Index / Edit company → `'company'`.
  - Profile / self → `'profile'` (si aplica).
  - AllUsers quick → contexto **directory** (nombre a tu criterio).

  Añadir ruta autenticada p. ej.
  `GET /admin/all-users/{user}/images/{userImage}/file` (nombre de ruta
  coherente con el resto) con:

  - authz: `viewAnyAllUser` (o Policy equivalente) **y**
    target `is_public` (mismo espíritu que `AllUserController::quick`);
  - `userImage.user_id === user.id`;
  - stream desde disco `local` (mismo helper que profile/company).
  - `UserImage::url('directory'|…)` apunta a esa ruta.

  **B (aceptable):** sin ruta nueva — ampliar `UserImagePolicy::view` (o
  Gate en el controller company/profile) para permitir, además de self /
  `updateCompanyUser`, lectura cuando el actor puede
  `viewAnyAllUser` **y** el owner es `is_public`. Entonces all-users
  puede seguir usando una URL existente **solo si** el perímetro no
  exige vínculo a CurrentCompany (hoy `fileForCompanyUser` hace
  `abort_unless` vínculo → B implica no usar esa ruta para all-users, o
  relajar perímetro solo en una ruta dedicada). Si B no puede cumplir
  sin romper perímetro company-users, usar **A**.

- Tests Feature (obligatorio):

  - Guest → no 200 binario en la nueva (o ajustada) URL directory.
  - Actor con `all-users.view`, user `is_public`, imagen featured → 200.
  - User no público → 403/404.
  - `UserQuickViewData` / AllUsers quick: `avatar_url` no contiene
    `/storage/` y la ruta responde 200 para ese actor.
  - Company-users file: perímetro ajeno sigue 403.

## Qué hacer (MEDIUM)

### 2. Lectura RO en company-users (avatar quick-view)

`UserImagePolicy::view` (y el file company-users) SHALL permitir al actor
que puede **ver** la ficha en Usuarios empresa, no solo mutarla.

- Sustituir / ampliar el predicado: self **o**
  `viewCompanyUser` (detalle) **o** el mínimo Gate ya usado para abrir
  quick-view / show — **no** exigir `updateCompanyUser` solo para
  stream del binario.
- Mutaciones (upload/delete/featured/public) siguen exigiendo la puerta
  de update / `canManageOwner` actual.
- Test: actor company-users con view/viewAny **sin** update → GET file
  del miembro de sesión → 200; sin permiso view → 403.

## Qué hacer (LOW, mismo PR si cabe)

### 3. Galería file vs Index (opcional)

`MediaAssetController::file` usa Policy `view` → Spatie `gallery.viewAny`;
Index/picker usan `viewAny` → `gallery.view`. Si un rol solo tiene
`.view`, thumbnails rotas. Alinear file a la misma capacidad que Index
(`viewAny` / `gallery.view`) **o** documentar en comentario de 1 línea
por qué file exige detalle. Test mínimo si cambias authz.

### 4. Comando migrate (opcional)

Pest Feature o unit del comando `erp:migrate-public-media-to-local`
(dry-run / copia idempotente con `Storage::fake`). No bloquea el HIGH.

## Verificación

```text
php artisan test --compact tests/Feature/Admin/UserImagesTest.php tests/Feature/Admin/GalleryTest.php tests/Feature/Auth/LoginTest.php tests/Feature/Admin/CompanyUserDeactivateLoginTest.php
vendor/bin/pint --dirty --format agent
```

Añade/ajusta el fichero de test que cubra all-users image file + Policy
RO. No uses `--filter=Login` a secas (arrastra Browser). PHPStan limpio
en PHP tocado si el gate 20 lo exige.

## Fuera de alcance

- GET anónimo / perfiles públicos sin login (F futuro).
- F-04 PII quick-view, F-06 scope galería, F-07–F-11.
- Reabrir disco `public`, login status, copy desactivar.
- Wysiwyg HTML histórico con `/storage/…`.
- Archive / E2E 40 / reescribir OpenSpec.

## Gate de salida

- [ ] All-users quick: `avatar_url` usable (200) para actor con
      `all-users.view` sobre user `is_public`; guest denegado.
- [ ] Company-users: file OK con permiso de **ver** ficha (sin exigir
      update); perímetro ajeno 403; mutaciones intactas.
- [ ] Sin `/storage/` en URLs de imagen user / gallery tocadas.
- [ ] Feature listados arriba (+ tests nuevos) verdes; Pint dirty OK.
- [ ] Volver a **25-architecture-review** (reauditoria) tras el fix.
