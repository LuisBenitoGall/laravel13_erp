<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-20-admin-gallery-wysiwyg
Rule: 10-architecture
Summary: Fase 2 Galería — insertar imagen desde la galería en el Wysiwyg
  de Contenidos tipo Article; ampliar allowlist HTML para `<img>` seguro.
Goals:
- Solo body Wysiwyg de **Article** (no Help).
- Botón en toolbar del Wysiwyg abre modal de selección (Fase 2: modal
  embebido; **no** extraer aún `<MediaPicker>` genérico = Fase 3).
- Inserción **solo** con URL de un MediaAsset de la galería (no URL
  remota libre en este change).
- Al insertar, `alt` del `<img>` = `alt` del MediaAsset (si vacío, alt
  vacío).
- Ampliar `AllowedHtmlSanitizer` (y TipTap) para permitir `<img>` con
  `src` + `alt` de forma segura, para que la imagen sobreviva al Guardar.
NonGoals:
- Fase 3 `<MediaPicker>` extraído/reutilizable.
- Insertar en Help / excerpt / otros campos.
- URL remota libre / upload directo desde el editor.
- Editar `alt` dentro del editor en este change.
- Crop / cambios en Index Galería.
Skills:
- openspec-propose
- inertia-react-development
- tenancy-rbac
Notes:
- Origen: `/opsx-explore`. Fases 1 / 1b / crop-ux hechas. Este brief = Fase 2.
- Hoy Wysiwyg = TipTap StarterKit sin Image; sanitizer sin `<img>`.
- Modal lista activos visibles (mismo perímetro/permisos que Galería);
  10 concreta endpoint (p. ej. JSON index ligero) vs props si aplica.
- Extensión TipTap Image: 10 confirma paquete (`@tiptap/extension-image`
  o equivalente del major instalado) y `libraries.md` si hace falta.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.
- Tras brief: usuario lanza 10-architecture en sesión nueva.


## ADDED Requirements

### Requirement: Insertar imagen de galería en Wysiwyg Article
El sistema SHALL permitir, desde el Wysiwyg del cuerpo de un Content
Article, abrir un selector de MediaAsset y, al elegir uno, insertar un
`<img>` con `src` = URL del activo y `alt` = alt del activo.

#### Scenario: Insertar desde galería
- **WHEN** el actor en Edit Article abre el selector, elige un activo y
  confirma
- **THEN** el HTML del editor incluye un img con la URL de ese activo

### Requirement: Persistencia img en allowlist
El sistema SHALL permitir que `<img>` con `src` y `alt` seguros
sobrevivan al sanitizado al guardar el Content Article.

#### Scenario: Guardar Article con imagen
- **WHEN** el actor guarda un Article cuyo body contiene un img insertado
  desde galería
- **THEN** tras recargar el Edit, la imagen sigue presente


## MODIFIED Requirements

_(ninguno obligatorio — 10 puede delta contents-edit / form-kit /
admin-gallery si el contrato lo exige)_
