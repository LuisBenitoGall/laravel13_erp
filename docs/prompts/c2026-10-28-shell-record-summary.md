<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-28-shell-record-summary
Rule: 10-architecture
Summary: Componente fijo de ficha de registro (card blanca, bordes redondeados) con
    título + identificador (badge) + array de campos i18n; para edit/show y similares, no
    index/create. Solo el componente; sin totales ni acciones inline tipo lápiz.
Goals:
- Card bajo la ActionBar (bloque de resumen del ítem): fondo blanco, bordes redondeados,
  coherente con shell v2 y estructura de la captura v1 (pedido de venta).
- Cabecera: título de lo que se está editando/mostrando (p. ej. “Pedido de venta”) +
  identificador en badge (p. ej. numeración `2026/00/002214`).
- Campos vía array declarativo (varía por vista); labels i18n. Campos frecuentes a
  contemplar en el contrato/helpers: created_at, created_by, updated_at, updated_by
  (no obligatorios en todas las vistas).
- Soporte tipado para valores con énfasis de estado (p. ej. Estado “Pendiente” con tono).
- Aplica a edit, show u otras vistas similares; **no** a index ni create “en principio”.
- Show vs Edit: el contenido puede variar según el caso (misma familia de componente,
  no forzar layout idéntico obligatorio).
- Alcance: **solo el componente** (+ catálogo frontend); no migrar todas las Edit
  existentes en este change.
NonGoals:
- Box de totales (Importe / IVA): no es estructura fija; pocas vistas.
- Acción inline lápiz junto a un campo (p. ej. editar cliente en pedidos/presupuestos no
  facturados): función específica, change posterior.
- Migración masiva de páginas Edit/Show actuales.
- ActionBar, breadcrumb, topbar (ya tratados).
- Inventar dominio de pedidos/ventas.
Skills:
- frontend-design
- laravel-specialist
Notes:
- Posición en el chrome: tras ViewActionBar, antes del contenido/formulario.
- Referencia visual: card “Pedido de venta” + badge naranja de número + fila Cliente, NIF,
  Estado, Creado, Autor, Comercial…
- Catálogo: relacionar con `Records/RecordEditLayout` / cabecera de registro; 10 nombra
  el componente (p. ej. RecordSummary).
- i18n snake_case; cero literales de UI.


## ADDED Requirements

### Requirement: Componente Record summary (card)
El sistema SHALL proveer un componente de ficha de registro en card blanca con bordes
redondeados que muestre un título, un identificador en badge, y una lista de campos
declarada por array (label i18n + valor), con soporte para tono de estado cuando aplique.
SHALL contemplar en el contrato el uso habitual de created_at, created_by, updated_at y
updated_by sin exigirlos en toda vista. SHALL pensarse para vistas edit/show (u otras
similares), no para index ni create. SHALL NOT incluir box de totales ni acciones inline
tipo lápiz en este change. SHALL NOT migrar todas las páginas Edit existentes: solo el
componente (y registro en catálogo).

#### Scenario: Card con título, badge y campos
- **WHEN** una página Edit/Show pasa título, identificador y array de campos al componente
- **THEN** se renderiza la card con título, badge del identificador y los pares
  label/valor en fila

#### Scenario: Campo de estado con tono
- **WHEN** un campo se declara con tono de estado
- **THEN** el valor se muestra con énfasis visual de estado (p. ej. color distinto)

#### Scenario: Sin totales ni lápiz
- **WHEN** se usa el componente en este change
- **THEN** no aparece box de totales ni iconos de acción inline por campo


## MODIFIED Requirements

### Requirement: (frontend catálogo / RecordEditLayout)
Registrar el componente de ficha de registro en el catálogo `frontend.md` y alinearlo
como pieza de cabecera de edit/show, sin implementar aún el esqueleto completo
RecordEditLayout ni dominio de ventas.
