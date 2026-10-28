## 1. Estado aside + persistencia

- [x] 1.1 Refactor `AppLayout`: distinguir móvil (off-canvas) vs md+ (expandido | rail);
      dejar de usar `md:hidden` que oculta el aside
- [x] 1.2 Persistencia `localStorage` (`erp.aside.collapsed`); leer al montar, escribir en
      toggle; documentar interacción con default móvil
- [x] 1.3 Feature/smoke o test de unidad del helper de lectura/escritura si aporta
      (`resources/js/lib/asideStorage.ts` + typecheck)

## 2. Mini-aside (rail) + AsideNav

- [x] 2.1 Mapa `icon` string → Lucide + fallback; pintar iconos en expandido y en rail
- [x] 2.2 Modo rail: logos ERP/company reducidos; un icono por punto principal
- [x] 2.3 Flyout B1 (Popover `side="right"`) para grupos con N hijos; Link directo si 1 hijo
- [x] 2.4 Móvil colapsado: off-canvas sin rail (overlay + translate)

## 3. Topbar + stubs

- [x] 3.1 `AppTopbar`: izquierda solo collapse; derecha locale → notif → notas → chat →
      favoritos → empresa → avatar
- [x] 3.2 `NotesStub`: sustituir Dialog por Popover bajo el icono (mismo patrón que
      `NotificationsPanel`)
- [x] 3.3 Revisar que ningún stub de panel use modal centrado

## 4. Calidad / docs

- [x] 4.1 Ajustar tests de layout/topbar si asumen collapse oculto o Dialog de notas
- [x] 4.2 E2E: N/A
- [x] 4.3 Pint / PHPStan (si toca PHP) / tsc+lint front en verde
- [x] 4.4 Nota breve en ficha Admin o frontend.md: mini-aside + persistencia + popovers
      unificados

## Vinculaciones

- V-01..V-20 / R-TEN-05 / module-map: N/A (chrome UI; sin entidad nueva)
