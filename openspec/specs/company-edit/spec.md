## Purpose

Ficha Edit de Company: layout con tabs Info e Imágenes, formulario Info
alineado al Create (NIF), logo único por upload, sin Miembros en esta UI.

## Requirements

### Requirement: Edit Company con tabs Info e Imágenes
El Edit de Company SHALL usar layout con tabs al estilo ficha User
(`RecordEditLayout`). Tab Info: readout a la izquierda (logo rectangular
centrado V+H + info de la empresa) y formulario a la derecha. Tab
Imágenes: gestión del único logo. SHALL NOT mostrar link ni tab de
Miembros en este Edit.

#### Scenario: Tabs Info e Imágenes
- **WHEN** un actor autorizado abre el Edit de una company
- **THEN** ve tabs Info e Imágenes (sin tab/link de Miembros en esta UI)

#### Scenario: Readout Info
- **WHEN** está en el tab Info
- **THEN** el bloque izquierdo muestra el logo (rectangular, centrado
  vertical y horizontal) y la info de la empresa; a la derecha el
  formulario editable

### Requirement: Formulario Info de Company
El tab Info SHALL pedir name, tradename, nif y status. NIF SHALL estar
alineado al Create (required, validación de formato, maxLength 12).
SHALL NOT exponer campos de slug ni de logo en el formulario. Authz:
Policy update + perímetro de vínculo.

#### Scenario: Campos del Info
- **WHEN** el actor edita el tab Info
- **THEN** puede guardar name, tradename, nif y status; no hay inputs de
  slug ni logo

#### Scenario: NIF como Create
- **WHEN** se valida el nif en Update
- **THEN** aplica las mismas reglas que Create (required, formato, max 12,
  único)

### Requirement: Tab Imágenes — un solo logo por file input
El tab Imágenes SHALL permitir sustituir el único logo de la company
mediante un input file simple (sin galería). Tamaño máximo 500 KB. Al
subir, reemplaza el logo anterior. Si la company editada es la
CurrentCompany de la sesión, el aside SHALL reflejar el nuevo
`tenant.logo_url`.

#### Scenario: Sustituir logo
- **WHEN** el actor sube un fichero de imagen ≤ 500 KB en el tab Imágenes
- **THEN** se guarda como único logo de la company (sustituye el anterior)

#### Scenario: Logo en aside si es sesión
- **WHEN** se cambia el logo de la company que es la de sesión actual
- **THEN** el logo del aside se actualiza

#### Scenario: Company distinta de sesión
- **WHEN** se cambia el logo de una company que no es la de sesión
- **THEN** el aside de la sesión no cambia por ese upload

### Requirement: Edit sin slug/logo URL ni link miembros
El Edit SHALL NOT mostrar input de slug, ni logo como URL de texto, ni el
enlace “Gestionar miembros”.

#### Scenario: Sin slug/logo URL ni link miembros
- **WHEN** un actor abre el Edit de Company tras este change
- **THEN** no ve input de slug, ni logo_url de texto, ni el enlace
  “Gestionar miembros”
