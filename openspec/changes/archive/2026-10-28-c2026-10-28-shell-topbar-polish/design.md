## Context

Feedback post-`c2026-10-28-shell-topbar` (archivado). Hoy: `asideCollapsed` →
`-translate-x-full md:hidden` (desaparece en md+); `AppTopbar` pone locale/stubs en el
mismo flex izquierdo que collapse; `NotesStub` usa `Dialog`; `AsideNavItem.icon` existe
pero no se renderiza. Brief: `docs/prompts/c2026-10-28-shell-topbar-polish.md`.

## Goals / Non-Goals

**Goals:** mini-rail md+; persistencia; móvil off-canvas; flyout B1; logos reducidos;
topbar split; popovers unificados.

**Non-Goals:** dominio stubs; bloque 2; cambiar R-AUT-08 / menú SA; rediseño visual
global.

## Decisions

1. **Dos modos de “colapsado”**  
   - `isMobile` (`max-width: 767px`, breakpoint `md`): colapsado = off-canvas (overlay +
     translate), sin rail.  
   - `!isMobile`: colapsado = rail estrecho (`w-14`/`w-16`), expandido = `w-64`.  
   Alternativa descartada: rail también en móvil (Luis: A = off-canvas móvil).

2. **Persistencia**  
   `localStorage` clave `erp.aside.collapsed` (`'1'|'0'`). Leer al montar AppLayout;
   escribir en cada toggle manual. En móvil, el default al entrar sigue siendo colapsado
   **si no hay preferencia**; si hay preferencia guardada en desktop y el usuario pasa a
   móvil, al volver a md+ se reaplica la preferencia. No sincronizar con servidor.  
   Alternativa descartada: cookie/sesión server (overkill).

3. **Iconos del rail**  
   Mapear `AsideNavItem.icon` (string del catálogo) → componente `lucide-react` con
   fallback genérico (`LayoutGrid` / `Circle`). Renderizar icono también en modo
   expandido (coherencia; no pide quitar labels). Dashboard y grupos usan el mismo mapa.  
   Alternativa descartada: hardcode de slugs sin usar `icon`.

4. **Flyout B1**  
   En rail, ítem con un solo hijo → Link directo. Ítem con N hijos → Popover/hover-click
   anclado al icono (`side="right"`) listando Links. Cerrar al navegar.  
   Alternativa descartada: expandir aside al click (B2) o primer hijo (B3).

5. **Logos reducidos**  
   En rail: una o dos celdas compactas (`h-8`/`h-7` object-contain) con `branding.logo_url`
   y `tenant.logo_url` (si faltan, inicial/abreviado mínimo). No ocultar el bloque entero.

6. **Topbar layout**  
   `justify-between`: izquierda **solo** botón collapse; derecha flex con
   locale → notificaciones → notas → chat → favoritos → empresa → avatar.  
   El “orden L→R” del spec original se interpreta como orden **lógico** del chrome, con
   collapse aislado a la izquierda y el resto del grupo a la derecha (feedback Luis).

7. **Notas → Popover**  
   Sustituir `Dialog` por el mismo patrón que `NotificationsPanel` (Popover bajo
   trigger, cabecera + empty). Chat/favoritos siguen no-op sin panel, o panel vacío si
   ya abren algo — no introducir Dialog centrado.

8. **E2E**  
   N/A. Feature/smoke opcional de persistencia vía jsdom/localStorage si aporta; no
   bloquear DoR.

## Risks / Trade-offs

- **[Riesgo]** Strings `icon` del seeder no coinciden con nombres Lucide → Mitigation:
  mapa explícito + fallback.  
- **[Riesgo]** Preferencia localStorage vs default móvil → Mitigation: documentar en
  tests el comportamiento por breakpoint.  
- **[Trade-off]** Popover click vs hover en rail — preferir **click** (touch-friendly en
  tablet).

## Migration Plan

1. Helper persistencia + refactor estado AppLayout (mobile vs rail).  
2. AsideNav rail + flyout + logos reducidos + iconos lucide.  
3. AppTopbar split + NotesStub Popover.  
4. Tests + calidad.

## Open Questions

Ninguna (A/B/C cerrados en explore).

## Excepciones a standards

Ninguna.

## E2E

No aplica.
