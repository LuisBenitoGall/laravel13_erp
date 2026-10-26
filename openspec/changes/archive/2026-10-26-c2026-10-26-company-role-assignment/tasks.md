## 1. Backend

- [x] 1.1 Extender `AssignCompanyRole`: validar rol local de la company; quitar otros
      roles locales del user en ese team; asignar el nuevo; rechazar plataforma / otra
      company / no vinculado
- [x] 1.2 `AssignCompanyRoleRequest`: `Rule::in` desde roles `company_id` = company
- [x] 1.3 `CompanyMemberController@index`: props `roles` (locales) + `role` actual
      (cualquier local, no solo manager|guest); actualizar docblock `CompanyRoleGate` si
      aplica

## 2. Frontend

- [x] 2.1 `Members.tsx`: select alimentado por `roles`; mostrar rol actual custom;
      i18n (manager/guest existentes; customs por name/description)

## 3. Registro / docs

- [x] 3.1 Actualizar `docs/modules/Admin.md` (asignación User↔rol local entra; sigue
      fuera invitar miembros / Form kit / Platform)
- [x] 3.2 Vinculaciones: V-07 no nuevos permisos; resto N/A; E2E N/A

## 4. Tests

- [x] 4.1 Feature: asignar custom a miembro vinculado
- [x] 4.2 Feature: reasignar reemplaza rol local previo (un solo rol de company)
- [x] 4.3 Feature: rechazo rol otra company / plataforma / user no vinculado
- [x] 4.4 Feature: authz (sin gate → 403); manager|guest siguen OK
- [x] 4.5 Ajustar tests existentes que asuman solo manager|guest en Members si aplica

## 5. Calidad

- [x] 5.1 Pint + PHPStan + tests del change en verde
