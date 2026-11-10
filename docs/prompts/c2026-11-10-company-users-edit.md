<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-10-company-users-edit
Rule: 10-architecture
Summary: Sustituir el Edit stub de usuario y el Profile máscara por una misma vista
  con cabecera (ActionBar + RecordSummary), tabs extensibles y Perfil usable;
  Contraseña solo para el propio usuario; tabs Emails/teléfonos y Empresas vacíos.
Goals:
- Una sola página Inertia de Edit de usuario servida por dos URLs:
  `/profile` (self) y `/admin/company-users/{user}/edit` (ficha desde Usuarios
  empresa).
- Cabecera como otras fichas: ActionBar + RecordSummary con título de la
  acción, `created_at`, `updated_at` y rol de la company de sesión en solo
  lectura (cuando aplique el contexto company-users).
- Estructura de **tabs** lista para añadir pestañas futuras (nacer
  `RecordEditLayout` o equivalente reutilizable según recomendación explore).
- Tabs de este change:
  - **Perfil** (contenido): dos columnas — izquierda componente reutilizable
    de resumen RO (avatar placeholder + datos personales + emails/tels);
    derecha formulario con name, surname, email principal, `is_public`,
    fecha de nacimiento, nif, `status` (no `is_admin`). Submit **por tab**.
  - **Contraseña**: gestión de contraseña; **bloqueada para cualquiera que
    no sea el propio usuario en sesión** (ni SA ni manager cambian la de
    otro). Si no es self, el tab no es usable.
  - **Emails y teléfonos**: tab vacío (lógica luego).
  - **Empresas**: tab vacío (lógica luego).
- Avatar: placeholder; subida real fuera de este change.
- `is_public` y `status`: el propio usuario puede cambiarlos; también un rol
  con permiso (permiso concreto TBD). Código SHALL quedar listo para enganchar
  ese permiso en cualquier momento.
- Self edition (`/profile`): **no** modelar ahora la relación con empresa
  (se trata luego). Ficha ajena vía company-users: authz
  `company-users.update` + vínculo a CurrentCompany (perímetro), sin ampliar
  a “ver todas las companies”.
NonGoals:
- CRUD real de emails, teléfonos o vínculos a empresas (solo tabs vacíos).
- Subida/gestión real de avatar/imágenes.
- Cancelar vinculación desde perfil; mail real de credenciales/vinculación.
- Exponer o editar `is_admin` en este Edit.
- Definir el slug final del permiso de `is_public`/`status` (solo gancho).
- Completar Members / AssignCompanyRole salvo mostrar rol de sesión en
  sumario readonly.
- Relación empresa en el flujo self `/profile` (explícitamente diferido).
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices (self vs ajeno; tab password solo self; update
  perfil; gancho permiso is_public/status; dos rutas misma página)
Notes:
- Origen: `/opsx-explore` tras Create company-users archivado; Edit stub +
  Profile máscara actuales.
- Estándar frontend: `RecordEditLayout` pendiente; Module Edit ya usa tabs +
  `RecordSummary` + `auditRecordSummaryFields` — reutilizar patrón.
- Campos concretos del formulario del tab Contraseña (p. ej. actual / nueva /
  confirmación) no se cerraron en explore: 10 propone alineado a v1/prácticas
  del repo o pregunta a Luis si duda.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Vista Edit de usuario unificada con tabs
El sistema SHALL exponer una vista Edit de usuario compartida por `/profile` y
por el Edit de Usuarios empresa, con ActionBar, RecordSummary (alta, última
actualización, y rol de la company de sesión en solo lectura cuando el
contexto sea company-users) y tabs extensibles. Tabs de este change: Perfil
(usable), Contraseña (solo self), Emails y teléfonos (vacío), Empresas (vacío).
Submit SHALL ser por tab.

#### Scenario: Misma página en dos rutas
- **WHEN** el actor abre `/profile` o el Edit de un user desde Usuarios empresa
- **THEN** se renderiza la misma vista de ficha con cabecera y tabs

#### Scenario: Tabs vacíos presentes
- **WHEN** se abre la ficha
- **THEN** existen las pestañas Emails y teléfonos y Empresas sin CRUD aún

### Requirement: Tab Perfil con resumen RO y formulario
El tab Perfil SHALL mostrar dos columnas: izquierda un componente reutilizable
de resumen solo lectura (avatar placeholder, datos personales, emails y
teléfonos); derecha el formulario con name, surname, email principal,
`is_public`, fecha de nacimiento, nif y `status` (sin `is_admin`). Un submit
del tab SHALL persistir esos campos según authz.

#### Scenario: Layout dos columnas
- **WHEN** el actor abre el tab Perfil
- **THEN** ve resumen RO a la izquierda (con avatar placeholder) y el
  formulario a la derecha

#### Scenario: Guardar perfil
- **WHEN** envía el formulario del tab Perfil con datos válidos y autorización
- **THEN** se persisten los campos del tab (incluidos `is_public` y `status`)

### Requirement: Tab Contraseña solo para el propio usuario
El sistema SHALL permitir la gestión de contraseña únicamente cuando el user
editado es el actor en sesión. SHALL NOT permitir que ningún otro actor
(incluido Super Admin) cambie la contraseña de un tercero por esta ficha.

#### Scenario: Self ve Contraseña usable
- **WHEN** el actor edita su propio user
- **THEN** el tab Contraseña es usable

#### Scenario: Ajeno sin Contraseña usable
- **WHEN** el actor edita a otro user
- **THEN** no puede cambiar la contraseña de ese user vía esta ficha

### Requirement: Authz is_public y status con gancho de permiso
El propio usuario SHALL poder cambiar `is_public` y `status` en su ficha. Un
rol con permiso (slug TBD) también SHALL poder. La implementación SHALL dejar
el punto de chequeo listo para el permiso futuro sin hardcodear el slug final
como constitución.

#### Scenario: Self cambia is_public o status
- **WHEN** el propio usuario actualiza `is_public` o `status` en el tab Perfil
- **THEN** el cambio se persiste

#### Scenario: Gancho de permiso
- **WHEN** exista el permiso futuro y un actor no-self lo tiene
- **THEN** el mismo camino de autorización puede conceder el cambio sin
  rediseñar la ficha


## MODIFIED Requirements

### Requirement: Edit stub de usuario empresa
El Edit de Usuarios empresa deja de ser stub de cabecera y SHALL usar la vista
unificada con tabs y tab Perfil usable (resto según este change).

#### Scenario: Edit company-users ya no es solo stub
- **WHEN** un actor autorizado abre Edit desde Usuarios empresa
- **THEN** ve la ficha con tabs (no solo el texto stub previo)

### Requirement: Profile / Mi cuenta
La ruta de perfil (`/profile` / Mi cuenta) SHALL dejar la máscara
`perfil_proximamente` y SHALL renderizar la misma vista Edit unificada en modo
self (sin modelar en este change la relación con empresa).

#### Scenario: Mi cuenta abre ficha self
- **WHEN** el usuario abre Mi cuenta / profile
- **THEN** ve la ficha Edit unificada de su propio user
