## Context

Brief: `docs/prompts/c2026-10-28-shell-topbar.md`. Aside cerrado
(`c2026-10-26-shell-navigation`). `AppLayout` hoy: aside fijo `w-64` + header con
`title` + `LanguageSwitcher` + select empresa si N>1 + nombre + logout. Faltan collapse,
stubs chrome y menú avatar. Catálogo `frontend.md`: `NotificationsPanel`,
`CompanySwitcher`. UI: popover + dialog/dropdown aún incompletos en `Components/ui`.

## Goals / Non-Goals

**Goals:** bloque 1 topbar obligatorio en vistas `AppLayout`; collapse aside (móvil
colapsado); stubs notificaciones/notas/chat/favoritos (empty UI); empresa 1 vs N; avatar
Perfil+Logout; perfil placeholder; sin título en topbar; look v2.

**Non-Goals:** bloque 2 (título/ActionBar); dominio Reverb/notas/chat/favoritos; excepción
Tienda; ficha Module nueva; ampliar perímetro companies.

## Decisions

1. **Composición en AppLayout**  
   Extraer `AppTopbar` (o equivalente) que orquesta slots L→R. Estado `asideCollapsed`
   vive en `AppLayout` (useState + media query / `matchMedia` para default móvil). Aside
   y topbar comparten ese estado.  
   Alternativa descartada: persistir collapse en localStorage en este change (follow-up
   OK; no pedido).

2. **Breakpoint móvil**  
   Tailwind `md` (768px): `< md` → aside colapsado por defecto; `≥ md` → expandido. Overlay
   o off-canvas al expandir en móvil (no empujar contenido de forma rota).  
   Alternativa descartada: solo `hidden` sin botón en desktop.

3. **LanguageSwitcher**  
   Adaptar presentación a icono globo + código locale (como v1) reutilizando POST
   `/locale`; mantener en AuthLayout/Welcome si aplica o variante compacta solo topbar.

4. **Stubs**  
   - `NotificationsPanel`: Popover (shadcn) — cabecera, empty i18n, botones refresh / ver
     todo / marcar leídas sin handler de dominio (no-op). Badge `0`.  
   - Notas: Dialog/modal esqueleto (título + empty); badge `0`.  
   - Chat / Favoritos: botones icono; click no-op (empty UI, sin toast).  
   Alternativa descartada: toast «próximamente» (Luis eligió empty UI).

5. **CompanySwitcher**  
   Si `available_companies.length === 1` → texto nombre (`tenant.name`). Si `> 1` →
   select/dropdown shadcn con `POST /companies/switch` (ya existe). No inventar listado
   fuera de `available_companies`.

6. **UserMenu + Perfil**  
   Avatar círculares con iniciales (name / surname si existe en props). Dropdown: Perfil →
   `GET /profile` (Inertia, middleware `auth`+`company`), página lectura name+email +
   mensaje i18n próximamente; Logout → `POST /logout`. Sin FormRequest de edición.  
   Alternativa descartada: ítem Perfil deshabilitado sin ruta.

7. **Título de vista**  
   Dejar de renderizar `title` en el header. Prop `title` puede permanecer en páginas
   (no-op en layout) o usarse solo en `<Head>` document title si ya hay patrón; **no**
   montar bloque 2 aquí. Páginas Index siguen mostrando su propio H1 en `children` donde
   ya lo tengan; si solo dependían del header, 20 añade un H1 mínimo en la página o lo
   anota — preferencia: **document title vía Inertia Head** si es trivial, sin ActionBar.

8. **Primitivas shadcn**  
   Añadir `dropdown-menu` y `dialog` (y `button` si falta) vía CLI shadcn del canon
   `libraries.md` — no inventar wrappers fuera de catálogo. Iconos: lucide-react si ya
   está en el proyecto; si no, SVG inline mínimos (no nueva dep fuera de canon).

9. **Ficha módulo / module-map / E2E**  
   N/A módulo SaaS. Sin edges nuevos en module-map. E2E Pest browser: N/A por defecto
   (chrome stub); Feature cubre perfil + logout + topbar smoke. Si 40 quiere smoke login
   visual, opcional fuera de este DoR.

10. **Obligatoriedad**  
    Todo lo que use `AppLayout` hereda el topbar. AuthLayout / onboarding / select company
    **no** usan este chrome. Excepción Tienda: no implementar; documentar que requerirá
    layout distinto declarado en su change.

## Risks / Trade-offs

- **[Riesgo]** Páginas que solo mostraban título en header quedan sin H1 visible →
  Mitigation: task 20 audita páginas `AppLayout` y añade título en contenido solo si
  hacía falta para usabilidad inmediata (sin ActionBar).  
- **[Riesgo]** Stubs parecen “rotos” → Mitigation: empty copy i18n clara; sin toast.  
- **[Trade-off]** Collapse sin persistencia → UX menos “pegajosa”; aceptable Fase 0.5.

## Migration Plan

1. Primitivas shadcn + componentes topbar.  
2. Refactor AppLayout (collapse + topbar; quitar title del header).  
3. Ruta/página perfil + i18n.  
4. Tests + pint/phpstan/tsc.  
5. Nota breve en `docs/modules/Admin.md` o comentario en `frontend.md` catálogo si tasks
   lo piden.

## Open Questions

Ninguna bloqueante (cerradas en explore: stubs, A1 perfil, empty UI, título fuera).

## Excepciones a standards

Ninguna.

## E2E

No aplica (no toca flujos e2e.md §4 de forma obligatoria). Feature sí.
