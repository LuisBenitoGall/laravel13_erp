Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-10-29-server-table-reference
Origen: informe 25-architecture-review (RETURN TO IMPLEMENTATION).
Contrato: openspec/changes/c2026-10-29-server-table-reference/ (proposal, design,
specs, tasks). Si este prompt y los artefactos chocan, mandan los artefactos.
No modifiques proposal.md, design.md ni los deltas de specs/. No archives. No
escribas tests/Browser/. No migres otros Index.

## Cerrar en código

### 1. Canal `filter[]` de la búsqueda avanzada (HIGH)

Ya está decidido. No lo rediseñes.

- design.md D2: el modal escribe al mismo `filter=[{field,type,value}]` que los
  header filters. Merge o replace, declarado en props. No añadas un tercer modo.
- design.md D7 y task 3.1: `AdvancedSearchModal` con campos por props (children
  vale como render prop), `open` / `onOpenChange`, y `onApply(filters)` donde
  `filters` es esa lista.
- Hoy `onApply` es `() => void` y `ServerTable` solo mete en la petición los
  header filters (`filtersRef` dentro de `ajaxURLGenerator`). El escenario
  «Modal aplica filtros» de specs/server-table-reference/spec.md no se puede
  cumplir.
- `ServerTable` debe aceptar esos filtros y mandarlos en el mismo query param
  `filter` del endpoint `table`, también en la descarga Excel/PDF, que ya reenvía
  `filtersRef`.
- Módulos sigue sin botón y sin montar el modal (task 3.3, `showAdvancedSearch`
  omitido). No añadas el CTA en `Admin/Modules/Index.tsx`.
- Documenta la prop de merge/replace en `docs/standards/frontend.md` §3.2 y, si
  hace falta, en `templates-frontend.md`. No cambies el párrafo del toggle ni
  el de «solo Edit».

Archivos: `resources/js/Components/Table/AdvancedSearchModal.tsx`,
`resources/js/Components/Table/ServerTable.tsx`, `docs/standards/frontend.md`,
`docs/standards/templates-frontend.md`, `tasks.md` (solo el checkbox de 3.1).

Hecho cuando: una página que active el modal puede aplicar filtros y la petición
`*/table` lleva esos `{field,type,value}` según la prop merge o replace; el
Index de Módulos no importa `AdvancedSearchModal` ni usa `busqueda_avanzada`.
Desmarca 3.1 hasta entonces. No reescribas el texto de la task.
El test no es de `tests/Browser/`. No vale un `toContain('onApply')` suelto:
tiene que verse el payload `filter` (campo, tipo y valor) que sale hacia el
endpoint.

## No tocar

- El toggle de estado de Módulos (`kind: 'toggle'`, `PATCH .../status`,
  `UpdateModule`, guard de shell). Ni lo quites ni lo amplíes.
- `ModuleController` export, `TableRequest`, `TableBuilder`, columnas del Index,
  i18n de celdas, iconos de Acciones, sort del export.
- Los MEDIUM del informe 25 (sort en Excel/PDF, `rowActions.icon`, enums
  `level`/`status`, slugs de shell en la página, recrear Tabulator al ocultar
  columnas, 302 del PATCH). No bloquean este pase.

## Anotar y devolver a 10 (no lo resuelvas)

El otro HIGH no es implementación. El delta `admin-ui-login` y design.md D3
dicen Acciones de Módulos = solo Edit. El Index, la task 6.13 y `frontend.md`
§3.2 tienen toggle + Edit.

Anótalo bajo la task 6.13 en `tasks.md`, breve, y sigue con el punto 1:

- A) El toggle se queda. Lo enmienda 10-architecture en design.md D3 y en los
  deltas `admin-ui-login` y `server-table-reference`. 20 no toca el Index.
- B) El toggle se quita. 20 revierte el Index y el párrafo de `frontend.md`
  §3.2 a solo Edit, solo cuando 10 o Luis confirmen B.

No elijas A ni B.

## Gate

- Task 3.1 marcada solo con el canal comprobado.
- `vendor/bin/pint --dirty --format agent` y `vendor/bin/phpstan analyse` si
  hay PHP tocado. Si el diff es solo front, no hace falta.
- El test estrecho de este punto, en verde.
- No des el change por listo para Agent 40 ni Agent 30: el HIGH del toggle
  sigue abierto.
