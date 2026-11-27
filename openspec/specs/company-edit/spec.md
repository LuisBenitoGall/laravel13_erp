## Purpose

Ficha Edit de Company: layout con tabs Info, Imágenes y Jerarquía;
formulario Info alineado al Create (NIF), logo único por upload, sin
Miembros en esta UI.

## Requirements

### Requirement: Edit Company con tabs Info e Imágenes
El Edit de Company SHALL usar layout con tabs al estilo ficha User
(`RecordEditLayout`). Tab Info: readout a la izquierda (logo rectangular
centrado V+H + info de la empresa) y formulario a la derecha. Tab
Imágenes: gestión del único logo. Tab jerarquía: padre e hijos (ver
capability `company-hierarchy`). SHALL NOT mostrar link ni tab de
Miembros en este Edit. SHALL NOT poner controles de jerarquía en el
Create.

#### Scenario: Tabs Info e Imágenes
- **WHEN** un actor autorizado abre el Edit de una company
- **THEN** ve tabs Info e Imágenes (sin tab/link de Miembros en esta UI)

#### Scenario: Readout Info
- **WHEN** está en el tab Info
- **THEN** el bloque izquierdo muestra el logo (rectangular, centrado
  vertical y horizontal) y la info de la empresa; a la derecha el
  formulario editable

#### Scenario: Tabs Info, Imágenes y jerarquía
- **WHEN** un actor abre el Edit tras este change
- **THEN** ve los tabs de Info e Imágenes y el nuevo tab de jerarquía

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

### Requirement: Edit Company bajo /admin/companies
El Edit de Company SHALL vivir bajo `/admin/companies/{id}/edit` (rutas
`admin.companies.*`). SHALL NOT usar el prefijo `/partners`.

#### Scenario: URL Edit
- **WHEN** un actor autorizado abre el Edit de una company
- **THEN** la URL está bajo `/admin/companies/.../edit`

### Requirement: Atribución de sectores en Edit Company
El Edit de Company SHALL permitir atribuir cero o más sectores Active del
catálogo global. SHALL NOT exigir sectores en Create. Authz del sync:
`company-sectors.update` + perímetro de vínculo (ver capability
`company-sectors-tab`).

#### Scenario: Guardar sectores
- **WHEN** el actor autorizado con `company-sectors.update` selecciona
  sectores y guarda en Edit
- **THEN** la company queda vinculada a esos sectores (N:N)

#### Scenario: Create sin UI de sectores
- **WHEN** un actor abre el Create de Company
- **THEN** no se exige ni se muestra la atribución de sectores como
  requisito del alta mínima

### Requirement: Tab Sectores fuera de Info
El Edit de Company SHALL exponer la atribución de sectores en un tab
dedicado `sectors`, no dentro del tab Info. Create SHALL permanecer sin
atribución de sectores.

#### Scenario: Tabs incluyen Sectores
- **WHEN** un actor con `company-sectors.view` abre el Edit
- **THEN** existen tabs Info, Imágenes, Jerarquía y Sectores (según
  permisos de cada uno)

#### Scenario: Info sin panel sectores
- **WHEN** se renderiza el tab Info
- **THEN** no incluye el panel de checkboxes de sectores

### Requirement: Sección Monedas en Edit Info
El tab Información del Edit de Company SHALL incluir una sección Monedas
(default + permitidas del catálogo Active) con guardado propio de sync.
SHALL NOT añadir un tab dedicado Monedas en este change. Create SHALL
permanecer sin atribución de monedas.

#### Scenario: Sección dentro de Información
- **WHEN** un actor autorizado abre Edit Company
- **THEN** ve Monedas dentro del tab Información, no como tab hermano de
  Imágenes/Jerarquía/Sectores

#### Scenario: Guardar monedas en Edit
- **WHEN** el actor guarda default y permitidas desde la sección Monedas
- **THEN** el pivote de esa Company queda sincronizado
