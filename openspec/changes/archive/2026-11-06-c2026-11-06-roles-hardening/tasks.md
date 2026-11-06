# Tasks

## Rajataja / Definition of Reject

25 **SHALL** RETURN TO IMPLEMENTATION si:

1. `CompanyRolePolicy::viewAny` (o gate Index/table) sigue exigiendo
   `isCompanyManager` / `hasRole('manager')`.
2. Existe ruta `company.roles.copy`, Action `CopyCompanyRole*`, Request copy
   company, o UI tenant con CTA/modal copiar.
3. `CreatePlatformRole` / `StorePlatformRoleRequest` aceptan o sincronizan
   `permissions` en el alta.
4. Tras materializar, un rol local nuevo puede quedar `universal = true`.
5. Copy plataforma asigna permiso fuera del catálogo filtrado del destino.
6. Mutación tenant sin assert de perímetro (solo Gate::before).
7. Se revierte mapeo change 1 (dashboard con Spatie `.viewAny`).
8. Faltan tests Feature que fallen en rojo ante 1–5.

20 **SHALL NOT** ampliar alcance. Duda → 10 / Luis.

## 1. Authz Index + perímetro Actions

- [x] 1.1 `CompanyRolePolicy::viewAny`: `company-roles.view` + perímetro;
      sin `isCompanyManager`. Verificar Feature Index/table con user no
      manager + `.view` → 200; sin `.view` → 403.
- [x] 1.2 Assert perímetro en Actions tenant de mutación
      (create/update/toggle/delete). Verificar Feature aislamiento /
      rechazo rol otra company.

## 2. Eliminar copy tenant

- [x] 2.1 Quitar ruta `company.roles.copy`, controller method, Action
      `CopyCompanyRole*`, Request. Verificar `route:list` sin ese name.
- [x] 2.2 Quitar CTA/modal/props `can_copy`/`copy_companies` del Edit
      tenant. Verificar source front sin copy.
- [x] 2.3 Actualizar/eliminar tests Feature de copy tenant. Verificar
      suite afectada verde y Rajataja #2.

## 3. Plataforma Create + materialización + copy recorte

- [x] 3.1 `StorePlatformRoleRequest` + `CreatePlatformRole`: sin
      `permissions` en alta (`prohibited` o equivalente). Verificar Feature
      POST con permissions no las asigna.
- [x] 3.2 `EnsureCompanyDefaultRoles`: forzar `universal = false` en
      locales. Verificar Feature materialización.
- [x] 3.3 `CopyPlatformRoleToCompanies`: filtrar con catálogo tenant
      destino. Verificar Feature AdminOnly (u fuera de catálogo) no llega
      al local.

## 4. Docs + calidad

- [x] 4.1 Actualizar `docs/modules/Admin.md` (Index sin manager; sin copy
      tenant; Create plataforma; materialización; copy plataforma
      recortada). Verificar fila coherente.
- [x] 4.2 Grep residual copy tenant / `permissions` en Create plataforma /
      manager en viewAny Index. Pint + PHPStan paths tocados en verde.

## Workflow follow-up

- `/opsx-apply` → **25-architecture-review** (checklist Rajataja) →
  **parada: informe a Luis/explore** → OK producto → `/opsx-archive`.
- Prohibido archive o “roles completos” sin OK post-25.
