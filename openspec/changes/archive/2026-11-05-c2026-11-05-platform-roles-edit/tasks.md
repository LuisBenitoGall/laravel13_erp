# Tasks

## 1. Backend

- [x] 1.1 Toggle permiso individual + auto `{module}.access` (grant/revoke).
      Verificar Feature primer/último permiso.
- [x] 1.2 Toggle Functionality completa (acciones canónicas) + comodín.
      Verificar que no arrastra otras funcs.
- [x] 1.3 PUT Update: solo descripción (sin sync matriz). Verificar.
- [x] 1.4 Copy a companies perímetro; versionado `name (n)`; sin CTA lógico
      para universal/SA. Verificar Feature + R-TEN-05 (rol en A ≠ B).
- [x] 1.5 Authz (`roles.update` / SA) en toggles y copy. Verificar 403.

## 2. Frontend Edit

- [x] 2.1 Layout v1 ancho completo (anclas, cabeceras Module, cards
      Functionality colapsables). Verificar UI.
- [x] 2.2 Checkboxes → `patchJson`; master Functionality; fallo → revert +
      toast. Verificar.
- [x] 2.3 Descripción + Guardar; name readonly; CTA Copiar + modal.
      Verificar.
- [x] 2.4 Respetar `permissions_readonly` si aplica. Verificar.

## 3. Docs + tests

- [x] 3.1 Actualizar `docs/modules/Admin.md` (Edit async, copy, layout).
- [x] 3.2 Ampliar/ajustar `PlatformRolesTest` a los escenarios del delta.
      Verificar Pest verde.
- [x] 3.3 E2E / V-07..V-09 nuevas: no aplican.

## 4. Calidad

- [x] 4.1 Pint `--dirty`.
- [x] 4.2 PHPStan / lint front paths tocados.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 / archive.
- Company-roles Edit: change futuro.
