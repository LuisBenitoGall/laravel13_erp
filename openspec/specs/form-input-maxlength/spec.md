## Purpose

Input del kit UI con `maxLength` default 255, alineación FormRequest, Auth migrado, CI de arquitectura y rule de agentes; Textarea excluido.

## Requirements

### Requirement: Input del kit con maxLength por defecto
El sistema SHALL exponer un componente de Input de texto reutilizable en el kit UI
(basado en shadcn/ui) con `maxLength` por defecto 255. Los formularios SHALL usar ese
componente para campos de texto (`text`, `email`, `password`, `search`, `tel`, `url`) en
lugar de `<input>` HTML suelto. Textarea (con o sin Wysiwyg) queda fuera de este
requisito.

#### Scenario: Default 255
- **WHEN** se renderiza el Input del kit sin override de longitud
- **THEN** el control aplica `maxLength={255}`

#### Scenario: Override explícito
- **WHEN** un campo requiere un máximo distinto de 255
- **THEN** se indica `maxLength` explícito en el componente y el FormRequest
  correspondiente usa el mismo `max:*`

### Requirement: Validación backend alineada
Toda entrada de texto bajo este patrón SHALL validarse en el servidor (FormRequest o
equivalente) con un `max` correspondiente al `maxLength` del Input.

#### Scenario: Rechazo por exceso
- **WHEN** se envía un valor que supera el `max` declarado para ese campo
- **THEN** se rechaza con error de validación y no se aplica el cambio

### Requirement: Auth migrado al Input del kit
Todas las vistas Auth con campos de texto SHALL usar el Input del kit (no inputs crudos
de texto). Quedan incluidos login, registro, forgot password, reset password y onboard
company (campos de texto). Controles no texto (`checkbox`, `file`, etc.) no están
obligados a usar este Input.

#### Scenario: Vistas Auth con texto
- **WHEN** un visitante usa un formulario Auth que pide datos de texto
- **THEN** esos campos usan el Input del kit con `maxLength` efectivo (255 u override)

#### Scenario: NIF en onboarding
- **WHEN** se muestra el campo nif en el alta de company
- **THEN** el Input usa `maxLength={32}` y el FormRequest valida `max:32`

### Requirement: Listo para formularios futuros
El default del componente SHALL aplicar a formularios nuevos que usen el Input del kit
sin exigir repetir `maxLength={255}` en cada pantalla. El estándar frontend SHALL
documentar esta regla.

#### Scenario: Formulario nuevo sin override
- **WHEN** se crea un formulario futuro que usa el Input del kit sin override
- **THEN** queda limitado a 255 caracteres por el default del componente

### Requirement: Enforcement en CI
La suite de arquitectura/CI SHALL fallar si en las páginas Auth aparece un `<input>` de
texto crudo (tipos de texto definidos arriba) en lugar del Input del kit, o si el Input
del kit no declara el default 255.

#### Scenario: Input de texto crudo en Auth
- **WHEN** una página Auth introduce un `<input>` de texto sin usar el componente del kit
- **THEN** el test de arquitectura falla

#### Scenario: Default ausente en el kit
- **WHEN** el componente Input del kit no aplica default `maxLength` 255
- **THEN** el test de arquitectura falla

### Requirement: Rule de agentes
El change SHALL incluir una Cursor rule que obligue a los agentes a usar el Input del
kit con `maxLength` (default u override explícito alineado al FormRequest) al crear o
modificar formularios con campos de texto.

#### Scenario: Agente crea o toca un campo de texto
- **WHEN** un agente crea o modifica un formulario con inputs de texto
- **THEN** la rule le indica usar el Input del kit y respetar maxLength (default 255 u
  override + `max` backend espejo)
