# Tasks

## 1. Standards (constitución)

- [x] 1.1 Actualizar `docs/standards/kernel.md` (y citas en `architecture.md`
      R-AUT menú) con tabla: `.view` = dashboard/menú; `.viewAny` = detalle;
      AND comodín + `.view`. Verificar diff de standards.
- [x] 1.2 Alinear adaptadores que citen menú+`viewAny`
      (p. ej. `.cursor/rules/01-kernel.mdc`, espejos CLAUDE/AGENTS si aplica).
      Verificar grep sin “menú exige viewAny”.

## 2. Menú + Policies + gates

- [x] 2.1 `MenuBuilder`: Functionality visible con `.view` (no `.viewAny`).
      Verificar Feature menú (escenarios AND).
- [x] 2.2 Policies: `viewAny()` → `can('….view')`; `view()` →
      `can('….viewAny')` en todas las Policies de dominio tocadas. Verificar
      grep Policies.
- [x] 2.3 Controllers/gates ad-hoc Index (`CompanyModuleController`,
      `CompanyMemberController`, etc.): Index/table con `.view`. Verificar
      403 Feature donde existan.
- [x] 2.4 `CompanyRolePolicy`: solo invertir sufijos Spatie; **no** quitar
      AND manager (change 2). Verificar tests company-roles Index no
      regresan salvo el sufijo.

## 3. Tests + calidad

- [x] 3.1 Actualizar tests Feature de menú/visibilidad/catálogos/users que
      den `.viewAny` para Index → `.view` (y show → `.viewAny` si aplica).
      Verificar suite afectada en verde.
- [x] 3.2 Grep residual: `viewAny` usado como gate de Index/menú en app/tests
      (excluir nombres de permiso canónico en matrices/seeders). Verificar
      lista vacía o justificada.
- [x] 3.3 Pint + PHPStan paths tocados en verde.

## Workflow follow-up

- `/opsx-apply` → 25 → 30 / archive.
- **Al cerrar este change: proponer change 2** (hardening roles: Index
  company-roles sin AND manager + `.view`, quitar copy tenant, Create
  plataforma sin `permissions`, materialización `universal=false`, etc.).
