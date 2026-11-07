# Design

## Context

See proposal.md — Why. Hoy: Index company-users con Crear/Editar máscara; sin
create/store/edit; all-users sin filtro público; `users.email`/`password` NOT
NULL; sin `is_public`. `AssignCompanyRole` solo asigna **roles locales** de la
company (tras `EnsureCompanyDefaultRoles`).

## Goals / Non-Goals

**Goals (diseño):** Action transaccional create-or-link; schema; rutas Inertia;
policy `create`; filtro all-users; máscaras flash/toast (no Mail real).

**Non-Goals:** plantillas mail reales; Edit completo; CRM; cancelar vínculo
desde perfil; ampliar Members UI.

**E2E:** no aplica a flujo canónico de `e2e.md` §4 en este change (Feature
tests bastan). Declarado explícitamente.

## Decisions

1. **Capability nueva `company-users-create`**  
   Create/link + schema viven ahí; `company-users-index` y `users-directory`
   solo deltas de CTA/filtro.  
   *Alt. descartada:* meter todo en `company-users-index` (mezcla Index con
   alta).

2. **Action `CreateOrLinkCompanyUser` (nombre orientativo)**  
   Una Action transaccional: resuelve email → create | link | reject;
   perímetro CurrentCompany; no confiar solo en `Gate::before`.  
   *Alt.:* dos Actions create/link — más ruido para el mismo POST.

3. **Selector de rol → asignación**  
   Opciones: plantillas **universales** (`company_id` NULL, `universal`) ∪
   roles **locales** de la company de sesión ∪ `Super Admin` **solo si** el
   actor es SA.  
   - Rol ≠ Super Admin: `EnsureCompanyDefaultRoles` + asignar rol **local**
     homónimo vía `AssignCompanyRole` (o equivalente). Un universal elegido
     se materializa/asegura como local con el mismo `name`.  
   - `Super Admin`: assign con team plataforma (`company_id` NULL / team id
     null); no usar `AssignCompanyRole` local.  
   *Alt. descartada:* listar genéricos de plataforma no universales (brief:
   universales ∪ empresa).

4. **Password / credenciales**  
   Alta nueva: si checkbox → `Str::random` + hash + flash máscara
   credenciales; si no → `password = null`.  
   Link de existente ya `is_admin`: no tocar password ni máscara credenciales.  
   Link con era `is_admin=false` y password null: máscara credenciales (checkbox
   default marcado en UI; generar password si aún null).  
   Mail real: no.

5. **Email nullable (schema) vs obligatorio en Create**  
   Migración: `email` y `password` nullable; unique email se mantiene (varios
   NULL OK en MySQL). FormRequest Create: email `required`. Soft-deleted:
   búsqueda con `withTrashed`; si trashed → conflicto.

6. **`is_public`**  
   Boolean, default `true` en alta de este form; columna tras `is_admin`.
   All-users: `User::query()->where('is_public', true)`. Backfill: existentes
   → `true` (no dejar el directorio vacío tras el filtro).

7. **Rutas**  
   Bajo prefijo admin ya usado: `company-users/create`, `POST company-users`,
   `company-users/{user}/edit` (stub). Index CTA → create; fila Edit → edit
   stub. Authz: `create` → `company-users.create`; `view` stub →
   `company-users.viewAny` o `update` según semántica change 1 (Index
   `.view`; detalle `.viewAny`) — Policy `view` del member con perímetro.

8. **Huecos al vincular**  
   Solo rellenar si el valor actual es null/vacío: `name`, `surname`,
   `status`, `is_public` desde el form. No pisar datos ya presentes.
   Siempre `is_admin = true` al vincular desde este flujo.

9. **Máscaras**  
   Flash/toast keys i18n (credenciales / vinculación / ya vinculado). Sin
   `Mail::` ni jobs de correo en este change.

## Risks / Trade-offs

- [SA asignable por SA] → Bypass global a otro user. Mitigación: selector SA
  solo si actor es SA (debatido).  
- [Filtro all-users] → Users previos sin `is_public` desaparecen. Mitigación:
  backfill `true`.  
- [email/password null] → Login/register siguen exigiendo email; Auth no debe
  asumir password siempre. Mitigación: no romper register; factories
  actualizadas.  
- [AssignCompanyRole solo locales] → Mal mapear universales. Mitigación:
  decisión 3 explícita + tests.

## Migration Plan

1. Migración: nullable email/password; add `is_public` default true; backfill.  
2. Backend Action + routes + policy create/view.  
3. Front Create + Edit stub + Index CTA.  
4. Filtro all-users.  
5. Tests Feature.  
Rollback: revert migración (email/password NOT NULL solo si no hay nulls en
datos).

## Open Questions

Ninguna bloqueante (brief cerrado). Detalle de copy i18n de máscaras lo
elige 20 según claves existentes / nuevas mínimas.
