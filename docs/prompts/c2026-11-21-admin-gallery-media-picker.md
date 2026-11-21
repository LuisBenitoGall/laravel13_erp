<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-21-admin-gallery-media-picker
Rule: 10-architecture
Summary: Fase 3 Galería — extraer `<MediaPicker>` reutilizable a partir
  del selector embebido del Wysiwyg; Article sigue siendo el único
  consumidor.
Goals:
- Extraer el modal/selector actual (`GalleryInsertDialog` + búsqueda +
  infinite scroll + contrato `GET admin/gallery/picker`) a un componente
  genérico `<MediaPicker>` (nombre canónico a fijar por 10 en glosario /
  frontend si hace falta).
- El Wysiwyg Article (vía `enableGalleryInsert`) SHALL usar ese
  `<MediaPicker>` por dentro; comportamiento de inserción img
  (src/alt) sin regresión.
- Contrato JSON del picker (q, page, per_page, `{assets,meta}`) se
  reutiliza; no rediseñar la API salvo lo mínimo que 10 vea necesario
  para el componente genérico.
NonGoals:
- Cablear una segunda pantalla/consumidor (opción A acordada).
- Upload con `company_id` de empresa.
- Cambios de alcance en Index `/admin/gallery` (paginación Index).
- Variantes/thumbnails dedicados.
- Insertar en Help / URL remota libre / editar alt en el editor.
Skills:
- openspec-propose
- inertia-react-development
- tenancy-rbac
Notes:
- Origen: `/opsx-explore`. Fases 1 / 1b / crop-ux / 2 / 2b hechas y
  archivadas. Usuario eligió alcance **A** (solo extracción).
- Hoy vive en `Form/GalleryInsertDialog` consumido solo por
  `Form/Wysiwyg`.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.
- Tras brief: usuario lanza 10-architecture en sesión nueva.


## ADDED Requirements

### Requirement: MediaPicker reutilizable
El sistema SHALL exponer un componente de selección de MediaAsset
reutilizable (MediaPicker) que abre un selector con listado paginado y
búsqueda, y al confirmar expone el activo elegido (al menos id, name,
alt, url).

#### Scenario: Selección confirma activo
- **WHEN** el consumidor abre el MediaPicker, elige un activo y confirma
- **THEN** el consumidor recibe el activo seleccionado con su URL y alt


## MODIFIED Requirements

### Requirement: Wysiwyg usa MediaPicker para insertar desde galería
Cuando la inserción desde galería está activada, el Wysiwyg SHALL abrir
el MediaPicker (no un selector acoplado solo al editor) para insertar el
`<img>` con src/alt del activo.

#### Scenario: Insertar desde galería vía MediaPicker
- **WHEN** el actor en Edit Article abre el selector de galería del
  Wysiwyg, elige un activo y confirma
- **THEN** el HTML del editor incluye un img con la URL de ese activo
