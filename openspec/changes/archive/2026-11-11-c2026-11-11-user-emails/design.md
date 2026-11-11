# Design

## Context

See proposal.md — Why. Tab contactos vacío; `user_emails` con `unique(user_id)`
incorrecto; stubs vacíos `UserEmail` / `UserEmailController`. Authz de ficha:
`updateCompanyUser` / profile self; helper `UserProfileAuthorization` solo para
flags.

## Goals / Non-Goals

**Goals:** N emails; UI cards + modal; mailto/copy/delete; schema fix; dominio
Admin; readout sincronizado.

**Non-Goals:** teléfonos; edit in-place; mail app; cambiar email principal.

**E2E:** no aplica (`e2e.md` §4).

## Decisions

1. **Capability `user-emails`**  
   Comportamiento durable de emails adicionales; `user-edit` solo delta del tab.

2. **Migración**  
   Drop index/unique solo sobre `user_id`. Mantener `email` unique global en
   `user_emails`. Validación FormRequest: `email` + `not_in` / Rule comparado
   con `$target->email` (principal).

3. **Authz**  
   Reutilizar la misma puerta que update de perfil de la ficha:
   - Context profile / self: actor === target.
   - Context company / ajeno: Policy `updateCompanyUser` (perímetro +
     `company-users.update`).
   Policy `UserEmailPolicy` (create/delete) delega a esa regla sobre el
   **owner** User (no permiso Spatie nuevo).  
   *Alt. descartada:* permiso `user-emails.*` aparte (brief: misma authz ficha).

4. **Rutas**  
   Nested bajo el user de la ficha, p. ej.:
   - profile: `POST/DELETE` vía user autenticado o `{user}` self-only;
   - company: `admin/company-users/{user}/emails` store/destroy.
   Una Action `CreateUserEmail` / `DeleteUserEmail`; controller
   `Domains/Admin/Http/Controllers/UserEmailController`. Eliminar stub
   `app/Http/Controllers/UserEmailController`.

5. **Modelo**  
   `UserEmail` fillable `user_id`, `email`, `description`; relación
   `User::emails()`. Factory para tests.

6. **UI**  
   Panel tab: grid 2 cols. Izquierda: título + botón abre Dialog (email +
   description); lista cards (email, description si hay; mailto `a[href]`,
   copy `navigator.clipboard`, delete confirm → router.delete). Derecha:
   placeholder i18n teléfonos. Tras mutación: `router.reload` props emails
   (o visit preserve). Readout usa mismos `emails` del page data (cargar desde
   relación en `UserEditPageData`).

7. **Dominio**  
   User es `App\Models\User` (ya usado por Admin); UserEmail en `App\Models`
   o Domains/Admin/Models — seguir convención del repo (hoy stub en
   `App\Models\UserEmail`): completar ahí salvo que Admin Models sea el
   patrón dominante para entidades Admin-only. Preferir **`App\Models\UserEmail`**
   junto a User (entidad de identidad, no catálogo Admin).

## Risks / Trade-offs

- [email unique global] → dos users no pueden compartir el mismo adicional.
  Aceptado (brief).  
- [unique vs principal] → solo validación app (principal no está en
  user_emails). Mitigación: Rule explícita + test.  
- [clipboard] → requiere HTTPS/secure context en algunos browsers; fallo →
  toast error suave.

## Migration Plan

1. Migración drop unique(user_id).  
2. Backend + rutas.  
3. Front tab.  
4. Tests.  
Rollback: re-add unique solo si ≤1 email/user en datos.

## Open Questions

Ninguna.
