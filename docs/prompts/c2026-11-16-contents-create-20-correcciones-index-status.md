Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-16-contents-create (archivado)
Origen: `/opsx-explore` + captura Index Contenidos (columna Estado
literal). Corrección de Index al patrón de catálogos Admin.
**Decisión Luis:** estado = switch en Acciones; **sin** columna Estado.
No reabran el Edit real (otro change). No archives. E2E N/A.

Contrato de referencia:
`openspec/changes/archive/2026-11-16-c2026-11-16-contents-create/`
Si este prompt y los artefactos chocan en el Index status, **manda este
prompt** (override de design D3 “badge RO” / status solo en Edit).
No reescribas proposal.md, design.md ni specs del archive.

## Contexto

Create/Index/placeholder/HelpTip ya entregados. El Index muestra
columna `Estado` con literal «Inactivo»/«Activo» y Acciones solo
Edit+Delete. El canon Admin (Sectores, Unidades, Métodos de pago, …)
es: **sin columna Estado** + toggle en Acciones (`kind: 'toggle'`) +
`PATCH …/status`.

Hoy `Contents/Index.tsx` tiene columna `status`; no hay ruta
`contents.status` ni Action de toggle. `HelpContentMap` ya hace
`forget()` en Create/Delete; el toggle también debe invalidar.

Patrón a calcar: `Sectors/Index.tsx` + `ToggleSectorStatus` +
`SectorController::updateStatus` + `PATCH admin/sectors/{id}/status`.

## Qué hacer

### 1. Backend status

- Action `ToggleContentStatus` (o nombre hermano del catálogo) que
  actualiza `Content.status` Active/Inactive y llama
  `HelpContentMap::forget()`.
- FormRequest de status (como `ToggleSectorStatusRequest`).
- `ContentController::updateStatus` + Policy `update`.
- Ruta `PATCH admin/contents/{content}/status` →
  `admin.contents.status` (junto al resource contents).

### 2. Index UI

En `resources/js/Pages/Admin/Contents/Index.tsx`:

- **Eliminar** la columna `estado` / `status` (y su headerFilter).
- En Acciones: orden **toggle → Edit → Delete** (como Sectores).
- Toggle: `kind: 'toggle'`, `checked` según `row.status === 'active'`,
  PATCH JSON a `/admin/contents/{id}/status` con `{ status }`,
  devolver fila actualizada como el patrón Sector.

Columnas que quedan: Nombre, Código, Tipo (+ Acciones).

### 3. Features

En `tests/Feature/Admin/ContentTest.php` (o el Feature existente):

- PATCH status Inactive→Active (y/o Active→Inactive) con permiso → OK
  y persiste.
- 403 sin permiso `contents.update` (o el que use Policy update).
- Tras activar un Help, el mapa/cache no sirve datos stale de forma
  observable (p. ej. forget cubierto vía activar + assert status; si
  hay helper de mapa en tests, usarlo; no hace falta test de UI
  browser).

## Verificación

```text
php artisan test --compact tests/Feature/Admin/ContentTest.php
```

Pint dirty PHP.

## Fuera de alcance

- Formulario Edit real (LangTabs, Wysiwyg, campos por kind).
- Cambiar Create (name+kind → Inactive).
- Galería, published_*, kinds nuevos.
- Columna Estado “de vuelta” con literales.
- Archive / E2E 40.

## Gate de salida

- [ ] Sin columna Estado en Index; toggle en Acciones.
- [ ] Ruta + Action + invalidación HelpContentMap.
- [ ] Features status + Pint OK.
- [ ] Volver a 25 solo si Luis lo pide; si no, seguir o pasar al
      explore/brief del Edit.
