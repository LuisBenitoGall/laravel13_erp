<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-11-user-images
Rule: 10-architecture
Summary: Añadir tab Imágenes en la ficha de usuario (dropzone + grid/galería),
  modelo UserImage según migration aportada, featured = avatar (readout y
  topbar), y componente Dropzone reutilizable del kit Form para todo el
  proyecto.
Goals:
- Nuevo tab **Imágenes** en `Users/Edit`, a continuación de Emails y
  teléfonos (antes de Empresas).
- Schema `user_images` según migration aportada: `image`, `user_id` cascade,
  `featured` (default false), `public` (default false), softDeletes,
  unique `(user_id, image, deleted_at)`, índices; enforce un solo featured
  por user vía columna generada `featured_user_id` + unique (MySQL).
- Modelo `UserImage` + relación en User; storage disco `public`, path
  `users/{id}/images/…`, URL vía `Storage::url`.
- UI: Dropzone + grid/galería (estilo captura); eliminar; marcar featured
  (estrella) = avatar del usuario.
- Validación: formatos jpg, jpeg, png, gif, webp; máx **1 MB** por fichero;
  máx **20 por subida** y **20 por usuario** (upgrades de pago = futuro).
- Visibilidad pública: una imagen solo se puede mostrar públicamente si
  `public=true` **y** `user.is_public=true`.
- Avatar: featured alimenta el readout de la ficha **y** el avatar del
  topbar/menú.
- Authz: igual emails/tels — self, o ajeno con `company-users.update` +
  perímetro CurrentCompany.
- Componente **Dropzone genérico** en el kit Form (`react-dropzone`, ya en
  `libraries.md`): parámetros de formatos admitidos, tamaño máx., etc.;
  este caso de uso lo configura a imágenes 1 MB / 20 ficheros.
- Dominio Admin (Policy/Action/FormRequest/controller) alineado al patrón
  de contactos de la ficha.
NonGoals:
- Documentos no-imagen en este tab (solo imágenes).
- Opciones de pago / límites superiores a 20.
- Wysiwyg u otros pendientes del kit Form.
- Cambiar emails, teléfonos, Empresas tab, o permisos Spatie nuevos.
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices (upload/validación; límite 20; featured único;
  public∧is_public; self/ajeno; soft delete; avatar readout/topbar)
Notes:
- Origen: `/opsx-explore` tras phones; migration pegada por Luis; UI =
  captura dropzone + grid + lightbox + estrella/borrar.
- Estado: `frontend.md` marca Dropzone pendiente; `UserProfileReadout` y
  topbar usan placeholder de avatar.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Schema user_images y modelo UserImage
El sistema SHALL crear `user_images` según la migration aportada (incl.
softDeletes, unique user+image+deleted_at, y un solo featured por user vía
columna generada). SHALL persistir archivos en disco `public` bajo
`users/{id}/images/…`.

#### Scenario: Alta de imagen
- **WHEN** un actor autorizado sube una imagen válida
- **THEN** se crea `UserImage` con path en storage y flags por defecto
  (featured/public false salvo acción explícita)

#### Scenario: Un solo featured
- **WHEN** se marca una imagen como featured
- **THEN** queda como única featured de ese user (enforce schema + Action)

### Requirement: Tab Imágenes en ficha de usuario
El Edit de usuario SHALL incluir el tab Imágenes después de Emails y
teléfonos, con Dropzone, grid/galería, eliminar y marcar principal
(featured). Authz: self o `company-users.update` + perímetro.

#### Scenario: Listado y galería
- **WHEN** el user tiene imágenes
- **THEN** se muestran en grid con acciones eliminar y featured; preview
  tipo lightbox usable

#### Scenario: Validación formato y peso
- **WHEN** se sube un fichero fuera de jpg/jpeg/png/gif/webp o > 1 MB
- **THEN** se rechaza con error de validación

#### Scenario: Límite 20
- **WHEN** la subida o el total del user superaría 20 imágenes
- **THEN** se rechaza la operación

### Requirement: Visibilidad pública de imágenes
Una imagen SHALL poder mostrarse en contextos públicos solo si
`public=true` **y** el User tiene `is_public=true`.

#### Scenario: Perfil no público
- **WHEN** `user.is_public` es false
- **THEN** ninguna imagen del user se expone como pública aunque
  `public=true`

#### Scenario: Imagen no public
- **WHEN** la imagen tiene `public=false` aunque el perfil sea público
- **THEN** esa imagen no se expone públicamente

### Requirement: Featured como avatar
La imagen featured del user SHALL usarse como avatar en el readout de la
ficha y en el topbar/menú avatar. Sin featured, SHALL mostrarse el
placeholder actual.

#### Scenario: Marcar featured
- **WHEN** el actor marca una imagen como featured
- **THEN** readout y topbar reflejan esa imagen como avatar

### Requirement: Dropzone reutilizable del kit Form
El sistema SHALL añadir un componente Dropzone genérico al kit Form
(basado en `react-dropzone`) configurable al menos por formatos admitidos
y tamaño máximo; el tab Imágenes SHALL usarlo con el perfil de imágenes
acordado.

#### Scenario: Reutilizable
- **WHEN** otra pantalla use Dropzone con otros accept/maxSize
- **THEN** puede configurarlo sin acoplarse a UserImage


## MODIFIED Requirements

### Requirement: Vista Edit de usuario unificada con tabs
Los tabs de la ficha SHALL incluir Imágenes (usable) entre Emails y
teléfonos y Empresas; Empresas sigue vacío si no hay change propio.

#### Scenario: Tab Imágenes presente
- **WHEN** se abre la ficha
- **THEN** existe el tab Imágenes operable según este change
