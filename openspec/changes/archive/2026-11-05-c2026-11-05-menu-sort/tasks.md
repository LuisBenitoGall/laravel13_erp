# Tasks

## 1. Modelos

- [x] 1.1 Añadir `sort` a `$fillable` de `Module` y `Functionality`. Verificar
      que mass-assignment acepta el campo (factory/test puntual o inspección).

## 2. Catálogo de menú

- [x] 2.1 En `MenuCatalogCache`: orden Modules y Functionalities =
      `sort` ASC NULLS LAST + `slug` ASC; bump KEY a `menu.catalog.active.v3` y
      forget de claves anteriores (`v2`, legado). Verificar orden en query/test.
- [x] 2.2 Confirmar que `MenuBuilder` no reordena tras filtrar (preserva orden
      del catálogo). Verificar con Feature o aserción de secuencia.

## 3. Tests

- [x] 3.1 Feature: Modules visibles con distinto `sort` → raíces en orden spec
      (incl. igualdad → slug). Verificar Pest verde.
- [x] 3.2 Feature: Functionalities con distinto `sort` / NULL → hijos en orden
      spec. Verificar Pest verde.
- [x] 3.3 Ajustar tests de menú que asuman orden solo por slug. Verificar suite
      menú afectada en verde. (sin cambios: tests usan toContain / ítems únicos)
- [x] 3.4 R-TEN-05 / E2E / ficha módulo / vinculaciones V-*: no aplican (design).

## 4. Calidad

- [x] 4.1 `vendor/bin/pint --dirty` en PHP tocado.
- [x] 4.2 PHPStan paths tocados sin errores nuevos.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 / archive.
