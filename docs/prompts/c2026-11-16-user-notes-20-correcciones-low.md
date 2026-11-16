Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-16-user-notes
Origen: informe 25-architecture-review (**APPROVED WITH OBSERVATIONS**).
Corrección **LOW** (no hay CRITICAL/HIGH). No reabran diseño.
Contrato: `openspec/changes/c2026-11-16-user-notes/`
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni specs.
No archives. E2E N/A.

## Contexto

Modal topbar, post-its, tope 50 y ownership OK (`NoteTest` 6/6).
Queda un LOW: el body `note` en `StoreNoteRequest` es solo
`required|string` (sin `max`), así que se pueden persistir textos
muy grandes pese al tope de 50 notas.

**Decisión Luis:** límite de cuerpo = **1000 caracteres**.

## Qué hacer (LOW)

### 1. Validar `note` max 1000

En `app/Domains/Admin/Http/Requests/StoreNoteRequest.php`:

- `note` → `required`, `string`, **`max:1000`**.
- Mantener `title` → `required`, `string`, `max:150`.

### 2. UI espejo (Textarea)

En `resources/js/Components/Topbar/NotesModal.tsx` (y en el post-it
solo si hubiera input de edición — no hay edit en MVP):

- `maxLength={1000}` en el Textarea del body (o atributo equivalente
  del componente `Textarea` del Form kit).
- Opcional: hint i18n corto del límite (es/en/ca) si encaja sin ruido;
  no obligatorio.

No añadas pantalla Edit ni soft delete. No toques tope 50 ni Policy.

### 3. Feature

En `tests/Feature/Admin/NoteTest.php`:

- Store con `note` de 1001 caracteres → `assertSessionHasErrors('note')`
  y no persiste.
- Store con exactamente 1000 (o ≤1000) sigue OK (puede reutilizar el
  test de alta o uno corto).

## Verificación

```text
php artisan test --compact tests/Feature/Admin/NoteTest.php
```

Pint dirty PHP.

## Fuera de alcance

- Index ServerTable / «Ver todas» distinto de dashboard.
- `company_id` / `status`.
- Notificaciones / chat / favoritos.
- Archive / E2E 40.
- Cambiar el límite a otro valor distinto de 1000.

## Gate de salida

- [ ] `note` max 1000 en FormRequest + Textarea.
- [ ] Feature rechazo 1001 + alta válida verde.
- [ ] Pint OK.
- [ ] Volver a 25 solo si Luis lo pide; si no, seguir a 30-testing.
