## 1. Backend — contrato table + export

- [x] 1.1 Ampliar `TableRequest`: `export` ∈ `xlsx|csv|pdf`; documentar cap de filas export
- [x] 1.2 Extender `TableBuilder` (o helper) para rama export: aplicar filtros/sort, sin
      paginación de UI, devolver descarga o stream; fail seguro si supera cap
- [x] 1.3 Ampliar `ModuleTableRequest` filterable a columnas del Index (p. ej. `name`,
      `slug`, `status`, `level` + `search` existente)
- [x] 1.4 `ModuleController@table`: rama `export=xlsx` (`maatwebsite/excel`) y
      `export=pdf` (`barryvdh/laravel-dompdf`); misma Policy `viewAny`
- [x] 1.5 Verificar paquetes canon instalados; si faltan, `composer require` según
      `libraries.md` (sin añadir deps fuera de canon)

## 2. Frontend — ServerTable de referencia

- [x] 2.1 Ampliar props `ServerTable`: `persistKey`, `exportable`, `rowActions`,
      header filter defs por columna, page size, locale reactivo
- [x] 2.2 Header filters tipados → serializan a `filter[]` del protocolo table
- [x] 2.3 Columna Acciones fija derecha: título centrado, iconos + tooltips i18n,
      Delete último + `ConfirmDialog` cuando se declare
- [x] 2.4 Toolbar: mostrar/ocultar columnas (checkboxes), page size, botones Excel/PDF
      si `exportable`; persistencia `localStorage` clave `erp.table.<persistKey>.<userId>`
- [x] 2.5 i18n Tabulator (paginador y textos UI) al locale de sesión; al cambiar idioma
      sin F5 actualizar títulos/tooltips/paginador (`ca` con override de strings si hace
      falta)
- [x] 2.6 Estilos toolbar alineados a tokens / `tabulator-erp.css` (sin CSS Tabulator
      adicional)

## 3. Frontend — AdvancedSearchModal

- [x] 3.1 Crear `Components/Table/AdvancedSearchModal` (Dialog): fields por props,
      `onApply(filters)` al canal `filter[]`
- [x] 3.2 Documentar integración ActionBar: primer CTA tras Volver cuando la página
      active advanced search
- [x] 3.3 Modules Index: **no** mostrar el botón (opt-in omitido / false)

## 4. Aplicación — Admin Modules Index

- [x] 4.1 Actualizar `Admin/Modules/Index.tsx`: columns + header filters, `persistKey`,
      `exportable`, `rowActions` solo Edit, sin advanced search
- [x] 4.2 Claves i18n nuevas (es/en/ca, A–Z) para toolbar, tooltips Acciones, export,
      paginador Tabulator si faltan
- [x] 4.3 Añadir `AdvancedSearchModal` y actualizar fila `ServerTable` en catálogo
      `frontend.md` §4

## 5. Norma

- [x] 5.1 Actualizar `docs/standards/frontend.md` §3 (header filters, rowActions,
      toolbar, localStorage, export xlsx+pdf, AdvancedSearch opt-in; Modules = referencia)
- [x] 5.2 Actualizar `templates-frontend.md` (y playbook/templates-backend de table si
      enlazan) al mismo contrato
- [x] 5.3 Norma: futuros Index preguntan a Luis el set de Acciones; excepción solo por
      escrito en design del change

## 6. Tests y DoD

- [x] 6.1 Feature: filtros whitelist Modules table (acepta campos filterable; rechaza /
      ignora no whitelist)
- [x] 6.2 Feature: export xlsx y pdf Modules respetan filtro y Policy `viewAny`
- [x] 6.3 Smoke/front o Feature Inertia: Index Modules sin CTA advanced search; Acciones
      Edit (cobertura razonable según harness actual)
- [x] 6.4 R-TEN-05: N/A para `Module` (catálogo global sin `company_id`); no inventar
      aislamiento tenant en esta entidad
- [x] 6.5 `vendor/bin/pint`, `vendor/bin/phpstan analyse`, `composer test` en verde
      (Feature/Architecture/Unit OK). E2E nuevo: N/A (listado CRUD; Feature 6.1–6.3).
      Rol 40: `npm run build` + suite Browser. Aserciones alineadas al chrome actual
      (dashboard sin «Inicio»; Index Módulos sin subtítulo antiguo, toolbar visible).
- [x] 6.6 Polish: i18n `acciones` → Acciones (mayúscula); borde suave header filters;
      spec + frontend.md actualizados
- [x] 6.7 Modules columnas: name, color, icon, level, created_at, status + Acciones;
      slug prohibido en cualquier Index ServerTable
- [x] 6.8 Alineación vertical celdas; fechas vía `lib/format` DATE_FORMATS (es/ca
      dd/mm/yyyy, en yyyy/mm/dd)
- [x] 6.9 Fechas y números alineados a la derecha (`valueKind` / `headerFilterKind`
      date|number → hozAlign right en ServerTable)
- [x] 6.10 Filtro date = intervalo datepicker (custom headerFilter Tabulator; nativo
      `date` = un día + Luxon opcional, insuficiente). Contrato `filter[]`:
      `type: date_between`, `value: { from?, to? }` ISO `yyyy-MM-dd`. Backend
      `whereDate` extremos abiertos. UI labels/placeholders vía DATE_FORMATS + i18n
      desde/hasta. Spec escenario formal → devolver a 10 si se exige en main specs.
- [x] 6.11 UX date filter: un trigger en cabecera + popover Desde/Hasta (Aplicar/
      Limpiar; Escape cancela). Prohibido dos `input[type=date]` apilados en la
      fila de filtros. Contrato `date_between` intacto. frontend.md actualizado.
- [x] 6.12 Header filters misma altura global (`--st-hf-height` en tabulator-erp.css
      para input/select/trigger date); aplica a todas las ServerTable.
- [x] 6.13 Acciones: botones fondo primario; danger rojo pastel; toggle status primero
      a la izquierda (Modules) vía PATCH JSON async sin recarga (`modules.status` +
      `kind: 'toggle'`). frontend.md / R-FE-03 actualizados.
- [x] 6.14 Guard shell: no desactivar catálogo `admin`/`workspace` (UpdateModule +
      sin toggle UI); evita 403 `module:admin` por Inactive global.
