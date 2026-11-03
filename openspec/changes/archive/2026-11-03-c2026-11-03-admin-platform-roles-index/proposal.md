# Proposal

## Why

El Index de roles de plataforma debe quedar con contrato UI estable (ServerTable +
Acciones Modules-like) y con plantillas `manager`/`guest` **no borrables pero sí
editables** (matriz guardable), sin que guardar la plantilla sustituya el sync del
manager local. Un apply previo reescribió artefactos sin gate 10; este documento
**ratifica** ese contrato como canónico del change.

## What Changes

- Index `/admin/roles`: ServerTable (nombre, descripción, universal) + Acciones
  **Edit** (todas las filas gestionables, incluidas plantillas) + **Delete** solo si
  `can_delete`.
- Plantillas universales `manager`/`guest`: no borrables; matriz asignable/guardable.
- Manager local por company: sigue gobernado por sync de autorizaciones; la plantilla
  no es techo que recorte módulos ya autorizados/activados.
- ActionBar: CTA **Crear rol** stub disabled (Create fuera del DoD de este incremento).
- Super Admin fuera del Index (`platformManaged`).
- Reutilizar backend (`PlatformRoleController`, Delete/Update, Resource); no reinventar.

## Capabilities

### New Capabilities
- (ninguna)

### Modified Capabilities
- `platform-roles`: Index (Edit+Delete condicional, CTA Crear stub); plantillas
  manager/guest con matriz editable; sync manager local intacto al guardar plantilla.

## Impact

- Front: `Admin/Roles/Index.tsx` (Acciones Edit+Delete; Crear stub).
- Backend: `PlatformRoleResource` (`can_delete`, `permissions_readonly=false`);
  `UpdatePlatformRole` guarda matriz también en plantillas.
- Tests: `PlatformRolesTest`.
- Ficha: `docs/modules/Admin.md`.
- E2E: N/A (`e2e.md` §4).
- Sin migraciones; sin tenant/`company-roles`; sin catálogo Spatie Permissions.
