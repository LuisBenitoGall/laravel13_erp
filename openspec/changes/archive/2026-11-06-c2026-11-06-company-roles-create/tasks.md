## 1. Backend Create (metadatos + authz)

- [x] 1.1 Alinear `CompanyRolePolicy::create` (gate `createCompanyRole`) a
      `company-roles.create` sin AND `isCompanyManager`, respetando perímetro
- [x] 1.2 Ajustar FormRequest/store Create: solo name + description opcional;
      no aceptar matriz ni `universal` desde el cliente
- [x] 1.3 Confirmar `CreateCompanyRole` fuerza `company_id` sesión +
      `universal = false` y nombres reservados; `store` redirect a Edit
- [x] 1.4 `create()` Inertia: no pasar props de matriz/modules/permissions

## 2. Frontend Create + CTA

- [x] 2.1 Reescribir `CompanyRoles/Create` al patrón metadatos (sin matriz,
      sin UI/mención universal; reutilizar `RoleMetadataFields` en modo sin
      universal o equivalente)
- [x] 2.2 Verificar CTA «Crear» del Index → `/company/roles/create`
- [x] 2.3 i18n: no introducir copy que explique roles universales en este flujo

## 3. Tests

- [x] 3.1 Feature: Create muestra solo metadatos; POST persiste local
      no-universal; redirect Edit; 403 sin `company-roles.create`
- [x] 3.2 Feature/smoke: CTA Index / ruta create coherentes
- [x] 3.3 Pint + PHPStan + tests afectados en verde

## 4. RETURN 25 — Create→Edit usable

- [x] 4.1 `CompanyRolePolicy::view`/`update`: perímetro + permiso Spatie, sin
      AND manager (Index/delete siguen con manager)
- [x] 4.2 Feature: no-manager con create+update abre y guarda Edit; solo create
      → Edit 403
