# Frontend — design system, componentes y patrones

> Documento normativo. Templates de código en
> [templates-frontend.md](templates-frontend.md). Librerías: [libraries.md](libraries.md) §2.

## 1. Estructura de `resources/js`

```
resources/js/
  app.tsx · ssr.tsx
  Layouts/
    AppLayout.tsx            # Sidebar data-driven + Topbar (selector empresa, buscador,
                             # notificaciones realtime, idioma, usuario)
    AuthLayout.tsx · PortalLayout.tsx
  Components/                # SOLO componentes reutilizables entre dominios (catálogo §4)
    Table/ · Form/ · Records/ · Feedback/ · ui/ (shadcn)
  Pages/<Dominio>/<Entidad>/{Index,Create,Edit,Show}.tsx
  Pages/<Dominio>/<Entidad>/components/   # componentes privados de la entidad
  hooks/                     # usePermissions, useCurrentCompany, useServerTable...
  lib/                       # format (importes/fechas es-ES), iban, line-calc, cn
  types/                     # <dominio>.d.ts espejo de Resources + shared.d.ts
  locales/                   # i18n front (es, en, ca) — claves compartidas con lang/
```

Reglas:
- **R-FE-01** TypeScript estricto (`strict: true`); prohibido `any` salvo justificación puntual comentada.
- **R-FE-02** Un componente se promociona de `Pages/**/components/` a `Components/` cuando lo usan ≥2 dominios; al promocionarlo se documenta en el catálogo (§4).
- **R-FE-03** Nada de axios/fetch manual contra endpoints propios: la navegación y mutación van por Inertia (`router`, `useForm`). Excepciones: el ajax interno de Tabulator (§3), toggle/acciones async de fila vía `lib/http` (`patchJson`), y descargas de ficheros.
- **R-FE-04** Importes y fechas SIEMPRE con `lib/format` (decimales 3 venta / 4 coste
  compra / 2 totales-contabilidad). Fechas UI: mapa `DATE_FORMATS` en `lib/format.ts`
  (`es`/`ca` → `dd/mm/yyyy`, `en` → `yyyy/mm/dd`; ampliar al añadir locale). Prohibido
  `toFixed`/`toLocaleString` sueltos. En ServerTable, fechas y valores numéricos
  alineados a la derecha: `headerFilterKind: 'date'|'number'` o
  `valueKind: 'date'|'number'` (ServerTable aplica `hozAlign: 'right'` si no hay
  override). Header filter `date` = **un control** en cabecera que abre panel
  Desde/Hasta etiquetado (no dos datepickers apilados en la fila);
  `date_between` + ISO `yyyy-MM-dd` en `filter[]`; resumen UI según locale.
- **R-FE-05** Toda cadena visible con i18n (`t('nombre')` / `__('guardar')`); cero
  literales. Claves en **castellano snake_case** (`^[a-z0-9_]+$`), sin puntos ni inglés
  jerárquico: genéricos reutilizables (`nombre`, `email`, `crear`); específicas solo si
  el texto no es el genérico (`nombre_comercial`, `empresa_nueva`); familias por prefijo
  (`usuario_nuevo`, `modulo_crear`). Locales: `config/i18n.php` (`es` default, `en`,
  `ca`). Fuente única: `lang/{locale}.json` (mismas claves; **orden A–Z**). Preferencia
  en `users.locale` + `LanguageSwitcher`. Texto nuevo = clave en **todos** los locales.
  Rule Cursor `i18n.mdc`; CI en `tests/Architecture/I18nJsonTest.php`.
- **R-FE-06** Las acciones se ocultan según `usePermissions()`, pero la barrera real es la Policy del backend (la UI nunca es la única defensa).
- **R-FE-08** Campos de texto (`text`, `email`, `password`, `search`, `tel`, `url`) usan
  `Components/ui/input` (`Input`), nunca un `<input>` HTML suelto. El kit aplica
  `maxLength={255}` por defecto (`DEFAULT_INPUT_MAX_LENGTH`); si el campo es más corto,
  se pasa `maxLength` explícito **y** el FormRequest declara el mismo `max:*`. Controles
  no texto (`checkbox`, `radio`, `hidden`, `file`, `submit`, `button`) no usan este
  Input. **Textarea / Wysiwyg quedan fuera** de R-FE-08 (límites propios cuando existan).

