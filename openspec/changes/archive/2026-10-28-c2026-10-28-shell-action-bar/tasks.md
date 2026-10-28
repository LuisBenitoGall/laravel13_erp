## 1. Origen + ConfirmDialog + ViewActionBar

- [x] 1.1 Helper/hook de URL de origen (sessionStorage + navegación Inertia); sin origen →
      no Volver
- [x] 1.2 `Feedback/ConfirmDialog` (si no existe) para delete
- [x] 1.3 `ViewActionBar`: array discriminado extensible; CTAs icon+label i18n; Volver
      antepuesto; delete último con gap mínimo; tipar dropdown sin UI
- [x] 1.4 `AppLayout`: prop `actions`; render bajo breadcrumb

## 2. Migración páginas AppLayout

- [x] 2.1 Indexes: mover Crear (y CTAs equivalentes) a `actions` tipo `link`;
      `showBack={false}` en Index (sin Volver salvo excepción explícita)
- [x] 2.2 Create/Edit/Profile/Dashboard/Members/Authorizations: declarar `actions` (puede
      ser vacío); no inventar delete sin ruta
- [x] 2.3 i18n (`volver`, confirm delete, etc.) es/en/ca
- [x] 2.4 Actualizar `frontend.md`: ViewActionBar vs FormActions vs Records/ActionBar

## 3. Tests y calidad

- [x] 3.1 Feature/smoke: Index Modules → Crear en ActionBar; Create desde Index → Volver
      visible; sin origen → sin Volver
- [x] 3.2 E2E: N/A
- [x] 3.3 Pint / PHPStan (si toca) / tsc+lint front en verde

## Vinculaciones

- V-01..V-20 / R-TEN-05 / module-map: N/A (chrome UI)
