# Proposal

## Why

El Index «Usuarios empresa» solo ofrece Crear/Editar como máscara: no se pueden
dar de alta ni vincular users a la company de sesión. Hace falta el Create real
(con rol, perfil público y reglas de email existente) y filtrar «Todos los
usuarios» por perfil público, anticipando nullability de email/password para CRM.

## What Changes

- CTA Crear del Index company-users navega a Create real (`company-users.create`).
- Formulario Create: name, surname, email (obligatorio aquí), status, send
  credentials, `is_public`, selector de rol (universales ∪ locales de sesión;
  `Super Admin` solo si el actor es SA).
- Alta nueva: `is_admin = true`, vínculo a CurrentCompany, rol en team company;
  password solo si checkbox credenciales.
- Email existente: rechazo si ya vinculado a sesión o soft-deleted; si otra
  company → vincular + rellenar huecos + `is_admin → true` + rol de esta
  company; mails credenciales/vinculación = máscara.
- Redirect a Edit stub (cabecera mínima).
- Schema: `is_public` (tras `is_admin`); `email` y `password` nullable.
- Todos los usuarios: filtro `is_public = true`.
- **BREAKING** (listado): users no públicos dejan de aparecer en all-users.

## Capabilities

### New Capabilities

- `company-users-create`: alta/vinculación de User desde Usuarios empresa
  (formulario, reglas de email, rol, schema `is_public`/nullability, Edit stub,
  máscaras de mail).

### Modified Capabilities

- `company-users-index`: Crear deja de ser máscara; Edit de fila puede ir al
  stub.
- `users-directory`: listado filtrado por `is_public = true` (antes sin filtro
  público).

## Impact

- Rutas/controller/policy `CompanyUser*`; Action de create/link; FormRequest;
  páginas Inertia Create/Edit stub; Index CTA.
- Migración `users` (`is_public`, email/password nullable); model/factory/fillable.
- `AllUserController` / query Todos los usuarios.
- Permiso Spatie `company-users.create` (seeder/sync si falta).
- i18n; `docs/modules/Admin.md`; glosario si falta término `is_public`.
- Tests Feature: alta, vínculo, rechazos, filtro all-users, selector SA.
- Sin envío real de mail; sin Edit completo; sin CRM.
