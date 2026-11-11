<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-11-menu-cuenta-vs-profile
Rule: 10-architecture
Summary: Corregir el concepto de menú Cuenta vs ficha de usuario: `/profile`
  solo desde el avatar; lateral sin enlace a profile; en profile ningún ítem
  de menú activo; Mi cuenta queda como próximamente (cuenta SaaS company);
  Mis módulos sin cambio de ubicación.
Goals:
- **Cuenta / Mi cuenta** = cuenta contratada de la **company** con el ERP
  (historial/renovaciones = futuro). SHALL NOT ser la ficha del User.
- Acceso a la ficha propia (`/profile`, Edit unificada self): **solo** desde
  el desplegable del avatar (topbar). SHALL NOT haber entrada en el menú
  lateral hacia `/profile`.
- Con URL `/profile*` (y mutaciones bajo profile): el AsideNav SHALL
  **no** marcar ningún Module/Functionality como activo (ni Cuenta ni
  Usuarios).
- Ítem **Mi cuenta** bajo Module `cuenta`: mantenerlo visible como
  **próximamente** (placeholder; ruta distinta de `/profile`, sin ficha
  user).
- **Mis módulos** (`company-modules`) sigue bajo Module `cuenta`.
- Actualizar breadcrumb / labels de la ficha self para no presentarla como
  «Mi cuenta» de menú (alineado al nuevo concepto).
- Delta specs `menu-core-pillars` (y lo que enlace `profile`→Mi cuenta /
  ficha user); MenuBuilder / catálogo / permisos de menú según design.
NonGoals:
- Implementar billing, historial de cuentas ni renovaciones reales.
- Mover Mis módulos fuera de `cuenta`.
- Cambiar el Edit unificado de user (tabs/CRUD) salvo breadcrumb/nav.
- Marcar Usuarios empresa como activo en `/profile` (descartado: sin
  selección).
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices (menú: profile no en lateral; Mi cuenta no va a
  /profile; /profile* sin aria-current; Mis módulos intacto; avatar sigue
  a /profile)
Notes:
- Origen: `/opsx-explore` tras ver highlight erróneo Cuenta/Mi cuenta en
  ficha self (captura Luis).
- Estado: Functionality `profile` bajo `cuenta` → `route('profile.show')`;
  AsideNav `pathMatches`; breadcrumb profile usa `mi_cuenta`.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Profile solo desde avatar
La ficha Edit del propio user (`/profile`) SHALL ser accesible desde el
menú del avatar del topbar. El menú lateral SHALL NOT incluir un enlace a
`/profile`.

#### Scenario: Avatar abre profile
- **WHEN** el usuario elige Perfil (o equivalente) en el desplegable del
  avatar
- **THEN** navega a `/profile`

#### Scenario: Lateral sin profile
- **WHEN** se construye el menú lateral
- **THEN** ningún ítem enlaza a `/profile`

### Requirement: Sin ítem activo en /profile
Mientras la URL sea la ficha self `/profile` (y rutas hijas de profile de
esa ficha), el AsideNav SHALL NOT marcar ningún Module ni Functionality
como página actual.

#### Scenario: Profile sin highlight
- **WHEN** el actor está en `/profile` (o subruta de la ficha self)
- **THEN** ningún ítem del lateral tiene estado activo / `aria-current`

### Requirement: Mi cuenta próximamente (cuenta SaaS company)
Bajo Module `cuenta`, el ítem Mi cuenta SHALL permanecer como
próximamente (cuenta de la company con el ERP), sin navegar a la ficha
del User. Mis módulos SHALL seguir bajo `cuenta`.

#### Scenario: Mi cuenta no es ficha user
- **WHEN** el usuario activa Mi cuenta desde el menú
- **THEN** no se abre `/profile` / la ficha Edit del user; ve el
  placeholder de próximamente (o equivalente acordado en design)

#### Scenario: Mis módulos intacto
- **WHEN** el usuario abre Mis módulos
- **THEN** sigue bajo Cuenta y navega a company-modules como hasta ahora


## MODIFIED Requirements

### Requirement: Cuenta con Mi cuenta y Mis módulos
(El requirement existente en `menu-core-pillars` que liga `profile` a Mi
cuenta → ficha user.) SHALL dejar de asociar Mi cuenta con la ficha Edit
del propio user; Mi cuenta = próximamente cuenta SaaS company; la ficha
self queda fuera del menú lateral (avatar). Mis módulos sin cambio de
pilar.

#### Scenario: Mi cuenta bajo cuenta
- **WHEN** un usuario con acceso a Cuenta obtiene el menú
- **THEN** ve Mi cuenta bajo Cuenta como próximamente (no ficha user)

#### Scenario: Mis módulos bajo cuenta
- **WHEN** un usuario con `cuenta.access` y `company-modules.view` y capas
  company satisfechas obtiene el menú
- **THEN** ve Mis módulos bajo Cuenta y navega al Index company-modules
