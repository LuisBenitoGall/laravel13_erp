# Design

## Context

Ver proposal.md — Why. Base hoy: `AdvancedSearchModal` genérico sin
consumidores Index; `ServerTable` ya soporta `advancedFilters` +
`advancedFilterMode` (`merge`|`replace`) y canal `filter[]`
(`frontend.md` §3.7). Toolbar muestra columnas a la derecha; no hay
botón de filtros avanzados. `Preference` = favoritos name+url (tope 20
por company) ≠ presets de filtros. Products Index sin modal;
`ProductTableRequest` filterable = `name`, `type`, `status`, `search`.
Ficha Catalog aún lista Family↔Category N:N en backlog.

## Goals / Non-Goals

**Goals:**
- Kit FE reutilizable: botón toolbar + modal (campos por Index) + presets.
- Persistencia BE de presets (user + company + `table_key`, máx. 5).
- Products Index cableado (campos + whitelist table alineados).
- Docs frontend + Catalog backlog sin Family↔Category N:N.

**Non-Goals:**
- Presets compartidos / globales de company.
- Aplicar al seleccionar preset.
- Cablear otros Index.
- Cambiar `Preference`.
- Implementar Family↔Category N:N.

## Decisions

### D1 — Entidad `TableFilterPreset` (Admin), no `Preference`

- Tabla `table_filter_presets`: `company_id`, `user_id`, `table_key`
  (string; misma clave que `ServerTable` `persistKey` del listado),
  `name`, `filters` (JSON array `[{field,type,value}]`), timestamps.
- `BelongsToCompany` + `Auditable`; factory.
- Constante `MAX_PER_TABLE = 5` por (`user_id`,`company_id`,`table_key`).
- Unique lógico: nombre único por ese triple (validación Action/Request).
- **Alternativa rechazada:** reusar `Preference` — distinto contrato
  (url vs filtros) y tope distinto.

### D2 — Authz y rutas (patrón Preference)

- Dominio Admin; Policy: solo el dueño (`user_id`) en company de sesión;
  sin Functionality Spatie nueva (recurso personal, como favoritos).
- Rutas JSON autenticadas ERP (p. ej. en `routes/domains/admin.php`):
  - `GET table-filter-presets?table_key=` → listar propios
  - `POST table-filter-presets` → crear (rechazo 422 si ≥5)
  - `PATCH table-filter-presets/{preset}` → renombrar
  - `DELETE table-filter-presets/{preset}` → borrar
- Actions: `Create|Update|DeleteTableFilterPreset` transaccionales;
  create cuenta y aborta si tope.
- Respuestas JSON (+ Resource) para el modal (no solo `back()`).

### D3 — Disparador canónico = toolbar ServerTable

- Prop opt-in p. ej. `onOpenAdvancedSearch?: () => void`: si está
  definida, ServerTable renderiza botón i18n a la **izquierda** de
  «Mostrar / ocultar columnas».
- El Index posee estado `open` del modal + `advancedFilters`; no se
  exige CTA en `ViewActionBar` (compat: ActionBar sigue permitido si
  alguien lo usa; canon documentado = toolbar).
- **Alternativa rechazada:** solo ActionBar (brief: toolbar).

### D4 — Modal: Limpiar / Aplicar / presets

- Evolucionar `AdvancedSearchModal` (o wrapper fino del kit):
  - Footer: Limpiar, Cancelar, Aplicar; zona presets (lista,
    seleccionar, renombrar, borrar) + Guardar (nombre).
  - Props: `tableKey`, carga/CRUD vía `useHttp`/router JSON a las rutas
    D2; `fields`/`children` sin cambio de contrato de campos.
- **Seleccionar preset:** escribe en el draft/controlado del form;
  **no** llama `onApply`.
- **Limpiar:** vacía draft; `onClear` del Index pone
  `advancedFilters` en `undefined` (tabla vuelve a solo cabecera) y
  deja el modal abierto.
- **Aplicar:** `onApply(draft)` → Index setea `advancedFilters` y cierra.
- Guardar: persiste el draft actual (validar nombre); no aplica solo.

### D5 — Products: merge + campos alineados a taxonomía usable

- `persistKey` / `table_key`: `catalog.products` (o el que ya use el
  Index si existe; unificar).
- `advancedFilterMode`: **`merge`** (cabecera + modal; header name
  sigue útil).
- Campos del modal: `name`, `type`, `status`, `family_id`,
  `category_id`, `tag_id` (selects Active de la company).
- Ampliar `ProductTableRequest::$filterable` y el query del
  `ProductController::table` / TableBuilder para `family_id` y
  `whereHas` category/tag.
- **Alternativa:** solo name/type/status — descartada: el brief pide
  alinear taxonomía si aplica; Classification ya existe en Product.

### D6 — Docs Catalog

- En `docs/modules/Catalog.md` (y `module-map` solo si cita el N:N):
  retirar Family↔Category N:N del backlog / pendientes; dejar nota
  breve de abandono si hace falta trazabilidad.

## Risks / Trade-offs

- [Tope 5 race] dos creates concurrentes → Mitigation: count+create en
  transacción Action; unique name ayuda; test feature del 422.
- [JSON filters opacos] payload inválido → Mitigation: FormRequest
  valida shape `field/type/value`; type en whitelist del canal table.
- [Toolbar vs ActionBar doc] confusión → Mitigation: actualizar
  `frontend.md` §3.7 + catálogo; Modules sigue sin botón.
- [Filtros taxonomía N+1] → Mitigation: selects precargados vía props
  Inertia del Index o endpoint picker ya existente; query table con
  whereHas indexado por pivotes.

## Migration Plan

1. Migración + model/factory/policy/actions/routes.
2. FE kit (ServerTable botón + modal presets).
3. Products Index + TableRequest.
4. Docs + Pest.
5. Rollback: drop table + revert FE opt-in (sin breaking otros Index).

## Open Questions

Ninguna bloqueante; detalles de copy i18n / icono del botón los fija
20-implementation según catálogo existente.
