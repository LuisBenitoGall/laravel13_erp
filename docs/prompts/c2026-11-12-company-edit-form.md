<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-12-company-edit-form
Rule: 10-architecture
Summary: Reformar el Edit de Company al layout con tabs (como Users): tab Info
  con readout + formulario (sin slug ni logo en UI) y tab Imágenes con un
  único file input que sustituye el logo (max 500 KB). Slug siempre
  automático y se regenera en update manteniendo unicidad. Aside de sesión
  refleja el logo si la company editada es la de CurrentCompany.
Goals:
- Edit Company = `RecordEditLayout` + tabs; primer tab Info (espejo readout
  de perfil usuario: bloque resumen a la izquierda con logo e info);
  segundo tab Imágenes.
- Tab Info: form name, tradename, nif, status. NIF alineado a Create
  (required, formato, maxLength razonable / max 12). Sin campo slug ni
  campo logo en UI.
- Slug: fuera de la UI; siempre automático; **sí se regenera tras update**
  y debe seguir siendo único.
- Tab Imágenes: un solo logo (no galería); input file simple que sustituye
  cada vez; max 500 KB; sin flag `public`; sin “featured” (solo hay uno).
- Logo en readout: rectangular, centrado vertical y horizontal.
- Si la company editada es la de sesión → al cambiar el logo se actualiza
  el logo del aside (`tenant.logo_url`).
- Quitar el link “Gestionar miembros” del Edit (Members irá en un tab
  más adelante).
NonGoals:
- Galería de imágenes / entidad `CompanyImage` / marcar featured.
- Flag `public` en imágenes de empresa.
- Tab o UI de Miembros en este change (ruta/Members existentes pueden
  quedar; no se enlazan desde este Edit).
- Cambiar el onboarding de la primera empresa (se mantiene como está).
- Paso de pago por nueva empresa (futuro).
- Create de Company (ya cerrado en change previo).
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore` tras Create; Luis corrigió el flujo (explore
  antes de propose).
- Estado actual: `Partners/Companies/Edit.tsx` = form plano + link
  miembros; `CompanyForm` en edit aún pide slug y logo_url de texto;
  `UpdateCompanyRequest` slug required editable + nif nullable max 32 +
  logo string; aside ya lee `companies.logo` vía HandleInertiaRequests.
- Fuente de verdad del logo: columna `companies.logo` (un fichero; al
  sustituir, reemplazo).
- Create ya: slug auto UniqueSlug, NIF único+formato max 12, sin logo en
  UI, redirect a Edit.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Edit Company con tabs Info e Imágenes
El Edit de Company SHALL usar layout con tabs al estilo ficha User
(`RecordEditLayout`). Tab Info: readout a la izquierda (logo rectangular
centrado V+H + info de la empresa) y formulario a la derecha. Tab
Imágenes: gestión del único logo. SHALL NOT mostrar link de Miembros en
este Edit.

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
alineado al Create (required, validación de formato, maxlength razonable
acordada / 12). SHALL NOT exponer campos de slug ni de logo en el
formulario.

#### Scenario: Campos del Info
- **WHEN** el actor edita el tab Info
- **THEN** puede guardar name, tradename, nif y status; no hay inputs de
  slug ni logo

#### Scenario: NIF como Create
- **WHEN** se valida el nif en Update
- **THEN** aplica las mismas reglas que Create (required, formato, max 12,
  único)

### Requirement: Slug automático también en Update
El slug de Company SHALL seguir generándose de forma automática (fuera de
UI). Tras un Update SHALL regenerarse y SHALL permanecer único.

#### Scenario: Regeneración en update
- **WHEN** se guarda el Edit de una company
- **THEN** el sistema regenera el slug automáticamente y el valor
  resultante es único

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


## MODIFIED Requirements

### Requirement: Edit de Company deja de ser form plano con slug/logo URL
(El Edit actual plano con slug editable, logo como URL de texto y link a
Miembros.) El Edit SHALL pasar al layout con tabs descrito arriba; slug y
logo dejan de editarse como campos de texto; el link de Miembros se quita
de esta pantalla.

#### Scenario: Sin slug/logo URL ni link miembros
- **WHEN** un actor abre el Edit de Company tras este change
- **THEN** no ve input de slug, ni logo_url de texto, ni el enlace
  “Gestionar miembros”
