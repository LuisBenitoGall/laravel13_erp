# Design

## Context

- Create plataforma: metadatos (`RoleMetadataFields` + Guardar/Cancelar); sin
  matriz; post-store → Edit.
- Create company hoy: `CompanyRoleForm` con matriz; `CreateCompanyRole` ya
  fuerza `universal = false`. Index CTA y redirect Edit ya apuntan bien —
  confirmar en implementación.
- Policy `create`: hoy `isCompanyManager && company-roles.create`. Brief:
  basta `company-roles.create`.

## Goals / Non-Goals

**Goals**

- Create solo nombre + descripción; cero universal en UI/i18n del flujo.
- Persistencia: `company_id` sesión + `universal = false`.
- Authz: `company-roles.create` (+ Gate SA / perímetro existente).
- Redirect Edit; CTA Index → Create.
- Reusar patrón plataforma sin campo universal.

**Non-Goals**

- Edit matriz/async/copy/AdminOnly.
- CRUD plataforma / materialización universales.
- Exponer “universal” al tenant.

## Decisions

1. **UI Create**: página Create tenant con metadatos name/description
   únicamente. Opciones: (A) props `showUniversal={false}` en
   `RoleMetadataFields`, o (B) campos locales sin Switch. Preferir A si el
   componente ya existe; no enviar `universal` en el POST (backend fija false).
2. **Sin matriz en Create**: no cargar `modules`/`permissions` ni montar
   `PermissionMatrix` en Create; Edit sigue como esté.
3. **Policy**: `CompanyRolePolicy::create` (y gate `createCompanyRole`) =
   `can('company-roles.create')` en perímetro company; quitar AND manager si
   aplica. `viewAny`/resto no se rediseñan salvo compile.
4. **Action**: `CreateCompanyRole` mantiene `universal = false` y nombres
   reservados; FormRequest Create sin `universal`/`permissions`.
5. **Redirect / CTA**: `store` → `edit`; Index `href` create — smoke tests.

## Risks / Trade-offs

- Quitar `isCompanyManager` del create amplía quién puede crear si el permiso
  se asigna a roles no-manager → **intencional** (Luis).
- Reutilizar `RoleMetadataFields` sin cuidado podría filtrar copy
  `rol_universal` → ocultar por completo el bloque.

## Migration Plan

N/A esquema. Desplegar código; sin backfill.

## Open Questions

Ninguna (brief cerrado).
