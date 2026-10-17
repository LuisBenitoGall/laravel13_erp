<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-15-form-input-maxlength
Rule: 10-architecture
Summary: Poner límite de caracteres global en los inputs de texto mediante un componente
reutilizable (default maxLength 255, overrides explícitos), alineado con validación
backend (FormRequest max correspondiente), migrar todas las vistas Auth a ese componente,
dejar el patrón listo para formularios futuros, enforcement en CI, y rule/skill de agentes
para no olvidar maxLength en inputs.
Goals:
- Crear y usar componente de Input (aprovechando librerías/componentes existentes del
  canon, p. ej. shadcn/ui) con `maxLength={255}` por defecto.
- Campos más cortos: override explícito de `maxLength` en UI y el mismo `max:*` en el
  FormRequest correspondiente.
- Validación backend: cada input con su max alineado al maxLength del front (no solo UX
  HTML).
- Migrar todas las vistas Auth a ese componente (no inputs HTML a pelo).
- Dejar el patrón listo para formularios futuros (el default del componente evita tener
  que acordarse en cada form nuevo).
- Enforcement CI: que falle si aparece un input de texto sin límite.
- Crear rule o skill de agentes para garantizar que el parámetro maxLength vaya en los
  componentes de los inputs.
NonGoals:
- Textarea (con o sin Wysiwyg): no aplica este default ni este alcance.
- Un skill/rule como única solución sin componente runtime (descartado: el skill refuerza,
  no sustituye el componente + validación + CI).
Skills:
- frontend-design
- laravel-specialist
- laravel-patterns
Notes:
- Motivación observada: login/registro permiten teclear sin tope; backend de registro ya
  tiene `max:255` en varios campos, pero el front no expone `maxLength`.
- Decisión de producto: opción A (default en componente) + default 255 espejo de
  string/varchar(255).
- “Siempre componentes”; Auth pasa al kit, no parche puntual en `<input>` crudos.
- 10-architecture elige entre rule y/o skill de agentes (el brief exige crear al menos
  uno) y el mecanismo concreto de CI, sin salirse de este alcance.
- Change de `surname` en registro es otro hilo; no forma parte de este brief.


## ADDED Requirements

### Requirement: Input con maxLength por defecto
El sistema SHALL exponer un componente de Input de texto reutilizable (basado en el kit
del canon / librerías existentes) con `maxLength` por defecto 255. Los formularios
SHALL usar ese componente en lugar de `<input>` HTML suelto para campos de texto.

#### Scenario: Default 255
- **WHEN** se renderiza el Input del kit sin override de longitud
- **THEN** el control aplica `maxLength={255}`

#### Scenario: Override explícito
- **WHEN** un campo requiere un máximo distinto de 255 (p. ej. más corto)
- **THEN** se indica `maxLength` explícito en el componente y el FormRequest usa el
  mismo `max:*`

### Requirement: Validación backend alineada
Toda entrada de texto de formularios bajo este patrón SHALL validarse en FormRequest
con un `max` correspondiente al `maxLength` del Input.

#### Scenario: Rechazo por exceso
- **WHEN** se envía un valor que supera el `max` declarado para ese campo
- **THEN** se rechaza con error de validación

### Requirement: Auth migrado al Input del kit
Todas las vistas Auth con campos de texto SHALL usar el componente Input del kit (no
inputs crudos).

#### Scenario: Vistas Auth
- **WHEN** un visitante o usuario usa cualquier formulario Auth (login, registro y resto
  de vistas Auth con inputs de texto)
- **THEN** esos campos de texto usan el Input del kit con maxLength efectivo

### Requirement: Listo para formularios futuros
El default del componente SHALL aplicar a formularios nuevos sin forzar a repetir
`maxLength={255}` en cada pantalla.

#### Scenario: Formulario nuevo
- **WHEN** se crea un formulario futuro que usa el Input del kit sin override
- **THEN** queda limitado a 255 caracteres por el default del componente

### Requirement: Enforcement en CI
El CI SHALL fallar si aparece un input de texto sin límite de longitud conforme a la
regla que defina 10-architecture.

#### Scenario: Input sin límite
- **WHEN** se introduce un input de texto sin maxLength / fuera del componente con
  default
- **THEN** la comprobación de CI falla

### Requirement: Rule o skill de agentes
El change SHALL incluir una rule o skill de agentes que garantice que el parámetro
maxLength vaya en los componentes de los inputs.

#### Scenario: Agente crea o toca un Input
- **WHEN** un agente crea o modifica un formulario con inputs de texto
- **THEN** la rule/skill le obliga a respetar maxLength vía el componente (default u
  override explícito)

---

## MODIFIED Requirements

<!-- Ningún requisito MODIFIED nombrado explícitamente en el debate; el alcance es
     ADDED sobre el kit de formularios / Auth. 10-architecture enlazará con specs
     existentes si procede al proponer. -->
