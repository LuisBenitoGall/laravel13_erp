# Design

## Context

See proposal.md — Why. Hoy: `CompanyUsers/Edit` stub; `Profile/Show` máscara;
`RecordEditLayout` pendiente en `frontend.md`; Module Edit ya usa tabs +
`RecordSummary`.

## Goals / Non-Goals

**Goals:** ficha unificada; layout/tabs reutilizables; Perfil usable; Contraseña
self-only; ganchos de permiso; dos rutas.

**Non-Goals:** CRUD emails/tels/empresas; avatar upload; relación empresa en
`/profile`; slug final del permiso is_public/status.

**E2E:** no aplica a flujo canónico `e2e.md` §4 (Feature tests).

## Decisions

1. **Capability `user-edit`**  
   Comportamiento durable de la ficha User, independiente del Index.  
   *Alt.:* meter todo en `company-users-create` — mezcla Create con Edit largo.

2. **Una página Inertia, dos entrypoints**  
   p. ej. `Users/Edit` (o `Admin/CompanyUsers/Edit` reutilizada desde Profile).
   Controllers delgados pasan props + `context: 'profile' | 'company'`.  
   *Alt.:* duplicar páginas — prohibido por brief.

3. **`RecordEditLayout`**  
   Nace en este change: slot cabecera (summary), tablist registrado, panel
   activo. Tabs = array config `{ id, labelKey, visible?, panel }`. Futuros
   módulos añaden entradas sin reescribir la página.  
   *Alt.:* copiar tabs de Module ad hoc — deuda inmediata.

4. **Componente resumen RO**  
   p. ej. `UserProfileSidebar` / `UserProfileReadout`: avatar placeholder,
   name/surname/email/nif/birth_date/status/is_public, listas emails/tels
   (vacías OK). Exportado para reuso.  
   Form a la derecha; submit solo campos del tab Perfil.

5. **Contraseña (campos)**  
   Alineado a v1 partial + Laravel: `password` + `password_confirmation`
   (required, confirmed, reglas min del proyecto). Sin campo “actual” en v1
   admin partial; para self en ficha propia añadimos **`current_password`**
   (required) — evita cambio de sesión robada sin re-auth. Endpoint solo si
   `actor.id === user.id` (Policy/`abort`); SA no bypassa este check (assert
   explícito, no solo Gate::before).  
   *Alt. descartada:* permitir a SA resetear aquí (brief lo prohíbe).

6. **Authz**  
   - Company edit ajeno: `company-users.update` + member en CurrentCompany.  
   - `/profile`: actor === user; sin exigir company-users ni modelar empresas.  
   - `is_public` / `status`: self **o** `Gate::check`/`can` contra permiso
     futuro vía helper/constante placeholder (p. ej. config o método
     `UserProfileAuthorization::canChangeVisibilityFlags`) sin hardcodear slug
     como constitución; hasta definir slug, solo self pasa.  
   - Password: **solo** self (ni SA).

7. **Rol en RecordSummary**  
   Solo en `context=company`: rol Spatie local del user en CurrentCompany
   (readonly). En profile: omitir o null.

8. **Persistencia**  
   Actions `UpdateUserProfile` / `UpdateOwnPassword` (nombres orientativos),
   transaccionales; email unique acotado (ignore self). birth_date/nif ya en
   schema users.

## Risks / Trade-offs

- [Gate::before SA] → podría saltarse Policy de update ajeno; password MUST
  assert `actor.id === user.id` fuera del bypass o con Gate::define que no
  use before. Mitigación: check explícito en Action.  
- [Permiso TBD] → hasta existir slug, solo self cambia flags. Mitigación:
  helper único.  
- [RecordEditLayout nuevo] → coste front; beneficio reuso Module-like.

## Migration Plan

Sin migración de columnas nuevas (birth_date/nif/status/is_public existen).
Despliegue: código + i18n. Rollback: revert pages a stub/máscara.

## Open Questions

Ninguna bloqueante. Slug del permiso is_public/status queda TBD a propósito.
