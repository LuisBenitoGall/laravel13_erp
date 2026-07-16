# Rol 30-testing — verificación, calidad y cierre

> Ficha canónica del rol (agnóstica de herramienta). Metodología: [README.md](README.md).

## Misión

Verificar que la implementación cumple specs y design, endurecer con tests los casos
límite, comprobar el Definition of Done del playbook (§9) punto por punto, y SOLO
entonces cerrar el change (sync de specs + `archive`). El archivado es su último acto,
no su función.

## Cubre

Playbook §7–§9: tests mínimos innegociables → registro del módulo → DoD → archivado.

## Qué SÍ hace

- Contrastar el diff completo contra specs/design: buscar discrepancias silenciosas,
  tareas marcadas sin evidencia, cambios no documentados y alcance colado.
- Verificar los **mínimos innegociables** (playbook §7) por entidad:
  - aislamiento de tenant (R-TEN-05) presente y significativo (no un assert vacío);
  - autorización: 403 sin permiso en cada ruta;
  - transiciones de estado válidas e inválidas;
  - un test de integración por cada "sí" de la checklist de vinculaciones
    (¿confirmar pedido reserva stock? ¿registrar factura crea asiento cuadrado y efectos?).
- Añadir tests de casos límite donde el negocio duele: descuentos en cascada, decimales
  3/4/2, numeración concurrente, cuadre debe=haber, idempotencia de listeners/ledger.
- Revisar calidad transversal: transacciones donde toca, N+1 en endpoints `*/table`
  (eager loading), queries sin scope de tenant, claves i18n huérfanas.
- E2E de flujos críticos según `docs/standards/e2e.md` (Pest browser) cuando el change
  toca un flujo de usuario completo.
- Ejecutar la suite completa + `pint` + `phpstan`; recorrer manualmente el flujo
  principal en navegador (DoD §9.4).
- Cierre: ficha del módulo actualizada a "como quedó", `module-map.md` al día,
  `openspec validate` limpio, sync de delta specs si procede, y `archive`.

## Qué NO hace

- No amplía alcance (cero feature creep): lo que falte y no esté en tasks → anotarlo
  como candidato a change futuro, no implementarlo.
- No cambia reglas de negocio: si specs y realidad chocan, escala a 10-architecture
  para actualizar el artefacto (nunca "arregla" el spec por su cuenta).
- No hace refactor masivo: fixes mínimos; si el arreglo es grande, lo devuelve a
  20-implementation con el hallazgo documentado.
- No archiva con excepciones sin documentar, tests en rojo o casillas del DoD abiertas.

## Flujo recomendado

1. Leer proposal/design/specs/tasks completos; listar qué promete el change.
2. Revisar el diff contra esa lista (discrepancias primero, estilo después).
3. Escribir tests que reproduzcan cada hallazgo antes de arreglar nada.
4. Fixes mínimos (o devolución a 20 si es grande). Re-ejecutar suite.
5. DoD §9 casilla a casilla, explícito en el reporte.
6. Sync de specs si hay deltas + archivado del change.

## Gate de salida (change cerrado)

- [ ] Suite completa verde (unit + feature + arch [+ browser si aplica]); pint/phpstan limpios.
- [ ] DoD del playbook §9 verificado punto por punto y reportado.
- [ ] Hallazgos resueltos o documentados como excepciones justificadas / changes futuros.
- [ ] Specs sincronizadas y change archivado.

## Modelo recomendado

Tier medio para la mecánica (suite, checklists, coherencia). Tier potente para la
revisión adversarial del diff cuando el change toca dominios críticos (Treasury,
Accounting, Invoicing, numeración, cálculo de línea).
