# Tasks

## 1. Backend authz, catálogo y metadatos

- [x] 1.1 Policy/gates: rechazar update/toggle/copy/edit de roles `universal`
      (y fuera de CurrentCompany); authz `company-roles.update`. Verificar
      Feature 403/404 por URL.
- [x] 1.2 Extender `CompanyRolePermissionCatalog` con custom del slug (patrón
      plataforma) manteniendo autorizados∩activos∩¬AdminOnly. Verificar set
      allowed incluye custom y excluye AdminOnly.
- [x] 1.3 `UpdateCompanyRole` / FormRequest: solo descripción; name inmutable.
      Descripción de `manager` local permitida; permisos de `manager` no
      mutables. Verificar Feature.
- [x] 1.4 Resource/Index: `can_edit = !universal`; name_readonly en Edit;
      sin CTA Editar en universales. Verificar respuesta table/resource.

## 2. Toggles y copy

- [x] 2.1 Actions + rutas PATCH permiso y Functionality (incl. custom +
      grant/revoke `{module}.access`). Verificar Feature primer/último
      permiso y rechazo en `manager` / universal.
- [x] 2.2 Action + ruta POST copy a companies perímetro; recorte catálogo
      destino; `universal = false`; versionado nombre; R-TEN-05. Verificar
      Feature (colisión, fuera de perímetro, una sola company).

## 3. Frontend Edit

- [x] 3.1 Reescribir `CompanyRoles/Edit` con layout plataforma (anclas/cards);
      descripción + Guardar; name readonly; sin mención universal. Verificar
      render Inertia.
- [x] 3.2 Toggles `patchJson` + master Functionality; optimista; fallo →
      revert + toast; respetar `permissions_readonly`. Verificar comportamiento.
- [x] 3.3 CTA Copiar + modal (≥2 companies); Index oculta Editar si
      `!can_edit`. Verificar UI/props.

## 4. Docs + calidad

- [x] 4.1 Actualizar `docs/modules/Admin.md` (Edit company async/copy/
      universales bloqueados). Verificar fila coherente.
- [x] 4.2 Ampliar Feature company-roles a escenarios del delta; Pint +
      PHPStan/lint paths tocados en verde.

## Workflow follow-up

- `/opsx-apply` → 25-architecture-review → 30-testing / archive.
- E2E Pest browser: no aplica.
