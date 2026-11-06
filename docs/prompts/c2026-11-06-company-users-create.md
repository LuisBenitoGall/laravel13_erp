<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-06-company-users-create
Rule: 10-architecture
Summary: Alta de usuarios desde Usuarios empresa (`/admin/company-users`): quitar
  máscara del CTA Crear, formulario Create con metadatos + rol + checkboxes, vínculo
  a la company de sesión, Edit stub tras el alta, columna `is_public` y filtro en
  Todos los usuarios. Mails de credenciales y de vinculación = máscara en este change.
Goals:
- Cablear el botón Crear del Index Usuarios empresa a la ruta Create real (dejar
  de usar `alta_usuario_mascara` / toast).
- Formulario Create con: nombre, apellidos, email (obligatorio en este form),
  status (activo; default marcado), checkbox enviar credenciales (default
  marcado), checkbox perfil público `is_public` (default marcado), selector de
  rol (roles universales ∪ roles locales de la company de sesión; rol
  `Super Admin` solo si el actor de sesión es Super Admin).
- Desde este flujo: alta nueva con `is_admin = true` y vínculo a la company de
  sesión; asignar el rol elegido en el contexto de esa company.
- Schema: `users.password` y `users.email` pasan a nullable (`is_public` nueva
  columna a la derecha de `is_admin`). Password se genera/persiste solo si el
  checkbox de credenciales está marcado (en alta nueva); si no, sin password.
- Email ya existente:
  - ya vinculado a la company de sesión → abortar e informar (toast/alerta);
  - soft-deleted con ese email → conflicto (no restaurar; evitar coladero);
  - existe en otra company → vincular a sesión, `is_admin → true`, rellenar
    huecos en name/surname/status/`is_public`, asignar el rol elegido para
    esta company (los roles pueden diferir por empresa); mail de “vinculado a
    otra empresa” = máscara; no tocar password ni enviar credenciales si ya
    era `is_admin = true`; credenciales (máscara) solo si era `is_admin =
    false` y no tenía password (con la regla de envío acordada / checkbox).
- Redirect post-éxito → Edit del usuario: vista muy básica (cabecera como otras
  páginas y poco más; se completará en otro change).
- Index Todos los usuarios: filtrar por `is_public = true` (incluir ya el
  filtro).
- Authz Create: `company-users.create`.
- Mails (credenciales y vinculación): solo máscara en este change.
NonGoals:
- Plantillas/envío real de emails (credenciales o vinculación).
- Completar el Edit de usuario (solo stub de cabecera).
- Cancelar vinculación desde perfil personal (explícitamente “luego, en la
  edición”).
- Implementar CRM / contactos / leads (solo anticipar nullability de email y
  password).
- Cambiar el Index company-users más allá del CTA Crear y lo imprescindible
  para compilar/navegación al Create/Edit stub.
- Reabrir el diseño de Members / AssignCompanyRole fuera de lo que impone el
  selector de rol en este Create.
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices (alta nueva; vínculo existente; rechazo ya vinculado;
  soft-delete conflicto; filtro all-users; SA en selector solo si actor SA;
  CTA con ruta)
Notes:
- Origen: `/opsx-explore` tras cierre roles; debate Create Usuarios empresa.
- Estado actual: Index company-users con Crear/Editar máscara; sin
  create/store/edit; `AllUserController` lista todos los users sin filtro
  público; `users` tiene `is_admin`/`status`, password/email NOT NULL, sin
  `is_public`.
- Kernel: permitir que un Super Admin asigne el rol `Super Admin` a otro
  (entre iguales) está debatido y pedido por Luis; un no-SA no debe ver ni
  asignar ese rol en el selector.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Create usuario desde Usuarios empresa
El sistema SHALL exponer Create de usuario accesible desde el Index
`/admin/company-users`, con el CTA Crear navegando a esa ruta (sin máscara de
alta). El formulario SHALL incluir nombre, apellidos, email obligatorio en
este flujo, status (default activo), checkbox enviar credenciales (default
marcado), checkbox perfil público `is_public` (default marcado) y selector de
rol (universales y locales de la company de sesión; `Super Admin` solo si el
actor es Super Admin). Un alta nueva SHALL persistir `is_admin = true`,
vincular a la company de sesión, asignar el rol elegido en esa company, y
generar password solo si el checkbox de credenciales está marcado. Tras éxito
SHALL redirigir al Edit stub del usuario. El acceso SHALL exigir
`company-users.create`.

