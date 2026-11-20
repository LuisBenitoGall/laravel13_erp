## Purpose

Anclar Contents Help a campos de formulario vía icono info + code, con
convención `{helpScope}.{fieldKey}`, consumidor NIF de empresa
(`companies.nif`) y promoción reproducible del catálogo Help entre
entornos.

## Requirements

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

### Requirement: Resolución automática de code HelpTip
El sistema SHALL resolver el code de ayuda de un Field como
`{helpScope}.{fieldKey}` cuando el formulario provee helpScope y el
Field tiene identidad de campo resoluble, salvo override por helpCode
explícito. SHALL mostrar el icono solo si existe Content Help Active
con ese code.

#### Scenario: Tip por convención
- **WHEN** un form tiene helpScope `companies`, el Field resuelve
  fieldKey `nif`, y existe Help Active `companies.nif`
- **THEN** el actor ve el HelpTip junto al NIF

#### Scenario: Sin Content
- **WHEN** el code derivado no tiene Help Active
- **THEN** no se muestra icono y la página no falla

#### Scenario: Override helpCode
- **WHEN** el Field declara helpCode explícito
- **THEN** se usa ese code en lugar del derivado

### Requirement: company-nif en Create/Edit empresa
Los formularios Create y Edit de Company SHALL mostrar el HelpTip del
NIF mediante convención `companies.nif` (helpScope + fieldKey), no
mediante un helpCode hardcodeado `company-nif`.

#### Scenario: NIF Create
- **WHEN** el actor abre Create empresa y existe Content Help
  `companies.nif` Active
- **THEN** ve el icono info junto al NIF y el tip al activarlo

#### Scenario: NIF Edit
- **WHEN** el actor abre Edit empresa con el mismo code Active
- **THEN** el tip está disponible igual que en Create

### Requirement: Migración company-nif → companies.nif
El sistema SHALL alinear el tip NIF de empresa con la convención
`companies.nif` (actualizar Content/fixture/seeder y el consumidor).

#### Scenario: NIF tras migración
- **WHEN** el actor abre Create/Edit empresa y `companies.nif` está
  Active
- **THEN** ve el tip en el campo NIF

### Requirement: Promoción de Contents Help entre entornos
El sistema SHALL proveer un fixture versionado de Help por `code`, un
seeder idempotente (upsert por code) y un comando de export desde BD
hacia ese fixture, para promover Help fijos sin copiar la base completa.
El fixture canónico del NIF SHALL usar code `companies.nif`.

#### Scenario: Seeder upsert por code
- **WHEN** se ejecuta el seeder de Help con `companies.nif` en el
  fixture
- **THEN** el Content Help queda insertado o actualizado por code sin
  borrar Help ajenos al fixture

#### Scenario: Export documentado
- **WHEN** un operador ejecuta el comando de export acordado
- **THEN** se actualiza el artefacto fixture con Help Active globales
