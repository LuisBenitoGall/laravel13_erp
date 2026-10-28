## 1. Primitivas UI (shadcn / canon)

- [x] 1.1 Añadir primitivas shadcn faltantes (`button`, `dropdown-menu`, `dialog`) desde el
      canon; no deps fuera de `libraries.md`
- [x] 1.2 Confirmar `lucide-react` usable para iconos del topbar (globo, campana, post-it,
      chat, kebab, menú)

## 2. Backend — perfil placeholder

- [x] 2.1 Ruta `GET /profile` (nombre estable) con middleware `auth` + `company`, controller
      delgado → Inertia `Profile/Show` (o equivalente) con name/email del user
- [x] 2.2 Feature: usuario autenticado con company ve perfil (nombre/email + copy
      próximamente); guest → login

## 3. Frontend — componentes topbar

- [x] 3.1 `CompanySwitcher`: 1 company → texto nombre; N → selector con sesión elegida y
      `POST /companies/switch`
- [x] 3.2 `NotificationsPanel`: campana + badge (0) + popover stub (empty + refresh / ver
      todo / marcar leídas no-op)
- [x] 3.3 Modal/dialog notas stub (post-it + badge 0 + esqueleto empty, sin persistencia)
- [x] 3.4 Botones stub chat y favoritos (empty UI / no-op, sin toast)
- [x] 3.5 `UserMenu`: avatar (iniciales) + dropdown Perfil (href profile) + Logout
      (`POST /logout`)
- [x] 3.6 Variante compacta de idioma en topbar (globo + locale) reutilizando `/locale`

## 4. AppLayout — integración bloque 1

- [x] 4.1 Estado `asideCollapsed` en AppLayout; default colapsado si viewport `< md`;
      botón collapse en topbar; aside responde (off-canvas/overlay en móvil)
- [x] 4.2 Montar topbar L→R según spec; **quitar** render del `title` en el header
- [x] 4.3 Auditar páginas `AppLayout`: si el único título visible era el del header, añadir
      H1 mínimo en `children` o `<Head>` document title — **sin** ActionBar (bloque 2)
- [x] 4.4 i18n snake_case es/en/ca para claves nuevas del topbar/perfil/stubs

## 5. Tests y calidad

- [x] 5.1 Feature: topbar/perfil/logout (y switch empresa si hace falta aserción HTTP ya
      cubierta — no duplicar)
- [x] 5.2 Ajustar tests que asuman título en header o logout como botón suelto
- [x] 5.3 E2E Pest browser: N/A (declarado en design; sin flujo nuevo).
      Login Browser existente alineado al avatar: «Salir» y el nombre ya no están sueltos
      (`LoginWithOneCompany`, `LoginWithMultipleCompanies`, `LoginAsManagerSeesBasicMenu`,
      `RegisterOnboardEntersApp`).
- [x] 5.4 `vendor/bin/pint`, PHPStan, `tsc`/lint front en verde en lo tocado

## 6. Docs / registro

- [x] 6.1 Nota breve en `docs/modules/Admin.md` o catálogo `frontend.md`: topbar bloque 1 +
      stubs; bloque 2 pendiente
- [x] 6.2 Vinculaciones V-01..V-20: N/A entidad; i18n sí (4.4); R-TEN-05 N/A; module-map sin
      cambio
