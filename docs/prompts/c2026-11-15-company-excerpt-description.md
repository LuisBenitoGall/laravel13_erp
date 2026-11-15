<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-15-company-excerpt-description
Rule: 10-architecture
Summary: Campos traducibles `excerpt` y `description` en `companies`
  (nullables, tras `parent_id`), editables solo en Edit → tab Información.
  Mismo change crea el kit `Form/Wysiwyg` (tiptap) como primer consumidor.
  El Directorio consumirá estos textos en un change posterior.
Goals:
- Migración: `excerpt` y `description` nullables a continuación de
  `parent_id`; admiten traducciones.
- Edit tab Información: `excerpt` = Textarea simple limitado a 1000
  caracteres; `description` = Wysiwyg del kit Form.
- Create: sin inputs de excerpt/description.
- Incluir en este change el componente `Form/Wysiwyg` e instalación de
  `@tiptap/react` (opción A del explore: kit + campos juntos).
NonGoals:
- Mostrar excerpt/description en Card o Show del Directorio (change
  siguiente).
- Inputs de excerpt/description en el formulario de Create.
- Crear un Wysiwyg ad-hoc fuera del kit Form.
Skills:
- openspec-propose
- inertia-react-development
- agent-setup
- testing-best-practices
Notes:
- Origen: `/opsx-explore`. Hallazgo: `Wysiwyg` aún no existe en
  `Components/Form` (pendiente en `frontend.md`); `@tiptap/react` en
  `libraries.md` “por módulo”, no instalado. Decisión Luis: A = mismo
  change kit + Company.
- Decisión Luis: consumo en Directorio = change siguiente.
- Patrón traducciones ya usado: Spatie `HasTranslations` + `LangTabs`
  (p. ej. Module explanation).
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Campos excerpt y description en Company
El sistema SHALL persistir en `companies` los campos `excerpt` y
`description`, ambos nullables y situados a continuación de `parent_id`.
SHALL admitir traducciones en ambos.

#### Scenario: Columnas presentes
- **WHEN** se aplica la migración del change
- **THEN** existen `excerpt` y `description` nullables tras `parent_id`

### Requirement: Edición solo en tab Información
El sistema SHALL exponer la edición de `excerpt` y `description` solo en
el formulario de Edit de Company, en el tab Información. SHALL NOT
incluir esos inputs en el formulario de Create.

#### Scenario: Edit Información
- **WHEN** el actor edita una company en el tab Información
- **THEN** puede editar excerpt (textarea ≤1000) y description (Wysiwyg)
  con soporte multi-idioma

#### Scenario: Create sin campos
- **WHEN** el actor abre el Create de Company
- **THEN** no ve inputs de excerpt ni description

### Requirement: Kit Form Wysiwyg (tiptap)
El sistema SHALL añadir el componente de kit `Form/Wysiwyg` basado en
`@tiptap/react` (canon libraries) e instalar la dependencia en este
change, usándolo como control de `description` en Company Edit.

#### Scenario: Description usa el kit
- **WHEN** el actor edita description en tab Información
- **THEN** el control es el Wysiwyg del kit Form (no un editor ad-hoc)


## MODIFIED Requirements

### Requirement: Tab Información de Company Edit
(El Edit de Company con tab Información.) SHALL incorporar los campos
traducibles excerpt y description según los requisitos ADDED, sin
cambiar el resto de tabs ni el Create.

#### Scenario: Información ampliada
- **WHEN** el actor está en Edit → Información
- **THEN** ve los campos de identidad existentes más excerpt y
  description multi-idioma
