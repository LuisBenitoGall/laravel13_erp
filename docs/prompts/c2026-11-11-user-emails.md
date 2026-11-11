<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-11-user-emails
Rule: 10-architecture
Summary: Activar el tab Emails y teléfonos de la ficha de usuario con la columna
  de emails adicionales (N emails, cards, alta modal, eliminar/copiar/mailto);
  teléfonos queda placeholder. Corregir schema para permitir N emails y completar
  dominio Admin (Policy/Action/Request; controller en Domains).
Goals:
- Tab `emails_y_telefonos`: layout **dos columnas** equivalentes — izquierda
  emails, derecha teléfonos (derecha vacía/placeholder en este change).
- Emails adicionales: un user puede tener **N** emails; formato email; únicos
  en `user_emails` (unique global) y **distintos** del email principal
  `users.email` del mismo user.
- Migración: **eliminar** el `unique(user_id)` actual (impide N filas).
- UI emails (estilo cards, no tabla): cabecera + botón **+ Email**; cards con
  email, observaciones opcionales, acciones **mailto**, **copiar** al
  portapapeles y **eliminar** (confirm). **Sin editar** (borrar y recrear).
- Alta vía **modal**: campos email (required) + observaciones/`description`
  (opcional).
- Authz: misma regla que Perfil de la ficha (self, o ajeno con
  `company-users.update` + perímetro CurrentCompany).
- Mover/crear lógica en `Domains/Admin` (controller, Policy, Action,
  FormRequest); modelo `UserEmail` usable; relación en User; props del Edit
  y `UserProfileReadout` reflejan la lista.
- Quitar el placeholder “próximamente” del tab contactos en lo que toca a
  emails.
NonGoals:
- CRUD / UI de teléfonos (solo columna derecha vacía).
- Editar email existente (ni PATCH de description).
- Envío real de correo desde la app (solo `mailto:`).
- Cambiar el email principal del User (sigue en tab Perfil).
- Avatar, Empresas tab, permisos nuevos distintos a la authz de ficha ya
  acordada.
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices (N emails; unique; distinto de principal; self/ajeno;
  delete; migración sin unique user_id)
Notes:
- Origen: `/opsx-explore` tras ficha user-edit; tab contactos vacío.
- Estado: migración `user_emails` con `email` unique + `description` nullable
  **y** `unique(user_id)` erróneo para N; `UserEmail` y
  `app/Http/Controllers/UserEmailController` vacíos.
- Referencia UI: cards tipo “Emails adicionales” (imagen Luis: + Email, card
  con observaciones, utilidades; en v2 sin Edit, con mailto/copy/delete).
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Tab contactos — columna emails adicionales
El tab Emails y teléfonos de la ficha de usuario SHALL mostrar dos columnas:
izquierda emails adicionales, derecha teléfonos. En este change la derecha
SHALL quedar vacía/placeholder. La izquierda SHALL listar los emails del user
en cards (no tabla), con botón de alta y acciones mailto, copiar y eliminar.

#### Scenario: Layout dos columnas
- **WHEN** el actor abre el tab Emails y teléfonos
- **THEN** ve la columna emails a la izquierda y la de teléfonos vacía a la
  derecha

#### Scenario: Listado en cards
- **WHEN** el user tiene emails adicionales
- **THEN** cada uno se muestra en una card con el email y, si hay,
  observaciones; sin acción de editar

### Requirement: Alta de email adicional por modal
El sistema SHALL permitir añadir un email adicional vía modal con email
obligatorio (formato email) y observaciones opcionales. El email SHALL ser
único en `user_emails` y distinto del email principal del user. Authz: self o
`company-users.update` + perímetro.

#### Scenario: Alta válida
- **WHEN** un actor autorizado envía email válido no duplicado ni igual al
  principal, con o sin observaciones
- **THEN** se persiste el `UserEmail` y aparece en el listado

#### Scenario: Duplicado o igual al principal
- **WHEN** el email ya existe en `user_emails` o coincide con `users.email`
  del mismo user
- **THEN** la operación se rechaza con error de validación

### Requirement: Eliminar email adicional
El sistema SHALL permitir eliminar un email adicional (con confirmación).
SHALL NOT ofrecer edición in-place.

#### Scenario: Eliminar
- **WHEN** el actor autorizado confirma eliminar un email adicional
- **THEN** la fila desaparece de `user_emails` y del listado

### Requirement: Acciones mailto y copiar
Cada card SHALL ofrecer abrir `mailto:` con ese email y copiar el email al
portapapeles.

#### Scenario: Mailto y copiar disponibles
- **WHEN** se muestra una card de email
- **THEN** hay controles usables de mailto y de copiar

### Requirement: Schema N emails por user
El sistema SHALL permitir N filas `user_emails` por `user_id` (eliminar la
restricción unique solo sobre `user_id`).

#### Scenario: Varios emails del mismo user
- **WHEN** se crean dos emails distintos para el mismo user
- **THEN** ambas filas persisten


## MODIFIED Requirements

### Requirement: Vista Edit de usuario unificada con tabs
El tab Emails y teléfonos deja de ser solo “próximamente” en la parte de
emails: SHALL mostrar la columna de emails adicionales operable según este
change; teléfonos sigue vacío.

#### Scenario: Tabs vacíos presentes
- **WHEN** se abre la ficha
- **THEN** el tab Emails y teléfonos existe; la columna emails es usable y la
  de teléfonos sigue sin CRUD
