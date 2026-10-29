## Context

`frontend.md` §3 ya define el contrato rico de listados (Tabulator vía único wrapper
`ServerTable`, filtros, export, persistencia). El código actual es mínimo:

- `ServerTable.tsx`: ajax remoto + paginación + `columns` (sin header filters, Acciones,
  toolbar, i18n Tabulator, export UI).
- `TableRequest` / `TableBuilder`: whitelist sort/filter; `export` solo `xlsx|csv` en rules
  (PDF no está en el contrato backend aún).
- Modules Index: Inertia + `ServerTable` básico; `ModuleController@table` con searchable
  `name`/`slug`; Acciones/export/advanced search ausentes.

Brief explore (`docs/prompts/c2026-10-29-server-table-reference.md`): Opción A — endurecer
Modules Index como primera referencia y actualizar la norma para todos los CRUDs.

Stack confirmado: `tabulator-tables` + `barryvdh/laravel-dompdf` + `maatwebsite/excel`
(`libraries.md`).

## Goals / Non-Goals

**Goals:**

1. Ampliar `ServerTable` al contrato de referencia: header filters tipados, columna
   Acciones (`rowActions`), locale Tabulator reactivo al idioma de sesión, toolbar
   (mostrar/ocultar columnas con persistencia localStorage por usuario + `persistKey`,
   page size, Excel + PDF).
2. Modal genérico de búsqueda avanzada (opt-in vía ActionBar); API lista; **sin botón** en
   Módulos.
3. Modules Index: primera aplicación (Acciones = solo Edit; exportable; sin advanced
   search).
4. Alinear backend table + export con el mismo canal `filter` (whitelist).
5. Actualizar `frontend.md` (+ templates de listado si existen) como norma obligatoria
   salvo excepción escrita en design de un change.

**Non-Goals:**

- Cablear advanced search en Módulos (solo componente/API listos).
- Migrar el resto de Index del ERP en este change.
- Edición inline en listados.
- Persistencia de columnas en BD.
- Inventar dominio Presupuestos/Pedidos; solo patrón.

## Decisions

### D1 — Un solo wrapper Tabulator

**Decisión:** Toda instancia Tabulator vive en `Components/Table/ServerTable.tsx` (R-FE-07).
No se instancia Tabulator en páginas de dominio.

**Alternativa:** hooks sueltos por página → rechazada (duplicación, drift de norma).

### D2 — Header filters = mismo canal `filter[]` que advanced search

**Decisión:** Los filtros de cabecera serializan al protocolo existente
`filter=[{field,type,value}]`. El modal avanzado, cuando se use, escribe al mismo array
(merge/replace documentado en props). Whitelist en `*TableRequest::$filterable`.

**Tipos de filtro de columna (Modules y genéricos):**

| UI columna | type tipico | Ejemplo Modules |
|---|---|---|
| string | `like` | name, slug |
| select / opciones | `=` | status, level |
| date | `=` / rango si se declara | n/a en Modules v1 de este change |

**Alternativa:** query params distintos por origen → rechazada (doble protocolo).

### D3 — Columna Acciones vía `rowActions`

**Decisión:** API `rowActions?: (row) => RowAction[]` (id, icon Lucide, labelKey i18n,
`onClick`, `confirm?`, `variant?`). ServerTable inyecta columna derecha fija título
centrado (`acciones`), iconos alineados a la derecha, tooltip obligatorio, Delete último
si existe + `ConfirmDialog`.

Modules: solo Edit → `router.visit(edit)`. Futuros dashboards: preguntar a Luis qué
acciones van (norma en frontend.md).

### D4 — Persistencia columnas / page size en localStorage

**Decisión:** Clave `erp.table.<persistKey>.<userId>` (userId desde shared Inertia;
fallback `anon` solo si no hay user — no debería ocurrir en ERP). Guarda
`{ hiddenColumns: string[], pageSize: number }`. No BD.

### D5 — Export Excel + PDF

**Decisión:**

- Excel: `?export=xlsx` en el mismo endpoint `table` (ya previsto en `TableRequest`);
  `maatwebsite/excel` Export class por entidad (Modules: `ModuleExport`). Sync en Modules
  (catálogo pequeño); cola si supera umbral documentado en frontend (reutilizar texto
  norma).
- PDF: ampliar `export` a `pdf` en `TableRequest` rules; respuesta descarga vía
  `barryvdh/laravel-dompdf` (vista Blade mínima de tabla o HTML generado en Export
  action). Mismo filtro/sort activos; sin paginación (cap razonable documentado, p. ej.
  max rows export = 5000 o abort con mensaje).

**Alternativa:** endpoints `/export` separados → rechazada (duplicar auth/filtros).

**Alternativa PDF client-side:** rechazada (canon backend + i18n servidor).

### D6 — i18n Tabulator reactiva

**Decisión:** Locale de sesión (`users.locale` / i18n shared) → mapa a locale Tabulator
(`es`/`en`…; `ca` → fallback `es` o locale custom si Tabulator no trae ca — documentar en
tasks: strings paginador vía `langs` override con claves `lang/*.json`). Al cambiar idioma
sin F5: re-aplicar `table.setLocale` / recrear opciones de textos; títulos de columna y
tooltips vía `t()` re-render.

### D7 — AdvancedSearchModal opt-in

**Decisión:** `Components/Table/AdvancedSearchModal` (Dialog shadcn): props `fields`
(render props o field defs), `open`/`onOpenChange`, `onApply(filters)`. Página Index:
primer CTA en `ViewActionBar` tras Volver **solo si** `advancedSearch` activo. Modules:
`showAdvancedSearch={false}` / omitido.

### D8 — Norma

**Decisión:** Sustituir/ampliar `frontend.md` §3.2 para header filters (no solo “banda
filters”), toolbar concreta (columnas / page size / Excel+PDF), Acciones, localStorage,
AdvancedSearch opt-in. Catálogo §4: fila `AdvancedSearchModal`. Patrón Index §5 apunta a
esta referencia. `templates-frontend.md` Index actualizado. Primera referencia
implementada: Admin Modules.

### D9 — Modules TableRequest whitelist

**Decisión:** Ampliar `ModuleTableRequest` filterable/sortable a columnas del Index
(name, slug, status, level, … según columnas visibles). Header filter de cada columna
usa field whitelistado.

## Risks / Trade-offs

- [Risk] Tabulator header filters + React 19 lifecycle (destroy/recreate) → Mitigation:
  encapsular en ServerTable; tests smoke + Feature filter.
- [Risk] PDF de tablas anchas ilegible → Mitigation: columnas export explícitas / landscape;
  cap de filas.
- [Risk] `export=pdf` no estaba en TableRequest → Mitigation: ampliar rules + tests; no
  romper xlsx/csv.
- [Risk] Drift si otros Index no migran → Mitigation: norma explícita; este change no
  migra el resto (aceptado).
- [Risk] localStorage por usuario en máquina compartida → Mitigation: clave con userId;
  aceptable vs BD (decidido explore).

## Migration Plan

1. Implementar ServerTable + helpers + AdvancedSearchModal + i18n keys.
2. Backend Modules: whitelist, export xlsx/pdf, tests Feature.
3. Modules Index UI (toolbar, Acciones Edit, sin advanced search).
4. Actualizar `frontend.md` / templates.
5. Rollback: revert change; listados previos sin props nuevas siguen funcionando si
   ServerTable mantiene defaults seguros (sin toolbar export si `exportable` false).

## Open Questions

Ninguna bloqueante del brief. En cada Index futuro: Luis decide el set de `rowActions`.
