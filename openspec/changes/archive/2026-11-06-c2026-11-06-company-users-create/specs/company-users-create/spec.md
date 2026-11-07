# Spec Delta

## Purpose

Alta y vinculación de usuarios desde Usuarios empresa: formulario Create, reglas
de email existente, rol por company, schema `is_public`/nullability, Edit stub y
máscaras de correo (sin envío real).

## ADDED Requirements

### Requirement: Create usuario desde Usuarios empresa
El sistema SHALL exponer Create accesible desde el Index company-users, con CTA
Crear navegando a esa ruta. Formulario: name, surname, email obligatorio, status
(default activo), send credentials (default marcado), `is_public` (default
marcado), selector de rol (universales ∪ locales de sesión; `Super Admin` solo
si el actor es SA). Alta nueva: `is_admin = true`, vínculo a CurrentCompany, rol
asignado; password solo si credenciales marcadas. Tras éxito → Edit stub.
Authz: `company-users.create`.

#### Scenario: CTA Crear navega a Create
- **WHEN** un usuario autorizado pulsa Crear usuario en Usuarios empresa
- **THEN** navega al formulario Create (no toast de máscara de alta)

#### Scenario: Alta nueva con credenciales marcadas
- **WHEN** envía un email nuevo con checkbox de credenciales marcado y rol
  válido
- **THEN** se crea el user con `is_admin = true`, vínculo a la company de
  sesión, rol asignado, password generado, y máscara de envío de credenciales
  (sin envío real obligatorio)

#### Scenario: Alta nueva sin credenciales
- **WHEN** envía un email nuevo con checkbox de credenciales desmarcado
- **THEN** el user se crea sin password persistido y sin máscara de
  credenciales

#### Scenario: Super Admin en selector solo para SA
- **WHEN** un actor no Super Admin abre Create
- **THEN** el selector de rol no incluye `Super Admin`

#### Scenario: Redirect a Edit stub
- **WHEN** el alta o vinculación termina con éxito
- **THEN** redirige a Edit básico del usuario (cabecera; sin formulario
  completo)

### Requirement: Email existente — vínculo o rechazo
Si el email ya es de un User, SHALL NOT duplicar. Ya vinculado a sesión →
abortar e informar. Soft-deleted → conflicto (no restaurar). Otra company →
vincular, `is_admin = true`, rellenar huecos name/surname/status/`is_public`,
asignar rol de esta company, máscara de vinculación. Sin cambiar password ni
máscara de credenciales si ya era `is_admin = true`. Credenciales (máscara)
solo si era `is_admin = false` y sin password.

#### Scenario: Ya vinculado a la sesión
- **WHEN** el email existe y el user ya está en `user_companies` de la sesión
- **THEN** la operación se rechaza y se informa al actor (toast/alerta)

#### Scenario: Soft-deleted
- **WHEN** el email corresponde a un user soft-deleted
- **THEN** conflicto (no se restaura ni vincula)

#### Scenario: Existe en otra company
- **WHEN** el email existe, no soft-deleted, y no está vinculado a la sesión
- **THEN** se crea el vínculo, `is_admin` queda true, se rellenan huecos
  acordados, se asigna el rol de esta company, y máscara de vinculación

### Requirement: Schema is_public y nullability email/password
SHALL añadir `is_public` en `users` a la derecha de `is_admin`, y hacer
nullable `email` y `password` (este Create sigue exigiendo email).

#### Scenario: Persistencia is_public
- **WHEN** se da de alta o se rellenan huecos con el checkbox de perfil público
- **THEN** el valor queda en `users.is_public`

### Requirement: Edit stub de usuario empresa
Tras Create/link, SHALL existir Edit mínimo del user (cabecera al estilo de
otras páginas, sin formulario completo de edición).

#### Scenario: Edit stub accesible
- **WHEN** un actor autorizado abre el Edit del user tras el alta
- **THEN** ve la cabecera/página stub sin el formulario completo diferido
