<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-27-server-table-advanced-filter-presets
Rule: 10-architecture
Summary: Evolucionar el kit de filtros avanzados de ServerTable (modal
  reutilizable, botón en toolbar) y presets guardados por usuario (máx. 5
  por tabla), con primer cableado en Products Index. Retirar Family↔Category
  N:N del backlog de Catalog.
Goals:
- **Kit genérico reutilizable** de filtros avanzados para dashboards/
  Index con ServerTable (no solo Products).
- Evolucionar / envolver el `AdvancedSearchModal` existente (cáscara ya
  en `Components/Table`; hoy sin consumidores Index): los **campos del
  modal se pasan por parámetro** (varían por dashboard).
- **Botón** para abrir el modal en la toolbar de `ServerTable`, a la
  **izquierda** de «Mostrar / ocultar columnas» (no como CTA de
  ViewActionBar, salvo que 10 deje compat opt-in).
- Modal: **Limpiar filtros**; **Aplicar** (canal `filter[]` existente
  `advancedFilters` + `merge`|`replace`); **Guardar** configuración.
- **Presets por usuario** (y company de sesión): varias configs nombradas
  por listado/tabla (`persistKey`/scope que fije 10); **máximo 5** por
  user+company+tabla.
- Al **seleccionar** un preset: **solo rellena el form** del modal (no
  aplica solo a la tabla); el usuario pulsa Aplicar después.
- En este change: **crear, cargar (rellenar), renombrar y borrar**
  presets.
- Primer consumidor listo: **Products Index** (campos avanzados alineados
  a lo filterable del table + lo que 10 cierre para taxonomía si aplica).
- Actualizar `frontend.md` / catálogo componentes; tests Pest (presets
  tope 5, aislamiento, authz user+company; canal filter).
- Docs Catalog: **retirar** Family↔Category N:N del backlog (Luis
  renuncia definitivamente).
NonGoals:
- Implementar Family↔Category N:N (abandonado).
- Presets compartidos entre usuarios; presets globales de company.
- Más de 5 presets por tabla.
- Aplicar automáticamente al elegir preset (solo rellenar form).
- Cablear todos los Index del ERP (solo kit + Products en este change).
- Cambiar el modelo `Preference` de favoritos URL (entidad distinta).
- Campos exactos del modal Products / `merge` vs `replace`: si no bastan
  los debatidos → decide 10 (o A/B a Luis).
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore` post taxonomía; filtros Index Products + kit
  reutilizable; Family↔Category descartado.
- Base técnica: `AdvancedSearchModal` + `ServerTable` `advancedFilters` /
  `advancedFilterMode` (`docs/standards/frontend.md` §3.7). Modal existe;
  **ningún Index lo monta** hoy (Modules sin él).
- `Preference` = favoritos de vista (name+url) ≠ presets de filtros →
  entidad/tabla nueva (nombre lo fija 10).
- Decisiones Luis: (1) conocer/evolucionar AdvancedSearchModal; (2)
  preset solo rellena form; (3) borrar+renombrar sí; (4) máx 5/tabla;
  (5) kit genérico + Products listo.
- Misión explore: **solo brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: Modal de filtros avanzados reutilizable en toolbar
El sistema SHALL exponer un módulo/componente de filtros avanzados
reutilizable cuyo contenido de campos se configure por el Index
consumidor. SHALL ofrecer un botón en la toolbar de ServerTable a la
izquierda de «Mostrar / ocultar columnas» para abrir el modal. SHALL
reutilizar el canal `filter[]` de ServerTable (`advancedFilters`).

#### Scenario: Abrir desde toolbar
- **WHEN** un Index opta por filtros avanzados y el actor pulsa el botón
  de la toolbar
- **THEN** se abre el modal con los campos declarados por ese Index

#### Scenario: Aplicar
- **WHEN** el actor aplica filtros del modal
- **THEN** la tabla recarga usando el canal `filter` (merge o replace
  según el Index)

#### Scenario: Limpiar
- **WHEN** el actor pulsa limpiar en el modal
- **THEN** los campos del draft quedan vacíos y la limpieza se refleja
  según el contrato onClear/apply que cierre 10

### Requirement: Presets de filtros por usuario
El sistema SHALL permitir al usuario guardar configuraciones nombradas de
filtros del modal, con alcance user + company de sesión + identificador
de listado/tabla. SHALL limitar a **5** presets por ese alcance. SHALL
permitir listar, seleccionar (rellena el form sin aplicar), renombrar y
borrar presets propios.

#### Scenario: Guardar preset
- **WHEN** un actor guarda la configuración actual del modal con un nombre
  válido y no supera el tope 5
- **THEN** el preset queda persistido para ese user+company+tabla

#### Scenario: Tope 5
- **WHEN** ya existen 5 presets para esa tabla y el actor intenta guardar
  otro
- **THEN** la operación se rechaza con error claro

#### Scenario: Cargar preset
- **WHEN** el actor selecciona un preset guardado
- **THEN** los campos del modal se rellenan con esa configuración y la
  tabla no cambia hasta que pulse Aplicar

#### Scenario: Renombrar y borrar
- **WHEN** el actor renombra o borra un preset propio
- **THEN** el cambio queda persistido y deja de listarse si fue borrado

#### Scenario: Aislamiento
- **WHEN** un actor lista presets
- **THEN** solo ve los suyos de la company de sesión para esa tabla (no
  los de otro user ni otra company)

### Requirement: Products Index cableado
El Index de Products SHALL montar el kit de filtros avanzados (botón +
modal + presets) listo para usar, con campos avanzados que 10 alinee al
listado de productos.

#### Scenario: Products abre filtros
- **WHEN** un actor autorizado abre el Index de productos y el modal de
  filtros avanzados
- **THEN** puede limpiar, aplicar, guardar/cargar presets según este
  change


## MODIFIED Requirements

### Requirement: AdvancedSearchModal / ServerTable advanced filters
(docs/frontend / server-table-reference según 10)
El disparador canónico de filtros avanzados SHALL poder vivir en la
toolbar de ServerTable (izquierda de columnas). El modal SHALL soportar
el flujo de presets descrito. La documentación del form-kit/ServerTable
SHALL reflejar el nuevo contrato.

#### Scenario: Documentación alineada
- **WHEN** se consulte frontend.md / catálogo de componentes tras el
  change
- **THEN** describe botón en toolbar, limpia/guarda presets y consumidor
  Products

### Requirement: Backlog Catalog (ficha módulo)
La ficha `docs/modules/Catalog.md` (y module-map si aplica) SHALL dejar
de listar Family↔Category N:N como pendiente de desarrollo (abandono
explícito).

#### Scenario: Backlog sin N:N Family-Category
- **WHEN** se consulte el backlog de Catalog tras el change
- **THEN** no aparece Family↔Category N:N como trabajo futuro previsto
