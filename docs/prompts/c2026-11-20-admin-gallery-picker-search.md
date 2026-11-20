<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-20-admin-gallery-picker-search
Rule: 10-architecture
Summary: Fase 2b Galería — buscador e infinite scroll en el selector
  del Wysiwyg (endpoint picker paginado), antes de extraer MediaPicker.
Goals:
- Endurecer el contrato de `GET admin/gallery/picker` con búsqueda
  server-side (`q` sobre name/alt) y paginación `page` + `per_page`
  (tope razonable, p. ej. 24–48) y respuesta `{ assets, meta }`
  (`current_page`, `last_page`, `per_page`, `total`).
- En el modal del selector Wysiwyg: campo buscador (debounce ~300 ms)
  + infinite scroll (sentinel al final del grid del modal).
- Al cambiar `q`: página 1 y sustituir lista (no append); cancelar
  peticiones en vuelo (`AbortController`) al cambiar `q` o cerrar.
- Criterio: solución económica/eficiente y a prueba de Fase 3 (mismo
  contrato JSON reutilizable; no heredar “cargar todos”).
NonGoals:
- Fase 3 `<MediaPicker>` genérico/extraído.
- Cambios en Index `/admin/gallery` (sigue carga completa).
- Upload con `company_id` de empresa.
- Variantes/thumbnails dedicados.
- Insertar en Help / otros campos / URL remota libre.
Skills:
- openspec-propose
- inertia-react-development
- tenancy-rbac
Notes:
- Origen: `/opsx-explore` tras Fase 2 wysiwyg archivada. Layout actual
  del modal OK; el problema es carga completa JSON + miniaturas.
- Perímetro/permisos del picker: mismos que hoy (visibilidad Galería /
  `gallery.view`).
- 10 concreta detalles de API Laravel (LengthAware vs forma exacta de
  `meta`) sin cambiar el alcance acordado.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.
- Tras brief: usuario lanza 10-architecture en sesión nueva.


## ADDED Requirements

### Requirement: Búsqueda y paginación en el listado JSON del picker
El sistema SHALL exponer el listado del selector de Galería con filtro
opcional por texto sobre name/alt y paginación por página, devolviendo
los activos de la página pedida más metadatos de paginación. SHALL NOT
obligar a devolver todos los activos visibles en una sola respuesta.

#### Scenario: Primera página con búsqueda
- **WHEN** el actor autorizado pide el picker con un `q` y página 1
- **THEN** recibe solo activos coincidentes de esa página y meta de
  paginación

#### Scenario: Sin permiso
- **WHEN** un actor sin permiso de ver Galería solicita el picker
- **THEN** recibe denegación (403)


## MODIFIED Requirements

### Requirement: Selector Wysiwyg con buscador e infinite scroll
El modal de selección de imagen del Wysiwyg (Article) SHALL permitir
buscar activos y cargar más resultados al llegar al final del grid
(scroll infinito), usando el picker paginado. Al cambiar la búsqueda
SHALL reiniciar en página 1 sustituyendo la lista.

#### Scenario: Buscar y cargar más
- **WHEN** el actor escribe en el buscador y luego hace scroll hasta el
  final del grid con más páginas disponibles
- **THEN** se muestran resultados filtrados y se añaden los de la
  página siguiente

#### Scenario: Cambio de búsqueda sustituye lista
- **WHEN** el actor cambia el texto de búsqueda tras haber cargado
  varias páginas
- **THEN** la lista se sustituye por la primera página del nuevo
  filtro (no se concatenan resultados del filtro anterior)