#### Scenario: CTA Crear navega a Create
- **WHEN** un usuario autorizado pulsa Crear usuario en Usuarios empresa
- **THEN** navega al formulario Create (no toast de máscara de alta)

#### Scenario: Alta nueva con credenciales marcadas
- **WHEN** envía un email nuevo con checkbox de credenciales marcado y rol
  válido
- **THEN** se crea el user con `is_admin = true`, vínculo a la company de
  sesión, rol asignado, password generado, y se muestra la máscara de envío de
  credenciales (sin envío real obligatorio en este change)

#### Scenario: Alta nueva sin credenciales
- **WHEN** envía un email nuevo con checkbox de credenciales desmarcado
- **THEN** el user se crea sin password persistido y sin máscara de
  credenciales

#### Scenario: Redirect a Edit stub
- **WHEN** el alta o vinculación termina con éxito
- **THEN** redirige a una vista Edit básica del usuario (cabecera al estilo
  de otras páginas; sin formulario completo)

### Requirement: Email existente — vínculo o rechazo
Si el email ya pertenece a un User, el sistema SHALL NOT crear un duplicado.
Si ya está vinculado a la company de sesión, SHALL abortar e informar. Si el
User está soft-deleted, SHALL tratarlo como conflicto (sin restaurar). Si
existe vinculado a otra company, SHALL vincularlo a la company de sesión,
poner `is_admin = true`, rellenar name/surname/status/`is_public` cuando
faltaban, asignar el rol elegido para esa company, y mostrar máscara de aviso
de vinculación (sin plantilla real). SHALL NOT cambiar password ni mostrar
máscara de credenciales si el user ya era `is_admin = true`. Máscara de
credenciales solo si era `is_admin = false` y no tenía password (según
checkbox / regla de envío).

#### Scenario: Ya vinculado a la sesión
- **WHEN** el email existe y el user ya está en `user_companies` de la company
  de sesión
- **THEN** la operación se rechaza y se informa al actor (toast/alerta)

#### Scenario: Soft-deleted
- **WHEN** el email corresponde a un user soft-deleted
- **THEN** la operación se trata como conflicto (no se restaura ni vincula)

#### Scenario: Existe en otra company
- **WHEN** el email existe, no soft-deleted, y no está vinculado a la sesión
- **THEN** se crea el vínculo, `is_admin` queda true, se rellenan huecos
  acordados, se asigna el rol de esta company, y se muestra la máscara de
  vinculación

### Requirement: Schema is_public y nullability email/password
El sistema SHALL añadir `is_public` en `users` a la derecha de `is_admin`, y
hacer nullable `email` y `password` (el Create de este flujo sigue exigiendo
email).

#### Scenario: Persistencia is_public
- **WHEN** se da de alta o se actualizan huecos con el checkbox de perfil
  público
- **THEN** el valor queda reflejado en `users.is_public`

### Requirement: Todos los usuarios filtra perfil público
El Index/table de Todos los usuarios SHALL listar solo users con
`is_public = true`.

#### Scenario: Listado filtrado
- **WHEN** un actor abre Todos los usuarios / su `table`
- **THEN** no aparecen users con `is_public = false`


## MODIFIED Requirements

### Requirement: Index Usuarios empresa
El control Crear usuario del Index Usuarios empresa SHALL dejar de ser máscara
y SHALL navegar al Create real de este change. El Edit de fila puede pasar a
navegar al Edit stub (cabecera) en lugar de la máscara de edición previa, en
lo imprescindible para el redirect del Create.

#### Scenario: Crear ya no es máscara de alta
- **WHEN** el usuario autorizado pulsa Crear usuario
- **THEN** abre el Create real

### Requirement: Index Todos los usuarios
El listado global SHALL dejar de mostrar el conjunto completo sin filtro y
SHALL aplicar el filtro `is_public = true` de este change.

#### Scenario: Solo perfiles públicos
- **WHEN** se carga el ServerTable de Todos los usuarios
- **THEN** el conjunto es el de users públicos
