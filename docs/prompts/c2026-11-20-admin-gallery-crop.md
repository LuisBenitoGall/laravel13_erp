<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-20-admin-gallery-crop
Rule: 10-architecture
Summary: Fase 1b de Galería — recortar/redimensionar una imagen y
  guardar el resultado como nuevo MediaAsset (copia), sin parent_id;
  original intacto.
Goals:
- Desde cada card de `/admin/gallery`, acción de recortar/redimensionar
  → modal → al guardar se crea un **nuevo** `MediaAsset` (copia).
- El original permanece sin cambios.
- La copia **hereda** `name` y `alt` del original (editables después
  con el flujo ya existente).
- Sin `parent_id` / linaje entre original y copia.
- Crop en **cliente**; librería adecuada al stack Inertia/React 19
  (elige 10; justificar e incluir en `libraries.md` en el mismo PR).
NonGoals:
- Fase 2–3: insertar desde Wysiwyg / `<MediaPicker>` genérico.
- Thumbs automáticos multi-medida estilo WordPress.
- `parent_id` u otro vínculo de linaje.
- Desbloquear upload no-SA / `company_id` de empresa / otra URL.
- Sustituir/sobrescribir el fichero del original.
- Vídeos en servidor.
Skills:
- openspec-propose
- inertia-react-development
- tenancy-rbac
Notes:
- Origen: `/opsx-explore` tras Fase 1 (`c2026-11-20-admin-gallery`) ya
  implementada (grid, multi-upload, name/alt, URL, sin Volver).
- Fases: 1 hecha → **este brief = 1b** → 2 Wysiwyg → 3 picker.
- Explore: crop cliente por eficiencia; Luis: librería “adecuada a la
  tecnología”; 10 elige (p. ej. candidata habitual `react-easy-crop`)
  y canoniza en `libraries.md`.
- Perímetro / `company_id` null: misma política que Fase 1 (copia
  plataforma en este flujo Configuración).
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.
- Tras brief: usuario lanza 10-architecture en sesión nueva (explore
  no invoca).


## ADDED Requirements

### Requirement: Recortar/redimensionar como copia
El sistema SHALL permitir, desde un MediaAsset visible en el Index de
Galería, abrir un modal de recorte/redimensión y, al confirmar, crear
un nuevo MediaAsset con el fichero resultante. SHALL NOT modificar el
fichero ni los metadatos del original en esa operación.

#### Scenario: Guardar copia recortada
- **WHEN** el actor autoriza el recorte de un activo y confirma guardar
- **THEN** existe un nuevo MediaAsset con URL propia y el original
  sigue listado e intacto

### Requirement: Herencia de name y alt
La copia creada por recorte/redimensión SHALL inicializar `name` y
`alt` con los valores del original.

#### Scenario: Metadatos de la copia
- **WHEN** se crea la copia desde un activo con name y alt dados
- **THEN** la copia nace con el mismo name y el mismo alt


## MODIFIED Requirements

_(ninguno obligatorio — 10 enlaza con `admin-gallery` / ficha Admin si
hace falta delta; no ampliar a Wysiwyg)_
