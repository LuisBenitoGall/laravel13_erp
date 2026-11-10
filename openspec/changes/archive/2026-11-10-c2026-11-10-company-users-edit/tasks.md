# Tasks

## 1. Layout / componentes front

- [x] 1.1 Crear `RecordEditLayout` (cabecera slot + tablist registrable +
      panel). Verificar uso en página User Edit y entrada en
      `docs/standards/frontend.md`.
- [x] 1.2 Componente resumen RO reutilizable (avatar placeholder + datos +
      emails/tels). Verificar render en tab Perfil (columna izq).

## 2. Backend ficha

- [x] 2.1 Unificar render Edit: Profile + CompanyUser edit → misma página
      Inertia; props `context` + user + summary (rol sesión si company).
      Verificar ambas rutas.
- [x] 2.2 Policy/authz: update ajeno `company-users.update` + perímetro; self
      profile sin empresa; password solo `actor.id === user.id` (sin bypass
      SA). Verificar Feature 403.
- [x] 2.3 Helper/gancho authz `is_public`/`status` (self o permiso futuro).
      Verificar self OK; punto de extensión documentado en design/code.
- [x] 2.4 Action + FormRequest update perfil (campos acordados; email unique
      ignore self). Verificar Feature persistencia.
- [x] 2.5 Action + FormRequest password (current + new + confirmed) solo self.
      Verificar Feature self OK / ajeno 403.

## 3. Frontend tabs + i18n

- [x] 3.1 Página Edit: RecordSummary + tabs Perfil / Contraseña / Emails-tels
      (vacío) / Empresas (vacío); Contraseña visible/usable solo self.
      Verificar UI Inertia.
- [x] 3.2 Tab Perfil dos columnas + submit por tab; quitar stub y
      `perfil_proximamente`. Verificar source + Feature.
- [x] 3.3 i18n claves ficha/tabs/errores password. Verificar keys.

## 4. Docs + calidad

- [x] 4.1 Actualizar `docs/modules/Admin.md` (Edit user unificado / profile).
      Verificar fila.
- [x] 4.2 Pint + PHPStan paths tocados; Feature afectados en verde.

## Workflow follow-up

- `/opsx-apply` → 25-architecture-review → 30 archive tras DoD.
- E2E rol 40: no aplica (design).
