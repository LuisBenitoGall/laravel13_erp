# Tasks

## 1. Catálogo matriz

- [x] 1.1 `PlatformRolePermissionCatalog::forMatrix`: permisos BD con prefijo
      `{slug}.` (canónicos + custom); excluir `*.access`; orden canónicos luego
      custom. Verificar que `companies.limpiar-algunas-empresas` sale en props
      Edit / test unitario del catálogo.
- [x] 1.2 `TogglePlatformRoleFunctionality`: set = mismo que matriz (no
      `ACTIONS` fijo). Verificar «Todas las acciones» incluye custom.
- [x] 1.3 Confirmar toggle individual custom + sync `{module}.access`. Verificar
      Feature.

## 2. UI (ajuste mínimo)

- [x] 2.1 Labels de custom (sufijo) en la card; «Todas las acciones» sin cambio
      de UX. Verificar Edit muestra el custom.

## 3. Docs + tests

- [x] 3.1 Nota breve en `docs/modules/Admin.md` (matriz = canónicos + custom).
- [x] 3.2 Feature: custom visible; toggle custom; Todas las acciones con custom.
      Verificar Pest verde.
- [x] 3.3 Company-roles / E2E / V-nuevas: no aplican.

## 4. Calidad

- [x] 4.1 Pint `--dirty`.
- [x] 4.2 PHPStan / lint paths tocados.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 / archive.
- Company-roles: change futuro análogo.
