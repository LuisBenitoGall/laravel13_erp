# Tasks

> Entidad nueva `TableFilterPreset` (Admin/shell). Sin Functionality Spatie
> (recurso personal, patrón Preference). E2E §4: N/A. V-01..V-20: N/A salvo
> tenancy R-TEN-05 en presets. Docs Catalog: retirar Family↔Category N:N.

## 1. Datos TableFilterPreset

- [x] 1.1 Migración `table_filter_presets` (`company_id`, `user_id`,
      `table_key`, `name`, `filters` JSON, índices por
      user+company+table_key). Verificar `migrate`.
- [x] 1.2 Modelo `TableFilterPreset` + `BelongsToCompany` + `Auditable` +
      factory + `MAX_PER_TABLE = 5`. Verificar factory create.
- [x] 1.3 Actualizar `docs/modules/Catalog.md` (y module-map solo si cita
      N:N): retirar Family↔Category N:N del backlog con abandono explícito.
      Verificar que el backlog ya no lo lista como pendiente.

## 2. Backend presets

- [x] 2.1 Policy ownership + company de sesión (create/viewAny/update/delete
      propios). Verificar 403 cruzado otro user / otra company (R-TEN-05).
- [x] 2.2 Actions Create (tope 5 en transacción + nombre único por alcance) /
      Update (rename) / Delete. Verificar Feature: alta OK, 6º → 422, rename,
      delete.
- [x] 2.3 FormRequests + Resource + Controller JSON
      (`index`/`store`/`update`/`destroy`) y rutas autenticadas. Verificar
      Feature list scoped por `table_key` y respuestas JSON.

## 3. Kit Frontend ServerTable + modal

- [x] 3.1 Prop opt-in `onOpenAdvancedSearch` (o equivalente) en `ServerTable`:
      botón i18n a la izquierda de columnas; sin prop = sin botón. Verificar
      Modules Index sin botón.
- [x] 3.2 Evolucionar `AdvancedSearchModal` (o wrapper del kit): Limpiar /
      Aplicar / Guardar + lista presets (seleccionar solo rellena form;
      renombrar; borrar) vía API JSON/`useHttp`. Verificar contrato
      controlado + draft.
- [x] 3.3 Actualizar `docs/standards/frontend.md` §3.7 + fila catálogo
      componentes (toolbar canónico, presets, Limpiar). Verificar texto
      alineado a specs.

## 4. Products Index consumidor

- [x] 4.1 Ampliar `ProductTableRequest` filterable + query table
      (`family_id`, `category_id`, `tag_id` con whereHas) además de
      name/type/status. Verificar Feature table con esos filtros.
- [x] 4.2 Cablear Products Index: `persistKey`/`table_key`,
      `advancedFilterMode: merge`, botón + modal con campos name/type/status/
      family/category/tag + props de opciones Active. Verificar que el Index
      monta el kit y aplica/limpia.
- [x] 4.3 i18n es/en/ca claves nuevas (botón, presets, errores tope).
      Verificar sin literales hardcodeados en UI tocada.
- [x] 4.4 Suite filtrada del change verde (`php artisan test --compact`
      paths tocados) + Pint dirty + PHPStan en verde.

## Workflow follow-up

- `/opsx:apply` → **25-architecture-review** → 30-testing → `/opsx-archive`.
- E2E 40: no aplica.
