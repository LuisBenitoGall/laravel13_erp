<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-11-user-docs
Rule: 10-architecture
Summary: Añadir tab Documentos en la ficha de usuario para que el owner guarde
  documentos personales (pdf/word/excel), con Dropzone, listado, descarga
  autenticada y límites 20 MB / 30 docs. Sin compartir en este change.
Goals:
- Nuevo tab **Documentos** en `Users/Edit` (tras Imágenes; antes de Empresas
  si sigue vacío).
- Modelo `UserDoc` + tabla `user_docs`: documentos **personales del User**
  (no de la company); softDeletes; metadata mínima para listar/descargar
  (path, nombre original, mime/tamaño según design).
- Storage en disco **privado**; path tipo `users/{id}/docs/…`; descarga
  solo por **ruta autorizada** (nunca URL pública tipo disco `public`).
- UI: Dropzone del kit Form (accept docs + maxSize) + listado/cards con
  descargar y eliminar (confirm).
- Validación: pdf, doc/docx, xls/xlsx; máx **20 MB** por fichero; máx
  **30 documentos por user** (planes de pago / más peso = futuro).
- Authz mutaciones: igual ficha contactos/images — self, o ajeno con
  `company-users.update` + perímetro CurrentCompany; el documento sigue
  siendo del owner.
- Dominio Admin (Policy/Action/FormRequest/controller) alineado al patrón
  UserImage / contactos.
NonGoals:
- Compartir con otros usuarios (change futuro; ver Notes).
- Bandeja “compartidos conmigo” / UI de destinatario.
- 500 MB u otros techos; chunked upload / S3.
- Imágenes, emails, teléfonos, Empresas tab, permisos Spatie nuevos.
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices (upload/validación; límite 30; 20 MB; download
  authz; self/ajeno; soft delete; disco privado)
Notes:
- Origen: `/opsx-explore` tras UserImage y debate UUID (aparte).
- Reutilizar `Form/Dropzone` ya entregado en user-images.
- **Share (fuera de alcance, decisiones ya cerradas para el change
  futuro):** solo usuarios del mismo tenant; solo el owner comparte y
  revoca; eliminar doc elimina shares; descarga siempre autorizada;
  dónde ve el destinatario los compartidos = a decidir más tarde.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Schema y modelo UserDoc
El sistema SHALL persistir documentos personales del User en `user_docs`
(softDeletes) con ficheros en disco privado bajo `users/{id}/docs/…`.

#### Scenario: Alta de documento
- **WHEN** un actor autorizado sube un fichero válido
- **THEN** se crea `UserDoc` ligado al owner y el fichero queda en storage
  privado

### Requirement: Tab Documentos en ficha de usuario
El Edit SHALL incluir el tab Documentos con Dropzone y listado; acciones
descargar y eliminar. Authz: self o `company-users.update` + perímetro.

#### Scenario: Listado
- **WHEN** el user tiene documentos
- **THEN** se listan con controles de descarga y eliminación

#### Scenario: Validación formato y peso
- **WHEN** se sube un fichero fuera de pdf/doc/docx/xls/xlsx o mayor de 20 MB
- **THEN** se rechaza con error de validación

#### Scenario: Límite 30
- **WHEN** el total del user superaría 30 documentos
- **THEN** se rechaza la operación

### Requirement: Descarga autenticada
El sistema SHALL servir el fichero solo tras autorización (owner o actor
autorizado a gestionar la ficha según la misma regla de mutación acordada).
SHALL NOT exponer URL pública permanente del fichero.

#### Scenario: Descarga autorizada
- **WHEN** un actor autorizado solicita descarga
- **THEN** recibe el fichero

#### Scenario: Descarga no autorizada
- **WHEN** un actor sin derecho fuerza la ruta de descarga
- **THEN** recibe 403 o rechazo de perímetro

### Requirement: Eliminar documento
El sistema SHALL permitir eliminar un documento (confirmación; soft delete)
y SHALL dejar de listarlo / servir descarga.

#### Scenario: Eliminar
- **WHEN** el actor autorizado confirma eliminar
- **THEN** el documento desaparece del listado (soft-deleted)


## MODIFIED Requirements

### Requirement: Vista Edit de usuario unificada con tabs
Los tabs de la ficha SHALL incluir Documentos (usable) tras Imágenes;
Empresas sigue vacío si no hay change propio.

#### Scenario: Tab Documentos presente
- **WHEN** se abre la ficha
- **THEN** existe el tab Documentos operable según este change
