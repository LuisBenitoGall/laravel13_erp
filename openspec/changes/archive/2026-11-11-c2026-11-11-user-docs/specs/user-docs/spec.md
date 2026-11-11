# Spec Delta

## Purpose

Documentos personales del User en la ficha: upload con límites, listado,
descarga autenticada desde disco privado y eliminación; sin compartir.

## ADDED Requirements

### Requirement: Schema y modelo UserDoc
El sistema SHALL persistir documentos personales del User en `user_docs`
(softDeletes) con path, nombre original, mime y tamaño, y ficheros en disco
privado bajo `users/{id}/docs/…`.

#### Scenario: Alta de documento
- **WHEN** un actor autorizado sube un fichero válido
- **THEN** se crea `UserDoc` ligado al owner y el fichero queda en storage
  privado

### Requirement: Tab Documentos en ficha de usuario
El Edit SHALL incluir el tab Documentos tras Imágenes, con Dropzone y
listado; acciones descargar y eliminar. Authz: self o
`company-users.update` + perímetro.

#### Scenario: Listado
- **WHEN** el user tiene documentos
- **THEN** se listan con controles de descarga y eliminación

#### Scenario: Validación formato y peso
- **WHEN** se sube un fichero fuera de pdf/doc/docx/xls/xlsx o mayor de 20 MB
- **THEN** se rechaza con error de validación

#### Scenario: Límite 30
- **WHEN** el total del user superaría 30 documentos
- **THEN** se rechaza la operación

#### Scenario: Ajeno sin permiso
- **WHEN** un actor no autorizado fuerza upload o delete
- **THEN** recibe 403 o rechazo de perímetro

### Requirement: Descarga autenticada
El sistema SHALL servir el fichero solo tras autorización (misma regla que
gestionar la ficha: self o `company-users.update` + perímetro). SHALL NOT
exponer URL pública permanente del fichero.

#### Scenario: Descarga autorizada
- **WHEN** un actor autorizado solicita descarga
- **THEN** recibe el fichero

#### Scenario: Descarga no autorizada
- **WHEN** un actor sin derecho fuerza la ruta de descarga
- **THEN** recibe 403 o rechazo de perímetro

### Requirement: Eliminar documento
El sistema SHALL permitir eliminar un documento (confirmación; soft delete)
y SHALL dejar de listarlo y de servir descarga.

#### Scenario: Eliminar
- **WHEN** el actor autorizado confirma eliminar
- **THEN** el documento desaparece del listado (soft-deleted)
