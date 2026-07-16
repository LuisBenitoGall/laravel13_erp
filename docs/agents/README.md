# Metodología de agentes (roles 10/20/30)

El desarrollo se organiza en **tres roles diferenciados** alineados con el ciclo OpenSpec.
Cada rol tiene su ficha canónica en esta carpeta — **agnóstica de herramienta**: vale igual
para Claude Code, Cursor o Codex. Cada herramienta la carga con un adaptador fino
(`.cursor/rules/*.mdc`, `.claude/agents/*.md`, `AGENTS.md`), que nunca duplica contenido.

| Rol | Ficha | Comando OpenSpec | Fase del playbook | Modelo recomendado |
|---|---|---|---|---|
| 10-architecture | [10-architecture.md](10-architecture.md) | propose | §1–2 (ficha + propuesta) | El más potente disponible |
| 20-implementation | [20-implementation.md](20-implementation.md) | apply | §3–6 (datos/backend/frontend/vinculaciones) | Tier medio (Sonnet o equivalente) |
| 30-testing | [30-testing.md](30-testing.md) | archive (tras verificar) | §7–9 (tests + DoD + archivado) | Tier medio; potente para revisar diffs de dominios críticos |

Nomenclatura de comandos: en Claude Code son `/opsx:propose`, `/opsx:apply`, `/opsx:archive`,
`/opsx:sync`; en Cursor/Codex, `/opsx-propose`, `/opsx-apply`, etc. Son las mismas skills.

## `/opsx:explore` no es un cuarto rol

`explore` es una **skill/postura de pensamiento**, no un rol con ficha ni gate propio:
no tiene alcance de herramientas distinto, ni modelo fijo, ni Definition of Ready/Done.
Por eso no se crea un `.claude/agents/05-explore.md` ni equivalente en otras herramientas.

- **Nunca implementa código.** Puede crear/actualizar artefactos OpenSpec si se le pide
  (eso es capturar pensamiento, no implementar) — el resto es solo lectura del repo.
- **Se usa en dos modos:**
  1. **En la conversación principal** (caso normal): se invoca directamente, sin pasar
     por el Agent tool, para aprovechar todo el contexto ya acumulado en la sesión.
  2. **Delegado como subagente `10-architecture`** cuando conviene aislar contexto o
     paralelizar la exploración de varias opciones a la vez — su misión ("pensar, no
     implementar, no dejar huecos") ya coincide con la de explore, así que no hace
     falta redefinir nada; el modelo se puede ajustar puntualmente con el override de
     esa llamada sin crear un agente nuevo.
- **Dónde encaja respecto a 10/20/30:**
  - Antes de `10-architecture`, cuando el problema aún no está maduro para una ficha de
    módulo o una propuesta (comparar opciones, investigar v1, dejar que la forma del
    problema emerja).
  - Como destino del *handoff* cuando `20-implementation` o `30-testing` topan con una
    ambigüedad real: su protocolo ya dice "anótala y devuelve a 10-architecture" — el
    "pensarla" ocurre en una sesión de explore (de 10-architecture), no dentro de 20/30.
- **`20-implementation` y `30-testing` no entran en modo explore por su cuenta.** Si
  detectan que necesitan pensar en vez de ejecutar/verificar, es señal de escalar, no de
  improvisar una decisión de diseño bajo su propio rol.

### Redactar el brief de handoff (regla estricta)

Cuando, dentro de una sesión de `explore`, se pida explícitamente **"redacta/prepara el
prompt"** para pasarlo a `10-architecture`, se aplican estas reglas sin excepción:

1. **Formato**: usar SIEMPRE la plantilla
   [`docs/prompts/template_prompt.md`](../prompts/template_prompt.md) tal cual — no
   inventar una estructura distinta ni omitir secciones.
2. **Fidelidad estricta a lo debatido**: cada campo (Summary, Goals, NonGoals, Notes,
   Requirements/Scenarios...) sale ÚNICAMENTE de lo hablado en la conversación. Prohibido
   añadir objetivos, alcance, reglas de negocio o decisiones no mencionadas explícitamente
   — ni por completar, ni por generalizar, ni por "lo lógico sería".
3. **Duda → preguntar, no inferir**: si falta información para rellenar un campo, o el
   debate dejó algo ambiguo, se PREGUNTA al usuario — cuantas veces sea necesario, sin
   límite de rondas. Nunca se rellena un hueco por inferencia o suposición razonable; esa
   decisión le corresponde a `10-architecture`, no a este paso.
4. **No ejecuta nada**: redactar el brief es un acto de escritura de texto en el chat, no
   una acción sobre el proyecto. Mientras dura esta tarea, prohibido crear el change de
   OpenSpec (`openspec new change`), invocar `/opsx:propose`, escribir código o tocar
   cualquier fichero del repo. Por defecto el prompt se entrega en el chat para copiar y
   pegar; solo se escribe a disco si el usuario lo pide explícitamente.
5. **Entrega**: el resultado es un bloque de texto listo para pegar como primer mensaje
   de un subagente `10-architecture` fresco (o para continuar en el mismo hilo pidiendo la
   propuesta directamente).

## El contrato de handoff son los artefactos, no la conversación

Todo el estado vive en el repo: `openspec/changes/<change>/` (proposal, design, specs,
`tasks.md` con checkboxes) + `docs/modules/<módulo>.md` (ficha). Consecuencias:

1. **Se puede cambiar de herramienta o de modelo a mitad de un change sin pérdida**: el
   siguiente agente lee los artefactos y continúa. Por eso conviene degradar a modelos
   baratos en implementación: heredan un plan cerrado, no una conversación.
2. Si hay conflicto entre lo hablado y los artefactos, **mandan los artefactos**.
3. Un rol no invade al siguiente: cada uno termina actualizando los artefactos, que son
   la única entrada del siguiente.

## Regla de oro compartida: no inventar requisitos

Si una decisión de negocio no está en spec/design/tasks ni en `docs/vision-v2.md`:
**no se inventa**. Se plantean opciones (A/B con consecuencias), se anota en el artefacto
correspondiente y decide 10-architecture (o Luis, si es de negocio). Esta regla obliga a
los tres roles.

## Jerarquía normativa (recordatorio)

`docs/standards/` obliga a los tres roles ([README](../standards/README.md)). Las
excepciones solo existen si el design del change las declara por escrito. Si un estándar
está mal: se corrige el estándar primero, después se sigue.
