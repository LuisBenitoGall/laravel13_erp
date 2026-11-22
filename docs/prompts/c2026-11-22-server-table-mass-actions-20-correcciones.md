Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-22-server-table-mass-actions
Origen: informe 25-architecture-review (**APPROVED WITH OBSERVATIONS**).
Corrección **MEDIUM** (+ LOW opcional). No reabran diseño.
Contrato: `openspec/changes/c2026-11-22-server-table-mass-actions/`
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni specs.
No archives. E2E N/A.

## Contexto

ServerTable `massActions` + piloto Usuarios empresa OK
(`CompanyUsersBulkTest` 7/7). Queda un MEDIUM de authz: los endpoints
bulk no usan la misma Gate Policy que la fila.

Hoy:

- Fila: `$this->authorize('deleteCompanyUser', $user)` /
  `$this->authorize('updateCompanyUser', $user)` → `CompanyUserPolicy`
  (actor en CurrentCompany + miembro vinculado + Spatie).
- Bulk: `abort_unless($request->user()?->can('company-users.delete|update'), 403)`
  → solo Spatie; omite `belongsToCurrentCompany` del actor.
  Los ids ajenos ya se **skip**ean en `BulkDetachCompanyUsers` /
  `BulkUpdateCompanyUserStatus` (R-TEN-05 OK).

**Decisión (25 / Luis):** alinear authz bulk a la misma Policy de fila
(`deleteCompanyUser` / `updateCompanyUser`), sin cambiar el contrato de
selección página ni las rutas.

## Qué hacer (MEDIUM)

### 1. Authz bulk = Policy de fila

En `app/Domains/Admin/Http/Controllers/CompanyUserController.php`
(`bulkDetach` / `bulkStatus`):

- Sustituir `abort_unless(…->can('company-users.*'), 403)` por autorización
  vía Gate Policy, coherente con destroy / updateStatus.
- Patrón preferido (elige el mínimo que conserve semántica de fila):

  **A (preferido):** para cada id validado, resolver el `User` y llamar
  `$this->authorize('deleteCompanyUser', $member)` (detach) o
  `updateCompanyUser` (status). Si algún id no pasa Policy → 403 (no
  aplicar el lote a medias). Ids inexistentes: ya filtrados por
  FormRequest `exists:users,id`.

  **B (aceptable si A resulta ruidoso):** una sola autorización de
  “capacidad” equivalente a Policy sin modelo — p. ej. comprobar
  pertenencia del **actor** a CurrentCompany + Spatie
  `company-users.delete|update` (mismos predicados que
  `CompanyUserPolicy::belongsToCurrentCompany` + `can`), y dejar el skip
  de miembros ajenos en la Action. Documentar en comentario de 1 línea
  por qué no se itera Policy por id.

- No inventar abilities Gate nuevas; reutilizar
  `deleteCompanyUser` / `updateCompanyUser` ya definidos en
  `AppServiceProvider`.
- Mantener Actions: skip de ids no vinculados a la company de sesión
  (perímetro R-TEN-05).

### 2. FormRequests (LOW, mismo PR)

En `BulkDetachCompanyUsersRequest` / `BulkUpdateCompanyUserStatusRequest`:

- Hoy `authorize(): true`. Mover la authz al FormRequest **o** dejarla en
  el controller tras A/B, pero no duplicar checks contradictorios.
- Opcional endurecer R-TEN-07: regla custom / `Rule::exists` acotada a
  users vinculados a CurrentCompany. Si lo haces, ids ajenos → 422
  (reject) en lugar de skip silencioso; **actualizar el Feature
  R-TEN-05** en consecuencia (assert errors o assert no-op — coherente
  con la opción elegida). Si no endureces Request, deja el skip en Action
  y no cambies el assert de aislamiento (sigue verde).

### 3. Tests

En `tests/Feature/Admin/CompanyUsersBulkTest.php` (o extensión):

- Mantener: detach no borra User; status; R-TEN-05; 403 sin permiso;
  validación `ids`.
- Añadir o ajustar: actor **sin** vínculo a CurrentCompany pero con
  permiso Spatie (si es construible en test) → 403 en bulk (cubre el
  hueco Policy). Si el harness no permite ese escenario, documentar en
  comentario del test por qué y cubrir al menos que bulk sigue
  respondiendo 403 sin `company-users.delete|update`.
- Assert source Index/ServerTable **no** hace falta tocarlos salvo
  regresión.

## Verificación

```text
php artisan test --compact tests/Feature/Admin/CompanyUsersBulkTest.php
vendor/bin/pint --dirty --format agent
```

PHPStan limpio en PHP tocado si el proyecto lo exige en el gate 20.

## Fuera de alcance

- Select-all filtrados / cross-page.
- Mass actions en otros Indexes.
- Cambiar semántica status User global vs pivot.
- UI ServerTable (checkbox/dropdown) salvo rotura.
- Archive / E2E 40.
- Reescribir OpenSpec.

## Gate de salida

- [ ] Bulk detach/status autorizan vía Policy (o equivalente B documentado)
  alineado a fila.
- [ ] R-TEN-05 + 403 sin permiso siguen verdes; test nuevo/ajustado del
      hueco actor↔company si aplica.
- [ ] Pint (y PHPStan si toca) limpios en PHP dirty.
- [ ] Volver a 25 solo si Luis lo pide; si no, seguir a 30-testing.
