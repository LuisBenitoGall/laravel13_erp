# Spec Delta

## ADDED Requirements

### Requirement: Excerpt y description traducibles en Edit Info
El tab Información del Edit de Company SHALL permitir editar `excerpt` y
`description` multi-idioma (`LangTabs`). `excerpt` SHALL ser textarea simple
con máximo 1000 caracteres por locale. `description` SHALL usar el
`Form/Wysiwyg` del kit. Ambos campos SHALL ser opcionales (nullable).
SHALL NOT mostrar estos inputs en Create.

#### Scenario: Edit Información con textos
- **WHEN** el actor edita una company en el tab Información
- **THEN** puede guardar excerpt (≤1000/locale) y description (Wysiwyg) por
  idioma activo

#### Scenario: Create sin excerpt/description
- **WHEN** el actor abre el Create de Company
- **THEN** no ve inputs de excerpt ni description

## MODIFIED Requirements

### Requirement: Formulario Info de Company
El tab Info SHALL pedir name, tradename, nif, status y, en Edit, los campos
traducibles excerpt y description. NIF SHALL estar alineado al Create
(required, validación de formato, maxLength 12). SHALL NOT exponer campos de
slug ni de logo en el formulario. Authz: Policy update + perímetro de vínculo.

#### Scenario: Campos del Info
- **WHEN** el actor edita el tab Info
- **THEN** puede guardar name, tradename, nif, status, excerpt y description;
  no hay inputs de slug ni logo

#### Scenario: NIF como Create
- **WHEN** se valida el nif en Update
- **THEN** aplica las mismas reglas que Create (required, formato, max 12,
  único)
