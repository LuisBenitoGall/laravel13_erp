## Why

El catálogo Admin (Module/Functionality, Spatie, MenuBuilder) ya existe, pero sin
`Company` ni pertenencia ni capas de módulos por empresa el ERP no es multi-tenant
usable: `CurrentCompany` es un int sin validar, el menú ignora el plan por empresa y
`EnsureModuleIsActive` sigue en stub. Hay que cerrar la excepción R-AUT-08 parcial y
dar actores reales (User + Company).

## What Changes

- Crear `Company` (shell) en Partners: name, slug, tradename, nif, status, logo;
  soft delete; todas las companies iguales.
- Crear `user_companies` (sin soft delete): acceso al ERP solo con ≥1 empresa activa.
- Post-login: 1 empresa → sesión automática; N → modal bloqueante de selección.
- Super Admin: bypass de permisos/módulos; datos solo de companies vinculadas.
- Tres capas de módulos: (1) Module Active/Inactive global; (2) activación por empresa
  (`company_modules`); (3) elegibilidad autorizado sí/no (Basic por defecto; Optional y
  AdminOnly requieren autorización SuperAdmin).
- UI de módulos de empresa (rejilla tipo v1; no autorizados en gris).
- Endurecer `MenuBuilder` + `EnsureModuleIsActive`.
- Actualizar `module-map`: Admin → Partners (lectura).
- Seed de trabajo: companies + vínculo superadmin + autorizaciones Basic / admin usable.

## Capabilities

### New Capabilities

- `company-tenancy`: Company shell, `user_companies`, sesión/modal post-login, regla Super
  Admin de datos por vínculo.
- `company-module-layers`: elegibilidad + activación por empresa + reglas por
  `ModuleLevel` + UI de tarjetas.

### Modified Capabilities

- `menu-visibility`: reintroduce filtro por módulo Active + autorizado + encendido para
  la empresa de sesión (cierra excepción sin CompanyModules).

## Impact

- Nuevo dominio parcial `App\Domains\Partners` (Company).
- Admin: autorizaciones, `CompanyModules`, MenuBuilder, middleware, seeds, posiblemente
  Functionalities de gestión.
- Support: `SetCurrentCompany` valida pertenencia.
- Frontend: modal selección empresa, CompanySwitcher, página módulos empresa.
- Arch: `Company` en ignoring de BelongsToCompany (es el tenant, no un hijo).
- Fichas: `docs/modules/Partners.md` (nueva); Admin actualizada en alcance.
- `docs/standards/module-map.md`: fila Admin → Partners.
