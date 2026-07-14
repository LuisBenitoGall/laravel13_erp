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
- **R-FE-03** Nada de axios/fetch manual contra endpoints propios: la navegación y mutación van por Inertia (`router`, `useForm`). Excepciones: el ajax interno de Tabulator (§3) y descargas de ficheros.
- **R-FE-04** Importes y fechas SIEMPRE con `lib/format` (es-ES; decimales 3 venta / 4 coste compra / 2 totales-contabilidad). Prohibido `toFixed`/`toLocaleString` sueltos.
- **R-FE-05** Toda cadena visible con i18n (`t('sales.budgets.title')`); cero literales.
- **R-FE-06** Las acciones se ocultan según `usePermissions()`, pero la barrera real es la Policy del backend (la UI nunca es la única defensa).

## 2. Design system

- Base **shadcn/ui** (Radix + Tailwind 4): los componentes se copian a `Components/ui/` y se
  ajustan a los tokens del proyecto. No se instalan kits alternativos.
- Tokens (colores, radios, spacing, tipografía) en CSS variables (`app.css` con `@theme` de
  Tailwind 4). Branding por tenant = sobreescritura de variables en runtime (logo + acento).
- Densidad **compacta** por defecto: esto es un ERP de trabajo intensivo con tablas; se
  prima información por pantalla sobre aire (tamaño base 14px, filas de tabla ~32px).
- Modo claro es el principal; el oscuro se soporta desde el día 1 vía tokens (no colores
  hardcodeados en componentes).
- Iconos: exclusivamente `lucide-react`.

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
  respuesta `data + last_page`); `total` se añade para el contador.
- El endpoint lo sirve el método `table()` del controller usando **`TableRequest`**
  (valida/whitelista campos de sort y filtro) + **`TableBuilder`** (service de `Support`
  que aplica filtros declarados, sort, paginación sobre un builder ya tenant-scoped).
- Filtros y columnas ordenables son **whitelist explícita** en el TableRequest de la
  entidad: nunca se aplica al query un campo no declarado (inyección de columnas).
- Export (xlsx/csv) = mismo endpoint con `?export=xlsx`, servido por cola si supera el
  umbral (maatwebsite/excel), respetando los filtros activos.
- Ruta con nombre `<módulo>.<entidad>.table`, protegida por la misma Policy que `index`.

### 3.2 Contrato frontend

```tsx
<ServerTable<BudgetRow>
  endpoint={route('sales.budgets.table')}
  columns={columns}            // ColumnDef[]: field, title (i18n), formatter tipado,
                               // width, sortable, visible por defecto
  filters={<BudgetFilters/>}   // banda de filtros controlada; ServerTable la serializa
  rowActions={(row) => [...]}  // acciones según permisos (edit, delete, confirm...)
  onRowDblClick={(row) => router.visit(route('sales.budgets.edit', row.id))}
  selectable                   // checkboxes → acciones masivas
  exportable                   // botón export (xlsx/csv) con filtros activos
  persistKey="sales.budgets"   // persistencia de estado (columnas visibles, tamaño página)
/>
```

- `ServerTable` encapsula: instancia/destrucción de Tabulator, paginación remota, spinner,
  vacío, errores, i18n de la tabla, densidad, persistencia de layout (localStorage por
  `persistKey` y usuario), y estilo Tabulator alineado con los tokens (tema propio
  `tabulator-erp.css`, único CSS de Tabulator permitido).
- Los `formatter` de columna son funciones TS del proyecto (dinero, fecha, badge de estado,
  empresa, obra…) registradas en `Components/Table/formatters.tsx` — no HTML strings ad-hoc.
- Edición inline de Tabulator: PROHIBIDA en listados (los listados navegan a Edit). La
  edición en grid solo existe dentro de `LineItemsEditor` (§4).

## 4. Catálogo de componentes genéricos

Todo lo de esta tabla existe UNA vez en `Components/`. Antes de crear un componente en un
módulo, comprobar aquí. Al añadir uno nuevo: fila en esta tabla + template si es patrón.

| Componente | Propósito / contrato resumido |
|---|---|
| `Table/ServerTable` | Listados server-side (§3) |
| `Table/formatters` | Formatters tipados: `money(dec)`, `date`, `statusBadge(enum)`, `boolean`, `link` |
| `Form/Field` + kit | Input, Textarea, `NumberInput` (decimales por contexto), `Select` con búsqueda remota (combobox), `DatePicker`, `Checkbox`, `Switch`, `Wysiwyg`, `Dropzone`, `LangTabs` (campos translatable) — todos integrados con react-hook-form + zod y con error/label/help homogéneos |
| `Form/FormActions` | Barra guardar/cancelar/guardar-y-seguir, estado dirty, atajos teclado |
| `Records/RecordEditLayout` | Esqueleto de Create/Edit: cabecera (título, estado, acciones), tabs, sidebar meta (auditoría), zona de contenido |
| `Records/ActionBar` | Acciones de ciclo de vida del documento (confirmar, facturar…) con confirmación y permisos |
| `Records/Tabs` | Tabs con lazy load (deferred props / partial reload) |
| `Lines/LineItemsEditor` | **El componente clave** (vision §4.3): grid editable de líneas de documento — buscador de artículo, 4 descuentos en cascada, stock por almacén, precios/tarifas, totales/margen/IVA en vivo, drag&drop. Único lugar con edición en grid. Cálculo SIEMPRE con `lib/line-calc` (espejo unit-testeado del cálculo backend) |
| `Feedback/ConfirmDialog` | Confirmaciones destructivas/irreversibles (borrar, remesar) |
| `Feedback/EmptyState` · `ErrorBoundary` | Vacíos con CTA · captura de errores de página |
| `ui/*` | Primitivas shadcn (Button, Dialog, DropdownMenu, Badge, Tooltip…) |
| `SearchCommand` | Buscador global (⌘K) multi-entidad |
| `NotificationsPanel` | Campana + panel realtime (Echo/Reverb) |
| `CompanySwitcher` / `WorksiteSwitcher` | Selector de empresa activa / obra en topbar |

## 5. Patrones de página

Toda pantalla del ERP es uno de estos 4 patrones (si no encaja, se discute en la propuesta
OpenSpec antes de inventar un quinto):

1. **Index** = `AppLayout` + cabecera (título + crear) + `ServerTable`.
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
