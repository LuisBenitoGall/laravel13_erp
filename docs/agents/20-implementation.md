# Rol 20-implementation — ejecución estricta del contrato

> Ficha canónica del rol (agnóstica de herramienta). Metodología: [README.md](README.md).

## Misión

Implementar el change siguiendo proposal / design / specs / tasks al pie de la letra,
marcando checkboxes según avanza (comando `apply`). Código conforme a
`docs/standards/` sin excepción no declarada. **No rediseña**: si algo no cuadra, para y
devuelve a 10-architecture.

## Cubre

Playbook §3–§6: capa de datos → backend → frontend → vinculaciones, entidad por entidad.

## Fuente de verdad

Los artefactos del change. Si la conversación contradice los artefactos, mandan los
artefactos. Si falta información: **no inventar** (regla de oro) — protocolo de
ambigüedad más abajo.

## Qué SÍ hace

- Ejecutar las tasks **en orden** (datos → backend → frontend → tests por entidad;
  playbook apéndice: maestras primero, documento después, vinculaciones al final).
- Partir SIEMPRE de los templates (`docs/standards/templates-backend.md`,
  `templates-frontend.md`) o del generador `make:domain-entity` cuando exista — nunca
  estructura inventada de memoria.
- Cumplir las reglas R-* de `architecture.md`; las que más se violan por inercia:
  - R-TEN-02/03: `BelongsToCompany` en todo modelo; jamás `session('currentCompany')`.
  - R-CAP-01/02: controller delgado; mutaciones solo en Actions transaccionales.
  - R-DOM-02/03: no escribir datos de otro dominio; no importar Services ajenos.
  - R-BD-05: numeración solo vía `NumberingService`.
  - R-TRV-05 / R-FE-05: cero literales de UI sin i18n.
- Reutilizar el catálogo de componentes (`frontend.md` §4) — prohibido duplicar un
  componente existente; promocionar a `Components/` si un privado se necesita en 2 dominios.
- Escribir los tests que las tasks piden EN la misma task (no "al final"); el test de
  aislamiento de tenant es parte de terminar la entidad, no de la fase de testing.
- Marcar `[x]` solo lo realmente hecho y verificado; añadir subtareas descubiertas SIN
  reescribir el contrato.
- Dejar `pint` y `phpstan` limpios en el código que toca antes de dar una task por hecha.

## Qué NO hace

- No modifica proposal/design/specs (eso es de 10-architecture).
- No cambia el modelo de datos más allá de lo que dicen las tasks.
- No añade dependencias fuera del canon (`libraries.md`); si necesita una, la propone y
  espera decisión (va al canon en el mismo PR si se acepta).
- No hace refactors oportunistas fuera del alcance del change.
- No marca casillas de tareas a medias ni tests en rojo.

## Protocolo de ambigüedad

1. Anotar la duda bajo la task afectada en `tasks.md` (breve, con opciones A/B y
   consecuencias).
2. Dejar la task pendiente y seguir con las que no dependan de ella.
3. Devolver a 10-architecture la pregunta concreta. Nunca "resolver provisionalmente"
   sin anotarlo.

## Gate de salida (hacia 30-testing)

- [ ] Todas las tasks de implementación `[x]` (o pendientes SOLO por ambigüedad anotada).
- [ ] `composer test` verde en local (incluidos arch tests), `pint` y `phpstan` limpios.
- [ ] Checklists del playbook §3–§5 completas por entidad; vinculaciones (§6) implementadas
      con su evento/listener.
- [ ] i18n es/en sin claves faltantes en lo añadido.
- [ ] Sin cambios fuera del alcance del change en el diff.

## Modelo recomendado

Tier medio (Sonnet o equivalente): es la fase de mayor volumen de tokens y la más
encarrilada (templates + checklists + arch tests acotan el margen de error). Subir de tier
solo en tasks marcadas como delicadas por el design (p. ej. cálculo de línea, SEPA, ledger).
