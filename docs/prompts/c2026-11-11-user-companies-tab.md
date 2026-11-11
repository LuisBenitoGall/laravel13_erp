<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-11-user-companies-tab
Rule: 10-architecture
Summary: Activar el tab Empresas de la ficha de usuario con ServerTable de
  vínculos `user_companies` (nombre, posición, departamento, fecha alta) y
  acción eliminar solo el vínculo; ampliar pivot con position/department
  nullable. Distinto del futuro Empleo.
Goals:
- Tab **Empresas** operable (deja de ser placeholder), patrón ServerTable
  anidado como functionalities en Edit de Module
  (`?tab=` / endpoint `*/table`).
- Columnas: nombre empresa, posición, departamento, fecha de alta
  (`user_companies.created_at` formateada según idioma).
- Migración: `position` y `department` varchar **nullable** en
  `user_companies`.
- Acción: **eliminar vínculo** (detach); no borra User ni Company. Alta /
  vincular empresa = fuera (sigue por otros flujos, p. ej. company-users
  create).
- Listado: **todas** las companies del user, incluida la de sesión.
- Authz: self o `company-users.update` + perímetro CurrentCompany.
- Detach última company: **permitido** (user puede quedar sin empresa).
- Detach de la company de sesión del **actor en sesión**: transferir sesión
  a otra company a la que siga vinculado; si no queda ninguna → **logout**.
- Dejar explícito en design/specs: vínculo `user_companies` ≠ Empleo
  (entidad futura); un user puede tener vínculo con o sin empleo.
NonGoals:
- Alta/edición de vínculos desde este tab (ni editar position/department
  en UI de este change, salvo que salgan solo lectura en tabla).
- Entidad Empleo / RRHH.
- Borrar User o Company.
- Cambiar emails, phones, images, docs.
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices (table; detach; última company; switch sesión /
  logout; self/ajeno; position/department nullable)
Notes:
- Origen: `/opsx-propose` con aclaraciones explore; pivot actual solo
  user_id/company_id/timestamps.
- Index company-users ya hace detach de la company de sesión; este tab
  lista **todas** las memberships del user editado.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Pivot user_companies con posición y departamento
El sistema SHALL persistir `position` y `department` como varchar nullable
en `user_companies`.

#### Scenario: Campos nullable
- **WHEN** existe un vínculo user–company sin posición ni departamento
- **THEN** ambas columnas pueden ser null

### Requirement: Tab Empresas con ServerTable de vínculos
El tab Empresas de la ficha SHALL listar los vínculos del user vía
ServerTable con columnas nombre de empresa, posición, departamento y fecha
de alta (created_at i18n). SHALL incluir todas las companies del user,
incluida la de sesión. Authz: self o `company-users.update` + perímetro.

#### Scenario: Listado completo
- **WHEN** el user tiene N companies vinculadas
- **THEN** la tabla muestra N filas con las columnas acordadas

### Requirement: Eliminar solo el vínculo
El sistema SHALL permitir eliminar la fila de `user_companies` sin borrar
el User ni la Company. Alta de vínculos SHALL NOT form part de este tab.

#### Scenario: Detach
- **WHEN** el actor autorizado confirma eliminar un vínculo
- **THEN** desaparece esa membership y User/Company siguen existiendo

#### Scenario: Última company
- **WHEN** se elimina el último vínculo del user
- **THEN** la operación se acepta y el user queda sin companies

#### Scenario: Detach company de sesión del actor
- **WHEN** el actor en sesión elimina el vínculo con su company de sesión
- **THEN** la sesión pasa a otra company a la que siga vinculado; si no
  queda ninguna, se hace logout


## MODIFIED Requirements

### Requirement: Vista Edit de usuario unificada con tabs
El tab Empresas SHALL dejar de estar vacío: SHALL mostrar el ServerTable
de vínculos operable según este change.

#### Scenario: Tab Empresas usable
- **WHEN** se abre la ficha
- **THEN** el tab Empresas es usable (listado + detach)
