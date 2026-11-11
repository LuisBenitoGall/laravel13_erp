# Design

## Context

See proposal.md — Why. Emails adicionales ya viven en el tab contactos
(`ContactsEmailsPanel`, columna izquierda); la derecha es placeholder.
Authz de ficha = self o `updateCompanyUser` (perímetro +
`company-users.update`), mismo patrón `UserEmailPolicy`. Migration de
referencia aportada en explore (polimórfica + unique owner+e164).

## Goals / Non-Goals

**Goals:** tabla `phones` morph; CRUD User (store/update/destroy + marcar
primario); UI cards/modal; primary único por owner; `country_id` null en
User; catálogo type; Domains/Admin; glosario + ficha Admin.

**Non-Goals:** verificación SMS; UI/API otros phoneables; selector country;
permisos Spatie nuevos; cambiar emails.

**E2E:** no aplica (`e2e.md` §4 — no es flujo crítico listado).

**Excepciones a standards:** ninguna.

## Decisions

1. **Capability `user-phones`**  
   Comportamiento durable de teléfonos de User; `user-edit` / `user-emails`
   solo deltas de layout del tab (derecha deja de ser placeholder).

2. **Schema**  
   Migration = la aportada: `morphs('phoneable')`, `e164(30)`,
   `country_id` nullable FK `countries` nullOnDelete, `ext(10)`,
   `type(32)` default `mobile`, `label(50)`, booleans primary/whatsapp/
   verified, `verified_at`, `notes`, softDeletes, unique
   `phones_owner_e164_unique`, índices primary/type/e164.  
   *Alt. descartada:* tabla `user_phones` no polimórfica (brief: morph día 1).

3. **Modelo**  
   `App\Models\Phone` (identidad / contacto, como `UserEmail`);
   `User::phones()` morphMany. Enum PHP `PhoneType`:
   `Mobile`, `Landline`, `Fax`, `Other` (valores `mobile|landline|fax|other`).
   Factory. `country_id` en creates/updates de User **forzado null** en Action
   (ignorar input si viniera). `is_verified` / `verified_at` no mutables por
   UI (quedan default).

4. **Authz**  
   `PhonePolicy` create/update/delete(/markPrimary) delega a self o
   `UserPolicy::updateCompanyUser` sobre el **owner** User. Sin permiso
   Spatie nuevo.  
   *Alt. descartada:* `user-phones.*` aparte (brief: igual emails).

5. **Actions + rutas**  
   `CreateUserPhone`, `UpdateUserPhone`, `DeleteUserPhone` (soft delete),
   `MarkUserPhonePrimary` (desmarca resto del mismo morph owner en la misma
   transacción). Create/Update si `is_primary=true` → mismo desmarque.
   Controller `Domains/Admin/Http/Controllers/UserPhoneController`.
   Rutas análogas a emails:
   - profile: `POST/PUT|PATCH/DELETE` (+ mark-primary) self;
   - company: `admin/company-users/{user}/phones[...]`.
   FormRequests: e164 required string max 30; unique Rule scoped a
   phoneable (+ ignore self en update); type `Rule::enum(PhoneType)`;
   ext/label/notes nullable; booleans whatsapp/primary.

6. **UI**  
   Extender el panel contactos: columna derecha = cards (borde destacado si
   primario; badges Primario/WhatsApp/No verificado; tipo i18n). Modal
   create/edit (mismos campos). Acciones: `tel:` href, copy clipboard,
   `https://wa.me/<digits>` si `is_whatsapp`, estrella → mark-primary,
   edit modal, delete confirm. Sin acción de verificar.
   Props phones desde `UserEditPageData`; readout consume lista real.

7. **Glosario / module-map**  
   Añadir `Phone` (Teléfono) al glosario. En module-map, listar `Phone` en
   entidades Admin (catálogo de contacto polimórfico; runtime User en este
   change). Ficha `docs/modules/Admin.md`: fila User phones.

8. **Vinculaciones V-01..V-20 (resumen)**  
   Aplica: migración, modelo+factory, Policy, Actions, FormRequests,
   controller/rutas, i18n, props Edit, tests Feature (incl. perímetro
   owner / SA no bypass perímetro como emails). No aplica: menú nuevo,
   Module/Functionality nuevas, BelongsToCompany en Phone (owner morph),
   ServerTable, eventos contables, NumberingService, E2E §4.

## Risks / Trade-offs

- [unique e164 solo por owner] → el mismo número puede existir en dos Users.
  Aceptado (migration aportada).  
- [e164 sin lib libphonenumber] → validación formato ligera (required string);
  normalización profunda fuera de alcance. Mitigación: maxlength 30 + tests.  
- [morph sin UI Company] → riesgo de filas huérfanas conceptuales bajo; sin
  API no se crean.  
- [soft delete + unique] → unique incluye soft-deleted en MySQL a menos que
  se use índice parcial; si choca al recrear mismo e164 tras delete, Action/
  migration puede necesitar unique con `deleted_at` o purge — **mitigación:**
  en implement. verificar comportamiento SoftDeletes+unique; si bloquea
  recrear, documentar y usar unique compuesto con `deleted_at` o hard-check
  solo active (decidir en apply con test; preferir unique solo filas no
  deleted vía índice que MySQL 8 permita, o validación app + soft unique).

## Migration Plan

1. Migración `phones` + enum/modelo/factory.  
2. Backend Policy/Actions/Requests/controller/rutas.  
3. Front columna teléfonos.  
4. Docs + tests.  
Rollback: drop table `phones` (sin datos productivos esperados).

## Open Questions

Ninguna (riesgo soft-delete+unique resuelto en apply con test, sin cambiar
specs).
