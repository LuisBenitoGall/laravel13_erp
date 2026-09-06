## ADDED Requirements

### Requirement: RBAC con Spatie Teams y Super Admin
El sistema SHALL usar Spatie Permission con la función Teams activada (`company_id` como
team key) y un rol Super Admin con `company_id` nulo que bypasa toda comprobación de
permiso (R-AUT-03).

#### Scenario: Super Admin bypasa cualquier permiso
- **WHEN** un usuario con el rol Super Admin accede a cualquier acción protegida por
  permiso
- **THEN** el acceso se concede sin comprobar permisos individuales (`Gate::before`)

#### Scenario: Rol Super Admin sin company_id
- **WHEN** se siembra el rol Super Admin
- **THEN** su `company_id` es NULL, sin necesitar ninguna columna adicional tipo
  "Universal"

#### Scenario: Usuario superadmin de trabajo
- **WHEN** se ejecutan los seeders de la aplicación
- **THEN** existe un usuario cuyas credenciales salen de config/env (no literales en
  código) y tiene asignado el rol Super Admin
