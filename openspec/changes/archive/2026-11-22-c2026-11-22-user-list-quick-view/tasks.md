# Tasks

> Sin entidad BD nueva. E2E §4: no aplica. V-01..V-20: N/A. Tests de authz/
> perímetro en endpoints quick.

## 1. Contenedor y panel UI

- [x] 1.1 Añadir primitiva shadcn `Sheet` en `Components/ui` (canon shadcn;
  sin npm fuera de radix ya listado) y documentar en `frontend.md` §4;
  verificar que el componente renderiza side=right
- [x] 1.2 Crear `UserQuickViewPanel` (Sheet + X + título + Editar opcional +
  `UserProfileReadout`); clic fuera y Escape no cierran; verificar cierre solo
  con X y que `editHref` null oculta Editar

## 2. Backend quick JSON

- [x] 2.1 Helper de payload readout (`UserQuickViewData` o extensión de
  `UserEditPageData`) con user/emails/phones/companies/avatar_url/can_edit;
  verificar shape en unit/feature
- [x] 2.2 `GET company-users/{user}/quick`: authz view + vínculo sesión;
  `can_edit` según update; 403/404 correctos; test aislamiento R-TEN-05
- [x] 2.3 `GET all-users/{user}/quick`: authz `all-users.view` + target
  `is_public`; `can_edit` false; rechazo si no público; test feature

## 3. Indexes

- [x] 3.1 Company-users Index: formatter ojo+nombre, estado panel, fetch
  quick, Editar si can_edit; verificar flujo abrir/cambiar/cerrar
- [x] 3.2 All-users Index: mismo trigger/panel sin Editar; verificar
- [x] 3.3 i18n (ver detalle, usuario, editar si falta) es/en/ca; Pint PHP;
  tests filtrados verdes
- [x] 3.4 Fix: abrir/cambiar ficha rápida NO debe recrear/recargar ServerTable.
  Causa: `rowActions` nueva identidad en cada render del Index → Tabulator
  destroy+ajax. Mitigación: `useCallback`/`useMemo` estables en Indexes
  (company-users y all-users); assert en test de fuente

## Workflow follow-up

- Auditoría 25 → 30-testing (sin 40) → `/opsx-archive`
- Ejecutar `/opsx:apply` para implementar
