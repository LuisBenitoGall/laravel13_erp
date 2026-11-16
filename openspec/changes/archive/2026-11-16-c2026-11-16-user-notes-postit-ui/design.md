# Design

## Context

See `proposal.md` — Why. Estado actual: `Dashboard.tsx` renderiza Notes
como cards `bg-amber-50` en grid; fecha corta sin hora; trash arriba.
Referencia v1: `ul.notes` (`#ffc`, 140×140, box-shadow, rotate por
nth-child, `small` fecha+hora arriba-derecha, trash abajo-derecha).

## Goals / Non-Goals

**Goals:** Look post-it v1 con CSS/Tailwind; fecha con hora; tamaño ~140×140.

**Non-Goals:** dominio Note; librería npm; drag/edit; favoritos/topbar.

**E2E:** no aplica (solo polish visual).

## Decisions

### D1 — CSS puro (sin librería)

- Clases Tailwind (+ CSS mínimo si hace falta para nth-child rotate).
- Alternativa descartada: `post-it-react` / `feeby` (drag/edit o estética
  RoughJS; no en `libraries.md`).

### D2 — Geometría y layout

- Contenedor ~`h-[140px] w-[140px]` (tipo v1).
- Fondo `#ffc` / `bg-[#ffc]`.
- Sombra tipo `shadow-[5px_5px_2px_rgba(33,33,33,0.7)]`.
- Rotación: `nth-child` / índice (`-6°`, even `4°`, `3n` `-3°`, `5n` `5°`)
  como v1; hover opcional `scale-110` (v1).
- Lista: flex/wrap o float equivalente (no grid rígido de cards).
- Fecha: `Intl` o `date-fns` con día + hora (p. ej. `dd/MM/yyyy HH:mm:ss`
  o locale equivalente).
- Trash: posición absoluta abajo-derecha (hoy está arriba).

### D3 — Overflow (decidido aquí; brief defería a 10)

- Título y cuerpo: `line-clamp` (título 2 líneas, cuerpo resto) +
  `overflow-hidden` para respetar 140×140 sin scroll interno.
- Alternativa descartada: scroll dentro del post-it (rompe look v1).

### D4 — Alcance de código

- Solo `Dashboard.tsx` (y CSS scoped/global mínimo si nth-child no cabe
  limpio en utilities).
- Sin FormRequest, Actions, i18n nuevas salvo que el formato de fecha
  no necesite claves.

### D5 — Empty state (Luis)

- Si `notes.length === 0`: no renderizar la sección (ni «Mis notas» ni
  `sin_notas`). Alta solo vía topbar.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Texto largo (max 1000) ilegible en 140px | line-clamp; detalle completo sigue en BD |
| Rotación + a11y | mantener contraste; trash con `aria-label` existente |

## Migration Plan

- Solo front; sin migración BD. Rollback = revert del commit UI.