## 2. Design system

- Base **shadcn/ui** (Radix + Tailwind 4): los componentes se copian a `Components/ui/` y se
  ajustan a los tokens del proyecto. No se instalan kits alternativos.
- **Plantilla de referencia (decisión 2026-07-14)**: [shadcn-admin](https://github.com/satnaing/shadcn-admin)
  (satnaing, MIT) — mismo motor que el nuestro (shadcn/ui + Radix + Tailwind), Vite/React/TS
  sin Next.js, iconos Lucide, claro/oscuro por tokens, sidebar data-driven y paleta de
  comandos ⌘K. Se usa como **referencia de adaptación**: se copian sus patrones visuales a
  `Components/`/`Layouts/AppLayout.tsx`, nunca se clona como scaffold completo — se descartan
  su router (TanStack Router) y su integración de auth (Clerk), sustituidos por Inertia y el
  auth propio del proyecto.
- Tokens (colores, radios, spacing, tipografía) en CSS variables (`app.css` con `@theme` de
  Tailwind 4). Tema de marca único y fijo del proyecto — sin theming completo por tenant
  (ver branding más abajo).
- **Color por módulo conservado** (decisión 2026-07-14, continuidad deliberada con
  Inspinia/v1): el `color`/`icon` de cada `Module` (R-AUT-07) tiñe su entrada en el sidebar
  — única variación de color "por dominio" dentro de un tema por lo demás único.
- **Branding por tenant limitado al logo** (decisión 2026-07-14): cada empresa puede traer
  su logo, mostrado en el chrome de la app (topbar/sidebar) y en las plantillas de
  documentos/PDF. Sin colores ni tipografía por tenant — simplifica los tokens (no hace
  falta que sean sobreescribibles en runtime más allá del logo).
- Densidad **compacta** por defecto: esto es un ERP de trabajo intensivo con tablas; se
  prima información por pantalla sobre aire (tamaño base 14px, filas de tabla ~32px).
- Modo claro es el principal; el oscuro se soporta desde el día 1 vía tokens (no colores
  hardcodeados en componentes).
- Iconos: exclusivamente `lucide-react` (coincide con la plantilla de referencia).

## 3. `<ServerTable>` — contrato estándar de listados (Tabulator)

**Decisión (2026-07-14): Tabulator** es el motor de los ~123 listados server-side
(sustituye a TanStack Table de la visión inicial). Tabulator no es React-nativo: se envuelve
UNA vez en `Components/Table/ServerTable.tsx` y **ningún módulo instancia Tabulator
directamente** (R-FE-07).

### 3.1 Contrato backend (el "API de tablas")

Cada listado expone un endpoint JSON convencional, hermano de su ruta index:

```
GET /sales/budgets/table?page=2&size=25&sort=[{"field":"date","dir":"desc"}]
                        &filter=[{"field":"status","type":"=","value":"confirmed"}]
→ 200 {
    "data":      [ ...filas del Resource de índice... ],
    "last_page": 14,
    "total":     332
  }
```

- Params y respuesta siguen el protocolo remoto de Tabulator (`page/size/sort/filter`,
  respuesta `data + last_page`); `total` se añade para el contador. `filter`/`sort`
  pueden llegar como JSON string (ServerTable) o array; `TableRequest` los normaliza.
- El endpoint lo sirve el método `table()` del controller usando **`TableRequest`**
  (valida/whitelista campos de sort y filtro) + **`TableBuilder`** (service de `Support`
  que aplica filtros declarados, sort, paginación sobre un builder ya tenant-scoped).
- Filtros y columnas ordenables son **whitelist explícita** en el TableRequest de la
  entidad: nunca se aplica al query un campo no declarado (inyección de columnas).
- Export = mismo endpoint con `?export=xlsx|csv|pdf`, mismos filtros/sort, **sin
  paginación UI**. Cap sync: `TableRequest::EXPORT_MAX_ROWS` (5000); si se supera → 422.
  Excel/CSV vía `maatwebsite/excel`; PDF vía `barryvdh/laravel-dompdf`. Cola si un change
  futuro supera el umbral de catálogo grande.
- Ruta con nombre `<módulo>.<entidad>.table`, protegida por la misma Policy que `index`.

### 3.2 Contrato frontend (referencia: Admin Modules Index)

```tsx
<ServerTable<ModuleRow>
  endpoint="/admin/modules/table"
  columns={columns}            // ColumnDef: title i18n, headerFilterKind string|select|date|number
  persistKey="admin.modules"   // localStorage erp.table.<persistKey>.<userId>
  exportable                   // toolbar Excel + PDF
  rowActions={(row) => [       // columna Acciones derecha; Delete último + ConfirmDialog
    { id: 'edit', icon: Pencil, labelKey: 'editar', onClick: () => router.visit(...) },
  ]}
/>
```

Obligatorio en todo Index/CRUD salvo excepción escrita en el design del change:

1. **Header filters** tipados por columna (`headerFilterKind`); serializan al canal
   `filter[]` (mismo que advanced search). Controles de filtro con borde suave
   y **misma altura** (`--st-hf-height` en `tabulator-erp.css`: input, select y
   trigger date); solo columnas con filtro. `date` → un trigger → panel
   Desde/Hasta; payload `type: date_between`, `value: { from, to }` ISO día.
2. **Columna Acciones** vía `rowActions`: título centrado (i18n `acciones` → «Acciones»
   / mayúscula inicial en todos los labels de columna), iconos + tooltip i18n; botones
   con fondo primario (`#0f766e`); Delete último si existe (rojo pastel `#fee2e2` /
   `#b91c1c`). Si el Index declara toggle activo/inactivo: **primero a la izquierda**
   (`kind: 'toggle'`), persistencia async sin recarga (JSON + `row.update`). **El set
   de botones lo decide Luis** en cada Index futuro. **Prohibido** mostrar columna
   `slug` en listados ServerTable (sí en formularios). Referencia Modules: `name`,
   `color`, `icon`, `level`, `created_at`, `status` + Acciones (toggle + Edit).
3. **Toolbar**: mostrar/ocultar columnas (checkboxes), page size, Excel/PDF si
   `exportable`. Persistencia solo localStorage (no BD).
4. **i18n Tabulator** (paginador/textos) al locale de sesión; reactiva al cambio de idioma
   sin F5 (`ca` usa override de strings del proyecto).
5. **AdvancedSearchModal** opt-in: primer CTA en `ViewActionBar` tras Volver cuando la
   página lo active. Módulos: **sin** botón. Escribe al mismo `filter[]`.

- `ServerTable` es el único wrapper Tabulator (R-FE-07). Estilos: solo `tabulator-erp.css`.
- Formatters tipados en `Components/Table/formatters.tsx` — no HTML strings ad-hoc.
- Edición inline de Tabulator: PROHIBIDA en listados.

## 4. Catálogo de componentes genéricos

Todo lo de esta tabla existe UNA vez en `Components/`. Antes de crear un componente en un
módulo, comprobar aquí. Al añadir uno nuevo: fila en esta tabla + template si es patrón.

| Componente | Propósito / contrato resumido |
|---|---|
| `Table/ServerTable` | Listados server-side (§3): header filters, `rowActions`, toolbar, export, i18n Tabulator; referencia = Admin Modules |
| `Table/AdvancedSearchModal` | Modal búsqueda avanzada opt-in (`fields`/children + `onApply`); ActionBar tras Volver; mismo canal `filter[]` |
| `Table/formatters` | Formatters tipados: `money(dec)`, `date`, `number(dec)`, `boolean` (+ ampliar por dominio); con `valueKind`/`headerFilterKind` date|number → align right |
| `Form/Field` + kit MVP | **Implementado** (`c2026-10-26-form-kit-mvp`): `Field`/`FormField`, `Input` (→ `ui/input` R-FE-08), `Textarea`, `NumberInput`, `Select` (filtro local; `onSearch` opcional), `DatePicker` (`date-fns` + input date), `Checkbox`, `Switch`. Pendientes de kit: `Wysiwyg`, `Dropzone`, `LangTabs` |
| `Form/FormActions` | Guardar/cancelar **dentro del form** — **pendiente** (≠ `ViewActionBar`) |
| `Records/RecordEditLayout` | Esqueleto de Create/Edit: tabs, sidebar meta, zona de contenido — **pendiente** (cabecera de ficha → `RecordSummary`) |
| `Records/RecordSummary` | Ficha edit/show (card blanca): `titleKey` + badge `identifier` + `fields[]` (`labelKey`/`value`/`tone`); helper `auditRecordSummaryFields` en `lib/recordSummary`. Opt-in por page (no AppLayout). Sin totales ni lápiz inline |
| `Records/ActionBar` | Ciclo de vida de **documento** (confirmar, facturar…) — ≠ chrome de vista |
| `ViewActionBar` | Chrome bloque 3: CTAs + delete; Volver solo si `showBack` (Index → `false` salvo excepción) |
| `Records/Tabs` | Tabs con lazy load (deferred props / partial reload) |
| `Lines/LineItemsEditor` | **El componente clave** (vision §4.3): grid editable de líneas de documento — buscador de artículo, 4 descuentos en cascada, stock por almacén, precios/tarifas, totales/margen/IVA en vivo, drag&drop. Único lugar con edición en grid. Cálculo SIEMPRE con `lib/line-calc` (espejo unit-testeado del cálculo backend) |
| `Feedback/ConfirmDialog` | Confirmaciones destructivas (`Components/Feedback/ConfirmDialog`) |
| `Feedback/EmptyState` · `ErrorBoundary` | Vacíos con CTA · captura de errores de página |
| `ui/input` (`Input`) | Primativa shadcn de texto con `maxLength` default 255 (R-FE-08); override + FormRequest `max` espejo |
| `ui/*` | Resto de primitivas shadcn (Button, Dialog, DropdownMenu, Badge, Tooltip…) |
| `SearchCommand` | Buscador global (⌘K) multi-entidad — adaptar el patrón ya resuelto en la plantilla de referencia (§2) |
| `NotificationsPanel` | Campana + panel (`Components/Topbar`); stub empty UI hasta Reverb |
| `CompanySwitcher` / `WorksiteSwitcher` | Selector empresa (`Topbar/CompanySwitcher`); obra pendiente |
| `AppTopbar` | Chrome bloque 1: collapse izq.; locale→avatar der. |
| Aside rail | Colapso md+ = mini-aside iconos + flyout Popover; persistencia `erp.aside.collapsed`; móvil off-canvas |
| `PageBreadcrumb` | Bloque 2 bajo topbar: `option` (Link i18n) + `suboption` (texto); Index → `dashboard` |

## 5. Patrones de página

Toda pantalla del ERP es uno de estos 4 patrones (si no encaja, se discute en la propuesta
OpenSpec antes de inventar un quinto):

1. **Index** = `AppLayout` + `ViewActionBar` (Crear; advanced search opt-in) + `ServerTable`
   de referencia (§3.2). Primera referencia implementada: Admin Modules.
2. **Create/Edit** = `RecordEditLayout` + form RHF+zod espejo del FormRequest + tabs.
3. **Documento** = Create/Edit + `LineItemsEditor` + `ActionBar` de ciclo de vida + panel
   de totales. (Presupuesto, pedido, albarán, factura, recepción comparten este esqueleto.)
4. **Dashboard/consulta** = grid de cards/KPIs + tablas embebidas (Fase 6).

Convenciones de flujo:
- Tras `store`: redirect a Edit del registro creado (no al índice) con toast.
- Formularios con `useForm` de Inertia para el transporte + RHF/zod para UX de validación;
  los errores de servidor se mapean a los campos.
- Estados de documento: badge con color por enum (mapa único en `lib/status-colors.ts`).
- Confirmaciones: toda acción irreversible pasa por `ConfirmDialog`.

## 6. Transversales de UX

- **Flash/toasts**: props flash de Inertia → `sonner` (patrón único en AppLayout).
- **Errores de validación**: siempre bajo el campo + resumen si el form es largo.
- **Notificaciones realtime**: canal privado por usuario + empresa activa (Echo/Reverb).
- **Carga**: indicador de progreso de Inertia + skeletons en tabs diferidas.
- **Accesibilidad**: navegable por teclado (Radix lo da casi gratis); foco visible; el grid
  de líneas opera 100% con teclado (tab/enter/flechas) — requisito de productividad ERP.
- **Responsive**: el admin es desktop-first; las tablas colapsan columnas secundarias
  (responsive layout de Tabulator) y el portal cliente sí es mobile-friendly.
