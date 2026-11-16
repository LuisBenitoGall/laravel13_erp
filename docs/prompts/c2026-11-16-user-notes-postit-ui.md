<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-16-user-notes-postit-ui
Rule: 10-architecture
Summary: Ajustar el aspecto de los post-its del Dashboard para
  asemejarse a v1, con CSS/Tailwind puro (sin librería), tamaño tipo
  v1 y fecha con hora.
Goals:
- Post-its del Dashboard con look v1: fondo tipo `#ffc`, tamaño ~140×140,
  sombra, rotación ligera (patrón nth-child / índice), fecha arriba-derecha
  con hora, título, cuerpo, trash abajo-derecha.
- Implementación solo CSS/Tailwind (sin dependencia npm nueva).
NonGoals:
- Librería de sticky notes (`post-it-react`, `feeby`, etc.).
- Drag / edición inline / nueva librería en `libraries.md`.
- Cambios de dominio Note (tope 50, ownership, modal alta, `company_id`).
- Notificaciones, chat, favoritos.
Skills:
- openspec-propose
- v1-reference
- inertia-react-development
- tailwindcss-development
Notes:
- Origen: `/opsx-explore` + captura v1 + CSS v1 `ul.notes` en
  `laravel8_erp_viudavila` (`background: #ffc`, 140×140, rotate, shadow).
- Decisión Luis: CSS puro (no librería); tamaño tipo v1; fecha **con hora**.
- `c2026-11-16-user-notes` ya archivado; este change es polish visual del
  Dashboard (`resources/js/Pages/Dashboard.tsx`).
- Overflow de textos largos dentro de ~140×140: no decidido en explore;
  decide 10-architecture (ellipsis / scroll / line-clamp).
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Look post-it estilo v1 en Dashboard
El Dashboard SHALL mostrar cada Note como post-it visualmente cercano a
v1: fondo amarillo tipo `#ffc`, tamaño aproximado 140×140, sombra,
rotación ligera entre notas, fecha con hora arriba-derecha, título y
cuerpo, acción de borrado abajo-derecha. SHALL usar solo CSS/Tailwind
(SHALL NOT añadir dependencia npm de sticky notes).

#### Scenario: Aspecto post-it
- **WHEN** el actor abre el dashboard con notas
- **THEN** ve post-its con tamaño/color/sombra/rotación tipo v1 y fecha
  con hora

#### Scenario: Sin librería
- **WHEN** se implementa el look
- **THEN** no se añade paquete npm de post-its; solo estilos del proyecto


## MODIFIED Requirements

### Requirement: Post-its en el Dashboard
(El requisito existente de post-its fecha/título/cuerpo/delete.) SHALL
actualizar el formato de fecha para incluir hora y el estilo visual
hacia el patrón v1 descrito arriba, sin cambiar create/delete ni el
modelo.

#### Scenario: Ver y borrar
- **WHEN** el actor tiene notas y abre el dashboard
- **THEN** ve los post-its con look v1 (incl. fecha con hora) y puede
  eliminar una nota propia
