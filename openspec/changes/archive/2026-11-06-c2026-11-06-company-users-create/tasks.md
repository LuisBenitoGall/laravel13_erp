# Tasks

## 1. Datos / schema

- [x] 1.1 Migración: `users.email` y `users.password` nullable; columna
      `is_public` boolean tras `is_admin` (default true + backfill existentes).
      Verificar migrate fresh/test schema.
- [x] 1.2 Actualizar `User` fillable/casts/factory (password/email opcionales;
      `is_admin`/`is_public`/`status`). Verificar factory crea users válidos.
- [x] 1.3 Glosario: término perfil público / `is_public` si falta. Verificar
      entrada en `docs/standards/glossary.md`.

## 2. Backend Create/link

- [x] 2.1 Permiso Spatie `company-users.create` en seeder/sync + Policy
      `create` (perímetro CurrentCompany). Verificar can create / 403.
- [x] 2.2 FormRequest store (email required aquí; role válido en catálogo
      actor; booleans). Verificar validación.
- [x] 2.3 Action create-or-link (transacción): alta nueva; rechazo ya
      vinculado; soft-delete conflicto; link otra company + huecos +
      `is_admin=true` + rol; password solo según reglas; máscaras flash.
      Verificar Feature scenarios del spec.
- [x] 2.4 Asignación de rol: universales→local vía Ensure+AssignCompanyRole;
      Super Admin solo si actor SA (team plataforma). Verificar selector SA
      y assign.
- [x] 2.5 Rutas create/store/edit + controller delgados. Verificar
      `route:list` y 403 sin permiso.
- [x] 2.6 Test aislamiento R-TEN-05 (no vincular/mutar fuera de sesión).
      Verificar Feature rojo/verde.

## 3. Frontend + all-users

- [x] 3.1 Página Create Inertia (campos + defaults marcados + rol). Verificar
      Inertia props/UI.
- [x] 3.2 Edit stub (cabecera). Verificar redirect post-store y GET edit.
- [x] 3.3 Index: CTA Crear → ruta create; Edit fila → edit stub (quitar
      máscaras alta/edición). Verificar source + Feature Index.
- [x] 3.4 All-users table filtra `is_public = true`. Verificar Feature
      listado.
- [x] 3.5 i18n claves Create/máscaras/errores. Verificar keys es (+en/ca si
      el proyecto las exige en el mismo PR).

## 4. Docs + calidad

- [x] 4.1 Actualizar `docs/modules/Admin.md` (Create company-users, is_public,
      filtro all-users, Edit stub). Verificar fila coherente.
- [x] 4.2 Pint + PHPStan paths tocados; suite Feature afectada en verde.
      Verificar comandos.

## Workflow follow-up

- `/opsx-apply` → 25-architecture-review → 30-testing / archive tras DoD.
- E2E rol 40: no aplica (declarado en design).
