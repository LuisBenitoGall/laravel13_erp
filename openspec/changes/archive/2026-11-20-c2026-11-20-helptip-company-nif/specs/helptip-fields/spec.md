# Spec Delta

## Purpose

Anclar Contents Help a campos de formulario vía icono info + code, con
primer consumidor NIF de empresa y promoción reproducible del catálogo
Help entre entornos.

## ADDED Requirements

### Requirement: HelpTip anclado a campos de formulario
El sistema SHALL permitir asociar un `code` de Content Help a un campo
de formulario mostrando un icono de información a la derecha del
control; al activarlo SHALL mostrar el tip (título/excerpt) del locale.
SHALL NOT mostrar tip si el code no existe o no está Active. Aplica
solo a kind Información (Help).

#### Scenario: Tip con code Active
- **WHEN** el campo tiene helpCode de un Help Active y el actor abre el
  icono
- **THEN** ve el popover con el contenido del tip en su idioma

#### Scenario: Code ausente o Inactive
- **WHEN** el helpCode no resuelve a un Help usable
- **THEN** no se muestra icono/tip útil y la página no falla

### Requirement: company-nif en Create/Edit empresa
Los formularios Create y Edit de Company SHALL mostrar el HelpTip con
code `company-nif` junto al campo NIF.

#### Scenario: NIF Create
- **WHEN** el actor abre Create empresa y existe Content Help
  `company-nif` Active
- **THEN** ve el icono info junto al NIF y el tip al activarlo

#### Scenario: NIF Edit
- **WHEN** el actor abre Edit empresa con el mismo code Active
- **THEN** el tip está disponible igual que en Create

### Requirement: Promoción de Contents Help entre entornos
El sistema SHALL proveer un fixture versionado de Help por `code`, un
seeder idempotente (upsert por code) y un comando de export desde BD
hacia ese fixture, para promover Help fijos sin copiar la base completa.

#### Scenario: Seeder upsert por code
- **WHEN** se ejecuta el seeder de Help con `company-nif` en el fixture
- **THEN** el Content Help queda insertado o actualizado por code sin
  borrar Help ajenos al fixture

#### Scenario: Export documentado
- **WHEN** un operador ejecuta el comando de export acordado
- **THEN** se actualiza el artefacto fixture con Help Active globales
