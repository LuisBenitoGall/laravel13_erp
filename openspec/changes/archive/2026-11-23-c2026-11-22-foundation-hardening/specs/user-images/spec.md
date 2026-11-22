# Spec Delta

## ADDED Requirements

### Requirement: Entrega autenticada de imágenes de usuario
El sistema SHALL servir el binario de cada `UserImage` solo mediante rutas
de la aplicación autenticadas. La authz SHALL ser equivalente a la de la
ficha del user en ese contexto (self en perfil; `company-users` + perímetro
en Usuarios empresa). SHALL NOT exponer el fichero por URL estática anónima
de disco público.

#### Scenario: Sin login no descarga imagen
- **WHEN** un cliente no autenticado solicita la URL de entrega de una
  imagen de user
- **THEN** no obtiene el binario (401/403/404 o redirección a login; no 200
  anónimo del fichero)

#### Scenario: Actor autorizado obtiene binario
- **WHEN** un actor autenticado autorizado solicita la URL de entrega de
  una imagen de ese user
- **THEN** recibe el binario de la imagen

## MODIFIED Requirements

### Requirement: Schema user_images y modelo UserImage
El sistema SHALL persistir `user_images` según la migration aportada (soft
deletes, unique user+image+deleted_at, un featured por user vía columna
generada). Los ficheros SHALL guardarse en disco privado (no público
anónimo) bajo `users/{id}/images/…`. La URL expuesta al cliente SHALL ser
la ruta autenticada de entrega, no una URL estática de disco público.

#### Scenario: Alta de imagen
- **WHEN** un actor autorizado sube una imagen válida
- **THEN** se crea `UserImage` con path en storage privado y
  `featured`/`public` false por defecto

#### Scenario: Un solo featured
- **WHEN** se marca una imagen como featured
- **THEN** queda como única featured de ese user

#### Scenario: URL no es estática pública
- **WHEN** el sistema expone la URL de una imagen al cliente (grid, avatar,
  lightbox)
- **THEN** esa URL no es un path anónimo bajo disco público (`/storage/…`)
