Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-16-contents-create
Origen: informe 25-architecture-review (**APPROVED WITH OBSERVATIONS**)
+ petición Luis (UX Create).
No reabran diseño de dominio (Create sigue = name + kind; Inactive;
placeholder Edit).
Contrato: `openspec/changes/c2026-11-16-contents-create/`
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md / design.md / specs salvo nota mínima en
Admin.md/frontend si hace falta documentar el nuevo Form control.
No archives. E2E N/A.

## Contexto

Create mínimo + Index + placeholder + HelpTip OK (`ContentTest` 11/11).
25 señaló **MEDIUM**: Actions/Requests Update/Toggle huérfanos del
propose `contents-help` (sin rutas). Luis pide además: el campo **Tipo**
en Create deje de ser `Select` y pase a **radio buttons**.

En el kit **no** hay `Form/Radio*` ni `ui/radio-group` (sí `Checkbox` /
`Switch` sobre Radix). Hay que añadir un control **reutilizable** y
usarlo en Create.

## Qué hacer

### 1. MEDIUM — Limpiar código muerto Update/Toggle (obligatorio)

Eliminar (o dejar de exportar / no dejar archivos huérfanos) lo que no
tenga ruta en este change:

- `app/Domains/Admin/Actions/UpdateContent.php`
- `app/Domains/Admin/Actions/ToggleContentStatus.php`
- `app/Domains/Admin/Http/Requests/UpdateContentRequest.php`
- `app/Domains/Admin/Http/Requests/ToggleContentStatusRequest.php`

Comprobar que `ContentController` y `routes/domains/admin.php` **no**
referencian update/status (ya `only: index,create,store,edit,destroy`).
No reintroducir Edit real. El change Edit futuro volverá a crear lo
necesario.

### 2. UX — Tipo con radio (obligatorio; decisión Luis)

En `resources/js/Pages/Admin/Contents/Create.tsx`:

- Sustituir el `Select` de `kind` por un grupo de **radio buttons**:
  opciones Información (`help`) y Contenido (`article`).
- Misma validación backend (`kind` enum); mismos i18n
  `contenido_tipo_informacion` / `contenido_tipo_contenido`.
- Accesible: `name`/`id`/`aria` correctos; error de Field visible.
- Layout: fila con gap (patrón booleanos del frontend.md: `flex` +
  `gap-x-8` o similar); no pegados.

### 3. Componente reutilizable `Form/RadioGroup` (obligatorio)

No existe aún → **crearlo** en el kit Form (no inventar otro path):

1. Si hace falta primitiva shadcn: añadir `Components/ui/radio-group`
   al estilo de `ui/checkbox` (Radix `@radix-ui/react-radio-group` —
   permitido vía canon shadcn/`libraries.md`). Instalar el paquete Radix
   solo si no está ya en `package.json`.
2. Wrapper `Components/Form/RadioGroup.tsx` (API cercana a Select/
   Checkbox), p. ej.:
   - `value: string`
   - `onChange: (value: string) => void`
   - `options: { value: string; label: string; id?: string }[]`
   - `name?`, `disabled?`, `className?`, `aria-invalid?` / `id` del grupo
3. Export en `Form/index.ts`.
4. Usar ese wrapper en Create Contenidos (no radios crudos sueltos en
   la página).
5. Actualizar catálogo en `docs/standards/frontend.md` § Form kit (una
   línea: `RadioGroup` implementado) — documentación pedida por Luis
   vía este prompt.

No crear un Radio de un solo botón suelto si el caso de uso es un grupo
exclusivo; el grupo es el control canónico.

### 4. LOW opcional

- Si el delta `menu-core-pillars` del change está vacío/roto: o bien
  restaurar un delta mínimo coherente con proposal («ítem ya en main»)
  o no tocarlo si 30 archive lo reconciliará; no bloquea.
- Ampliar Feature Create UI: source contiene radio/`RadioGroup` y **no**
  `Select` para kind.

## Verificación

```text
php artisan test --compact tests/Feature/Admin/ContentTest.php
```

Pint dirty PHP. Si tocaste TS del kit, que el build/IDE no quede roto
(export Form).

## Fuera de alcance

- Formulario Edit real (Wysiwyg, status Active, textos i18n).
- Galería / published_* / JSON runtime.
- Cambiar default Inactive o `company_id` null.
- Archive / E2E 40.
- Reabrir `contents-help` como contrato.

## Gate de salida

- [ ] Update/Toggle Actions+Requests huérfanos eliminados; rutas intactas.
- [ ] Create Tipo = radios vía `Form/RadioGroup` reutilizable (+ ui
      Radix si aplica).
- [ ] `frontend.md` menciona RadioGroup; export Form OK.
- [ ] Feature Contents verde (+ assert UI radio si se añade).
- [ ] Pint OK.
- [ ] Volver a **25-architecture-review**.
