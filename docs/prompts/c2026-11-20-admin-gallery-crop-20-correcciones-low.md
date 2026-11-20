Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-20-admin-gallery-crop
Origen: informe 25-architecture-review (**APPROVED WITH OBSERVATIONS**).
Corrección **LOW** (no hay CRITICAL/HIGH/MEDIUM). No reabran diseño.
Contrato: `openspec/changes/c2026-11-20-admin-gallery-crop/`
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni specs.
No archives. E2E N/A.

## Contexto

Crop → copia (`POST …/copies`, hereda name/alt, original intacto) OK
(`GalleryTest` 16/16). Queda un LOW: en
`resources/js/Pages/Admin/Gallery/CropDialog.tsx`, `saveCopy()` captura
errores de canvas/`toBlob`/carga de imagen en un `catch` vacío que solo
hace `setSaving(false)` — el actor no ve feedback si falla el crop
cliente (CORS tainted, canvas, etc.). El éxito POST ya toast vía flash
`AppLayout`; falta el error cliente.

**Decisión Luis:** toast de error i18n en el `catch` (mismo patrón que
`galeria_url_copiar_error` en Index).

## Qué hacer (LOW)

### 1. Feedback en catch de CropDialog

En `resources/js/Pages/Admin/Gallery/CropDialog.tsx`:

- Importar `toast` de `sonner` (como Index).
- En el `catch` de `saveCopy`: `toast.error(t('…'))` + `setSaving(false)`.
- No cambiar el happy path (`router.post` + `onSuccess`/`onFinish`).

### 2. i18n

Clave nueva es/en/ca, p. ej. `galeria_recortar_error` — mensaje corto
tipo «No se pudo generar la imagen recortada» / EN / CA. No reutilizar
`galeria_url_copiar_error` (otro significado).

### 3. Verificación mínima

- Assert source: `CropDialog.tsx` contiene `toast.error` y la clave i18n
  (puede ir en el Feature source-assert existente de Gallery, o un `it`
  corto). No hace falta browser E2E.
- No ampliar Action/Request/ruta.

## Verificación

```text
php artisan test --compact tests/Feature/Admin/GalleryTest.php
```

Pint dirty PHP solo si tocas PHP (no debería hacer falta).

## Fuera de alcance

- Paginación Index / DRY CreateMediaAsset ↔ Copy.
- Wysiwyg / MediaPicker / parent_id / upload empresa.
- Sobrescribir original; Intervention server-side.
- Cambiar límites mime/KB o quality canvas.
- Archive / E2E 40.

## Gate de salida

- [ ] `catch` de crop muestra `toast.error` con clave i18n es/en/ca.
- [ ] GalleryTest (o assert source) verde.
- [ ] Volver a 25 solo si Luis lo pide; si no, seguir a 30-testing.
