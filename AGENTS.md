# ERP v2 (SaaS) — instrucciones para agentes

> Puente estándar `AGENTS.md` (Codex y cualquier herramienta que lo soporte). Para evitar
> divergencias, este fichero NO duplica las reglas: apunta a las fuentes canónicas.
> En Claude Code el equivalente es `CLAUDE.md`; en Cursor, `.cursor/rules/00-project.mdc`.

## Lecturas obligatorias, en este orden

1. **`CLAUDE.md`** (raíz) — contexto del proyecto, reglas capitales y comandos. Léelo
   entero: obliga igual aunque estés en otra herramienta.
2. **`docs/standards/README.md`** — índice de la normativa obligatoria (playbook de
   módulo, reglas R-*, templates, glosario, canon de librerías, E2E).
3. **`docs/agents/README.md`** — metodología de roles.

## Metodología de roles

Trabaja siempre COMO UNO de los tres roles (fichas canónicas en `docs/agents/`):

| Si vas a… | Actúa como | Comando |
|---|---|---|
| Proponer un change, redactar ficha de módulo, decidir diseño | `docs/agents/10-architecture.md` | `/opsx-propose` |
| Implementar tasks de un change ya propuesto | `docs/agents/20-implementation.md` | `/opsx-apply` |
| Verificar, endurecer tests, comprobar DoD y archivar | `docs/agents/30-testing.md` | `/opsx-archive` |

Lee la ficha del rol ENTERA antes de empezar y respeta su gate de salida. El estado del
change vive en `openspec/changes/<change>/` (los skills están en `.codex/skills/`); si
conversación y artefactos chocan, mandan los artefactos. Regla de oro: **no inventar
requisitos** — opciones A/B anotadas en el artefacto y decide 10-architecture (o Luis).

`/opsx-explore` no es un rol: no implementa, no ejecuta comandos OpenSpec. Si en una
sesión de explore se pide "redacta/prepara el prompt" para 10-architecture, sigue el
protocolo estricto de `docs/agents/README.md` §"Redactar el brief de handoff": usa
`docs/prompts/template_prompt.md` tal cual, cíñete solo a lo debatido (pregunta ante
cualquier duda, cuantas veces haga falta, nunca infieras) y no ejecutes nada mientras
redactas — el resultado se entrega en el chat para copiar y pegar.

## Mínimos innegociables (detalle en las fuentes de arriba)

- Multi-tenant BD única: `BelongsToCompany` en todo modelo; test de aislamiento de tenant
  obligatorio por entidad; jamás `session('currentCompany')`.
- Policies + permisos Spatie; Actions transaccionales; eventos entre dominios.
- Código en inglés según `docs/standards/glossary.md`; UI en español vía i18n.
- Dependencias solo del canon `docs/standards/libraries.md`.
- Antes de dar nada por terminado: `composer test`, `vendor/bin/pint`,
  `vendor/bin/phpstan analyse` en verde.
