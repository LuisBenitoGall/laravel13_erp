# Design

## Context

Hoy `Form/Wysiwyg` usa TipTap StarterKit sin extensión Image; el toolbar no
tiene inserción de medios. `AllowedHtmlSanitizer` allowlista
`p/br/strong/em/ul/ol/li/h2/h3/a` y elimina atributos salvo `a[href]` seguro —
cualquier `<img>` se pierde al Guardar Article (`UpdateContent`). La Galería
(`MediaAsset`) ya expone URL pública y `alt`; Index es Inertia grid, sin
endpoint JSON de selección. Fase 3 (`MediaPicker` genérico) está fuera de
alcance. Motivación: ver proposal.md — Why.

## Goals / Non-Goals

**Goals:**

- Insertar desde Galería en el body Wysiwyg de Content **Article** únicamente.
- Persistencia segura de `<img src alt>` vía sanitizer + TipTap Image.
- Modal embebido en el propio Wysiwyg (opt-in), alimentado por listado JSON
  con el mismo perímetro/`gallery.view` que el Index.

**Non-Goals:**

- Extraer `<MediaPicker>` reutilizable (Fase 3).
- Help, excerpt, Company description u otros campos.
- URL remota libre / upload desde el editor / editar `alt` en el editor.
- Cambios en Index Galería o crop.

## Decisions

### D1 — Extensión TipTap Image + canon

- **Decisión:** añadir `@tiptap/extension-image` alineado al major TipTap ya
  instalado (`^3.x`) y documentarlo en `docs/standards/libraries.md` junto a
  `@tiptap/react` / starter-kit.
- **Por qué:** StarterKit no incluye Image; sin extensión el nodo no se
  serializa bien en el editor.
- **Alternativa:** HTML crudo / `setContent` sin extensión → frágil y peor UX.

### D2 — Opt-in `enableGalleryInsert` (Article only)

- **Decisión:** `Wysiwyg` carga siempre la extensión Image (para renderizar
  HTML con `<img>` ya persistido). El botón «Galería» y el modal solo si
  `enableGalleryInsert === true`. `ContentForm` lo activa solo cuando
  `kind === Article`. Company / Help no pasan la prop (default false).
- **Por qué:** cumple NonGoal «solo Article» sin bifurcar el kit en dos
  componentes.
- **Alternativa:** botón siempre visible → fuga a Company/Help.

### D3 — Modal embebido, no MediaPicker

- **Decisión:** UI de selección vive dentro del árbol Wysiwyg (dialog + grid
  ligero). No se exporta componente genérico.
- **Por qué:** brief Fase 2; Fase 3 reutilizará el patrón.
- **Alternativa:** extraer ya MediaPicker → scope creep.

### D4 — Endpoint JSON `GET admin/gallery/picker`

- **Decisión:** acción dedicada (p. ej. `MediaAssetController@picker`) que
  devuelve JSON `{ assets: [{ id, name, alt, url }] }` con la **misma** query
  de visibilidad que `index` (`company_id` null OR companies del actor) y
  `authorize('viewAny', MediaAsset::class)`. Ruta nombrada
  `admin.gallery.picker`, middleware/módulo Galería igual que el resource.
- **Por qué:** Index es Inertia; el modal en Edit Content no debe montar la
  página Galería ni reutilizar props de otra pantalla.
- **Alternativa A:** props Inertia en Edit Content con todos los assets →
  acopla Contents↔Gallery y payload grande siempre.
- **Alternativa B:** `Accept: application/json` en `index` → mezcla
  contratos Inertia/JSON; menos claro para tests.

### D5 — Inserción: solo URL de MediaAsset; `alt` heredado

- **Decisión:** al confirmar, `editor.chain().focus().setImage({ src: url,
  alt: asset.alt ?? '' })`. No campo URL libre; no edición de alt en el
  editor en este change.
- **Por qué:** brief; reduce XSS y superficie.

### D6 — Sanitizer: `<img>` con `src` + `alt` seguros

- **Decisión:** ampliar `ALLOWED_TAGS` con `<img>`. En el callback de
  atributos, para `img` conservar solo:
  - `src`: permitir `https?://…` **o** path relativo que empiece por `/`
    (URLs de disco `public` tipo `/storage/…`). Rechazar `javascript:`,
    `data:`, `vbscript:`, etc.
  - `alt`: texto escapado (puede ser vacío).
  Self-closing / void tag: emitir `<img src="…" alt="…">` sin hijos.
- **Efecto colateral aceptado:** Company description (mismo sanitizer)
  también podría persistir un `<img>` seguro si el HTML ya lo trae; sin
  botón de galería no es el flujo UX. No se bifurca sanitizer por dominio
  en este change.
- **Alternativa:** sanitizer distinto por campo → duplicación y drift.

### D7 — Tests y E2E

- Feature: actor con permisos Content+Gallery inserta vía flujo HTTP de
  update con body que incluye img de URL de galería → tras reload el HTML
  sanitizado conserva `src`/`alt`. Unit: sanitizer keep/reject casos.
- Feature: `GET picker` 200 con assets visibles / 403 sin permiso.
- E2E Pest browser: **no aplica** (flujo editor+modal; cobertura Feature +
  assert fuente / unit sanitizer suficiente para Fase 2).

### D8 — Excepciones a standards

Ninguna. Listados Galería Index siguen con excepción ServerTable ya
declarada; este change no la toca. Dependencia nueva solo vía
`libraries.md`.

## Risks / Trade-offs

- [Sanitizer demasiado permisivo en `src`] → Mitigación: denylist de esquemas
  peligrosos + solo http(s) o path `/…`; tests de rechazo.
- [Extensión Image sin botón en Company permite pegar HTML con img] →
  Mitigación: sin UI de inserción; sanitizer sigue filtrando; aceptable.
- [Acoplamiento Wysiwyg→ruta gallery] → Mitigación: fetch solo si
  `enableGalleryInsert`; Fase 3 moverá a MediaPicker.
- [Assets borrados dejan img rotas en Articles] → Mitigación: fuera de
  alcance (igual que URLs copiadas hoy); no soft-redirect.

## Migration Plan

1. `npm i @tiptap/extension-image@^3` + fila en `libraries.md`.
2. Sanitizer + tests unit.
3. Ruta/picker + tests Feature.
4. Wysiwyg + modal + ContentForm Article.
5. Feature Contents update con img; Pint/PHPStan/tests afectados.

Rollback: revert change; Articles ya guardados con img seguirían
necesitando el sanitizer ampliado para no perderlas — rollback solo si no
hay datos en prod (Fase 0).

## Open Questions

_(ninguna — decisiones cerradas en brief + D1–D8)_
