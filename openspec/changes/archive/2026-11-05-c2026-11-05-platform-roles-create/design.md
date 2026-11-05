# Design

## Context

Estado v2: Index ServerTable + CTA stub; `PlatformRoleController@create/store`
y `CreatePlatformRole` operativos; `RoleForm` mezcla Create/Edit **con matriz**
en ambos. v1 Create (`admin/roles/create` + `partials/form`): solo name,
description, universal (SA); permisos en Edit; store → redirect edit.

Luis: mismo formulario conceptual globales/tenants; diferencia = permisos
atribuibles después; empezar por globales según v1.

## Goals / Non-Goals

**Goals:**

- Activar Create de roles plataforma desde Index.
- Create = metadatos (name, description, universal); sin matriz.
- Persistencia + redirect Edit; permisos en Edit (ya existente).
- Estructura de UI/form compartible con company roles (sin implementar tenant).

**Non-Goals:**

- Rediseñar matriz de permisos de Edit.
- Cablear Create/Edit company roles a este componente (change posterior).
- Copiar Livewire/Blade/jQuery de v1.
- Cambiar reglas Super Admin / manager / guest / materialización.
- UI de assign roles a users.
- E2E Pest browser.

## Decisions

### D1 — Create sin matriz (v1)

Create muestra: `name` (required), `description` (optional), `universal`
(checkbox/Switch, solo sentido en plataforma). `permissions` opcional en store
→ vacío por defecto (Action ya acepta `permissions ?? []`).

**Descartado:** matriz en Create (estado actual v2) — contradice v1 y el
mensaje «permisos se atribuyen después».

### D2 — Split UI Create vs Edit

- `RoleForm` (o `PlatformRoleFields` + page): Create solo campos metadatos +
  Guardar/Cancelar.
- Edit: metadatos (name readonly en plantillas) + matriz (código actual).
- Extraer campos metadatos a componente reutilizable bajo
  `Pages/Admin/Roles/components/` (o `Components/` solo si company lo usa en
  el mismo PR — preferencia: privado Roles, documentar “listo para company”).

### D3 — CTA Index

ActionBar `rol_crear`: `disabled: false`, `onClick` →
`router.visit('/admin/roles/create')` (o `href` tipado del ActionBar si existe
patrón Modules).

### D4 — Form kit / i18n

Usar controles del catálogo (`Input`, Switch shadcn si ya se usa en Modules;
evitar inputs nativos sueltos donde haya patrón). Claves i18n existentes
(`nombre`, `descripcion`, `rol_universal`, …).

### D5 — Spec delta

- **REMOVED** (o MODIFIED hasta invertir stub): «CTA Crear rol stub…».
- **MODIFIED** escenarios Create del CRUD: alta sin permisos; redirect Edit;
  universal/genérico.

### D6 — Vinculaciones

V-07/V-08/V-09: no (Functionality `roles` ya existe). R-TEN-05 N/A. E2E N/A.
Actualizar ficha `docs/modules/Admin.md` fila Index (quitar stub).

## Risks / Trade-offs

- [Tests que asumen matrix props en Create] → quitar props matrix del render
  Create / tests Inertia.
- [Usuario espera marcar permisos al crear] → Edit inmediato (v1); documentado.

## Migration Plan

N/A datos. Solo UI + tests + spec.

## Open Questions

Ninguna bloqueante (alcance tenant diferido explícito).
