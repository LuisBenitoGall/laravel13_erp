# ERP v2 (SaaS) — Laravel 13 + Inertia + React

v2 multi-tenant del ERP Viuda Vila. Referencia funcional (v1, solo lectura):
`C:\laragon\www\laravel8_erp_viudavila_github`. Visión y decisiones de producto:
`docs/vision-v2.md`.

## Normativa obligatoria

**`docs/standards/` es normativo para TODO desarrollo.** Antes de:

- crear un módulo o entidad → leer `docs/standards/module-playbook.md` y usar el skill `/new-module`
- escribir código backend → cumplir `docs/standards/architecture.md` (reglas `R-*`)
- escribir UI → `docs/standards/frontend.md` (patrones y catálogo de componentes; no crear componentes que ya existan)
- copiar un patrón → partir de `docs/standards/templates-*.md`, no inventar estructura
- añadir una dependencia → debe estar en `docs/standards/libraries.md` (si no está: justificar y añadirla al canon en el mismo PR)
- nombrar algo del dominio → `docs/standards/glossary.md` (código en inglés; UI en español vía i18n)
- tests de navegador → `docs/standards/e2e.md` (Pest browser; sin sandbox Playwright aparte)

Excepciones a los estándares: solo declaradas por escrito en el design de OpenSpec del cambio.
Si un estándar está mal: corregir el estándar primero, después seguir.

## Reglas capitales (resumen — el detalle manda en standards)

1. Multi-tenant BD única: todo modelo con `BelongsToCompany`; jamás `session('currentCompany')`; test de aislamiento cruzado obligatorio por entidad.
2. Sin empresa preferente hardcodeada (esto es un SaaS de N empresas iguales).
3. Autorización = Policy por modelo + permisos Spatie `<módulo>.<acción>`. Nada de middleware ad-hoc.
4. Controllers delgados → Actions transaccionales → eventos de dominio. Cross-domain: leer libre, escribir SOLO vía Action del dominio propietario o evento (matriz en `module-map.md`).
5. Estados = enums PHP con transiciones sobre columna `string` (jamás `ENUM` de MySQL); numeración solo vía `NumberingService`; decimales 3 venta / 4 coste / 2 contable; MySQL strict ON; nada de `serialize()`.
6. Contabilidad solo por eventos → LedgerGenerator → SeatBuilder → Seat (idempotente, cuadrado).
7. Listados = `<ServerTable>` (Tabulator) contra el contrato `*/table`; formularios = RHF + zod espejo del FormRequest; cero literales sin i18n.
8. Todo texto UI en español (+en/ca) vía i18n; identificadores en inglés según glosario.

## Flujo de trabajo

OpenSpec: `/opsx:propose` → revisar → `/opsx:apply` → `/opsx:archive`. Los cambios de
código nacen de una propuesta; la ficha del módulo (`docs/modules/<módulo>.md`) se crea
antes de proponer (checklist de vinculaciones incluida).

Metodología de roles (transversal Claude/Cursor/Codex): fichas canónicas en `docs/agents/`
— 10-architecture (propose), 20-implementation (apply), 30-testing (verificar DoD +
archive). Al ejecutar cada fase, actuar según la ficha del rol correspondiente. El estado
del change vive en los artefactos OpenSpec: si conversación y artefactos chocan, mandan
los artefactos; regla de oro: no inventar requisitos no especificados.

`/opsx:explore` no es un rol (no implementa, no ejecuta comandos OpenSpec). Si en una
sesión de explore se pide "redacta/prepara el prompt" para 10-architecture, seguir el
protocolo estricto de `docs/agents/README.md` §"Redactar el brief de handoff": usar
`docs/prompts/template_prompt.md` tal cual, ceñirse solo a lo debatido (preguntar ante
cualquier duda, cuantas veces haga falta, nunca inferir) y no ejecutar nada mientras se
redacta.

## Comandos

- `composer dev` — serve + queue + pail + vite
- `composer test` — suite completa (Pest cuando esté instalado; incluye tests de arquitectura)
- `vendor/bin/pint` · `vendor/bin/phpstan analyse` — antes de dar nada por terminado

## Estado actual

Esqueleto recién instalado (Fase 0 sin arrancar): no hay dominios, ni git, ni canon de
librerías instalado aún. Primer trabajo real = change de Fase 0 (tooling de
`docs/standards/tooling.md` + piezas de soporte de `templates-backend.md` §12).
