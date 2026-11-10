# Proposal

## Why

El Edit de Usuarios empresa es un stub y Mi cuenta (`/profile`) sigue en máscara.
Hace falta una ficha de usuario real, compartida, con tabs extensibles (Perfil +
Contraseña ahora; Emails/teléfonos y Empresas como huecos), lista para crecer
con cada módulo que cuelgue del user.

## What Changes

- Una página Inertia de Edit de usuario servida por `/profile` y
  `/admin/company-users/{user}/edit`.
- Cabecera: ActionBar + RecordSummary (alta, última act.; rol company sesión
  readonly en contexto company-users).
- `RecordEditLayout` (o equivalente) + tabs extensibles.
- Tab Perfil: columna RO reutilizable (avatar placeholder + datos + emails/tels)
  + form (name, surname, email, `is_public`, birth_date, nif, `status`); submit
  por tab.
- Tab Contraseña: solo self (ni SA cambia la de otro).
- Tabs Emails/teléfonos y Empresas: vacíos.
- Authz: ficha ajena = `company-users.update` + perímetro; self `/profile` sin
  modelar empresa; `is_public`/`status` = self o gancho de permiso TBD.
- Avatar real, CRUD emails/tels/empresas, `is_admin` en form: fuera de alcance.

## Capabilities

### New Capabilities

- `user-edit`: ficha Edit unificada de User (tabs, Perfil, Contraseña self-only,
  resumen RO reutilizable, dos rutas).

### Modified Capabilities

- `company-users-create`: el Edit stub pasa a la ficha unificada.
- `menu-core-pillars`: Mi cuenta / profile deja de ser máscara y abre la ficha
  self.

## Impact

- Controllers Profile + CompanyUser (edit/update profile/password).
- Actions + FormRequests; Policy (update ajeno; password solo self; gancho
  is_public/status).
- Front: `RecordEditLayout`, componente resumen perfil RO, página User Edit,
  i18n.
- Tests Feature self/ajeno/password/permiso.
- `docs/modules/Admin.md` + catálogo `frontend.md` (`RecordEditLayout`).
