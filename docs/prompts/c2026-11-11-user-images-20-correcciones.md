Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-11-user-images
Origen: informe 25-architecture-review (**RETURN TO IMPLEMENTATION**).
Contrato: `openspec/changes/c2026-11-11-user-images/`
(proposal, design Decision 3, specs `user-images` «Un solo featured»,
tasks de Actions/tests).
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni los deltas de specs.
No archives. No escribas tests/Browser/ (E2E N/A en design). No amplíes
alcance (Wysiwyg, docs no-imagen, límites de pago, CRUD Empresas,
auto-featured al subir).

## Problema (HIGH) — soft-delete de featured no libera el hueco

Tab Imágenes, Dropzone kit, upload/límites, toggle `public`, authz
perímetro y avatar shared están bien encaminados. Suite actual
`UserImagesTest` + `ShellTopbarTest` pasa en **SQLite** (9/9).

Falla el contrato de **featured único** tras borrar la featured:

- `design.md` Decision 3: `DeleteUserImage` → soft delete; **si era
  featured → sin featured**.
- Spec `user-images`: «Un solo featured» + schema con columna generated
  `featured_user_id` + unique `user_images_one_featured_per_user`
  (migración `2026_11_11_140337_create_user_images_table.php`).
- `DeleteUserImage` hoy solo hace `$image->delete()` **sin** poner
  `featured = false` antes.
- SoftDeletes deja la fila con `featured = 1` → en **MySQL** la unique
  generated sigue ocupada por la fila soft-deleted.
- `MarkUserImageFeatured` solo desmarca otras vía `$owner->images()`
  (scope sin soft-deleted) → no limpia la featured soft-deleted residual.

Efecto en runtime MySQL (producción/local Laragon): tras eliminar el
avatar featured, marcar otra imagen como featured choca con unique
constraint. En SQLite el ALTER generated va en try/catch y **no**
reproduce el fallo → CI verde engañoso.

## Qué hacer (obligatorio HIGH)

### 1. Cumplir «si era featured → sin featured» en Delete

En `DeleteUserImage`, **dentro de la transacción y antes del soft-delete**:

1. Si `$image->featured` es true, actualizar esa fila a `featured = false`
   (y solo entonces soft-delete + borrar fichero del disco, como ya hace).
2. Tras el delete, el user no debe tener ninguna fila **activa** con
   `featured = true`, y en MySQL no debe quedar ninguna fila (ni
   soft-deleted) con `featured_user_id = user_id` por esa imagen borrada.

No inventes cleanup global ni jobs. No cambies la decisión de borrar el
fichero del disco al soft-delete.

### 2. Cinturón en MarkUserImageFeatured (mínimo)

Al marcar featured, además de desmarcar activas con `$owner->images()`:

- Asegura que no queden otras filas del mismo user (incl. soft-deleted)
  con `featured = true` antes de poner la nueva a true — p. ej.
  `UserImage::withTrashed()->where('user_id', ...)->whereKeyNot(...)->where('featured', true)->update(['featured' => false])`
  o equivalente Eloquent del proyecto.

Objetivo: idempotente y seguro si ya hubiera basura soft-deleted de
deploys previos. Mantén la Action delgada y transaccional.

### 3. Feature que falle en rojo sin el fix (obligatorio)

Ampliar `tests/Feature/Admin/UserImagesTest.php` (mismo estilo Pest):

1. User con ≥2 imágenes; marcar una como featured.
2. Soft-delete (vía ruta destroy) de la **featured**.
3. Assert: la borrada queda soft-deleted y **`featured = false`** en BD
   (withTrashed), y no hay featured activa.
4. Marcar la imagen restante como featured → **200/éxito**; queda como
   única featured activa.
5. Si el entorno de test no tiene la unique generated (SQLite), el assert
   de `featured = false` tras delete + remarcado exitoso sigue siendo
   evidencia del contrato de design; no dependas solo del constraint SQL.

Mantener verdes los escenarios ya existentes (authz perímetro SA A↛B,
límites, public∧is_public, soft delete de no-featured, ShellTopbar
avatar).

### 4. Verificación

```text
php artisan test --compact tests/Feature/Admin/UserImagesTest.php tests/Feature/ShellTopbarTest.php
```

Pint paths PHP dirty (`vendor/bin/pint --dirty --format agent`). No
PHPStan de más fuera de lo tocado.

## UI / estilos

Este HIGH es backend (+ Feature). **No retques** Dropzone, panel,
topbar ni estilos salvo que un test o contrato lo exija de forma mínima
(no debería).

## Fuera de alcance

- Extraer `assertBelongsToCompany` compartido (LOW del informe 25).
- Cambiar `can.manage_images` hardcodeado (mismo patrón emails/phones).
- Auto-featured al subir la primera imagen.
- Cambiar unique `(user_id, image, deleted_at)` o quitar SoftDeletes.
- Reabrir Dropzone kit / i18n / docs salvo typo bloqueante.
- Archive / 30-testing / E2E.

## Gate de salida

- [ ] `DeleteUserImage` deja `featured = false` antes del soft-delete.
- [ ] `MarkUserImageFeatured` no deja featured soft-deleted residual
      bloqueando el unique MySQL.
- [ ] Feature: borrar featured → marcar otra, verde; en rojo sin el fix.
- [ ] Suites `UserImagesTest` + `ShellTopbarTest` verdes; sin inventar UI.
- [ ] Volver a 25-architecture-review (no archive).
