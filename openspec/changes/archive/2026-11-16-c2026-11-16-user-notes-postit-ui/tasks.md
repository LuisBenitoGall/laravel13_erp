# Tasks

## 1. Frontend post-it UI

- [x] 1.1 Restyle Notes en `Dashboard.tsx`: ~140×140, fondo `#ffc`, sombra,
      rotación tipo v1 (nth-child/índice), trash abajo-derecha,
      line-clamp overflow. Verificar visualmente vs captura/CSS v1.
- [x] 1.2 Formato de fecha con hora (`Intl`/`date-fns`). Verificar que el
      post-it muestra día + hora.
- [x] 1.3 Confirmar `package.json` sin deps nuevas de sticky notes.
      Verificar diff deps vacío en este change.
- [x] 1.4 Sin empty state: no mostrar sección «Mis notas» si `notes` vacío
      (spec + Dashboard).

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- R-TEN-05 / V-*: N/A (solo UI; dominio Note intacto).
