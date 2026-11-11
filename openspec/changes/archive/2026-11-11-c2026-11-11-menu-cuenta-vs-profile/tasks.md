# Tasks

## 1. Placeholder Mi cuenta + MenuBuilder

- [x] 1.1 Ruta + página Inertia «próximamente» (cuenta SaaS company).
      Verificar GET autorizado.
- [x] 1.2 `MenuBuilder::hrefFor('profile')` → placeholder (no
      `profile.show`). Verificar menú JSON sin href `/profile`.

## 2. AsideNav + breadcrumb

- [x] 2.1 AsideNav: `/profile*` no marca ningún ítem activo. Verificar UI /
      test.
- [x] 2.2 Breadcrumb `Users/Edit` context profile ≠ `mi_cuenta` menú; i18n
      perfil. Verificar UI.
- [x] 2.3 Avatar sigue enlazando `/profile`. Verificar UserMenu.

## 3. Docs + calidad

- [x] 3.1 Actualizar `docs/modules/Admin.md` (Cuenta vs ficha). Verificar
      fila.
- [x] 3.2 Pint + PHPStan + tests menú/Feature afectados en verde.

## Workflow follow-up

- `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Billing real: change futuro (posible rename slug `profile` → `account`).
