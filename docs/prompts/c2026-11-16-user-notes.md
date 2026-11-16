<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-16-user-notes
Rule: 10-architecture
Summary: Notas internas de usuario (modelo Note / tabla notes): modal
  desde el icono del topbar para crear título+nota; post-its en el
  dashboard con borrado; sin company_id ni status; tope 50 por user.
Goals:
- Modelo `Note` (referencia v1): `user_id`, `title`, `note`. Sin
  `company_id`. Sin `status`.
- Topbar icono notas → modal formulario (título + nota requeridos) →
  guardar; badge con conteo.
- Dashboard: notas como post-its (fecha, título, cuerpo, delete).
- Tope 50 Notes por user.
- Sustituir stub de notas en shell-topbar (`NotesStub`).
NonGoals:
- Scope por tenant / `company_id`.
- Columna `status`.
- Notificaciones / chat / favoritos (otros changes).
- Página Index ServerTable de notas (salvo lo que haga falta para
  «Ver todas» → dashboard, como v1).
Skills:
- openspec-propose
- v1-reference
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore` + captura v1 (modal Nueva nota / Ver todas +
  post-it dashboard).
- Decisión Luis: modal (no popover vacío); sin status; límite 50;
  notas personales cross-tenant.
- v1 «Ver todas» enlaza al dashboard donde se listan los post-its.
- Excepción posible a “solo popover” del stub notas en shell-topbar:
  este change usa **modal/dialog** para el formulario (acordado).
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Crear Note desde el topbar
El sistema SHALL permitir, desde el icono de notas del topbar, abrir un
modal con formulario (título y nota requeridos) para crear un `Note`
del usuario autenticado. SHALL NOT superar 50 Notes por user. SHALL NOT
asociar `company_id`.

#### Scenario: Alta nota
- **WHEN** el actor introduce título y nota y guarda
- **THEN** se crea un Note con su user_id y aparece en el dashboard

#### Scenario: Tope 50
- **WHEN** el actor ya tiene 50 notas e intenta otra
- **THEN** no se crea y se informa del límite

### Requirement: Post-its en el Dashboard
El Dashboard SHALL mostrar las Notes del usuario como post-its (fecha,
título, cuerpo) y permitir eliminar cada una.

#### Scenario: Ver y borrar
- **WHEN** el actor tiene notas y abre el dashboard
- **THEN** ve los post-its y puede eliminar una nota propia

### Requirement: Notas personales sin tenant
Las Notes SHALL pertenecer solo al user; SHALL NOT filtrarse por company
de sesión.

#### Scenario: Cambio de empresa
- **WHEN** el actor cambia de company de sesión
- **THEN** sigue viendo las mismas notas personales en el dashboard


## MODIFIED Requirements

### Requirement: Stubs de notificaciones, notas, chat y favoritos
(El topbar con stubs parciales.) SHALL dejar de tratar notas como stub
vacío: el icono de notas SHALL abrir el modal real de alta. Chat (y lo
que siga stub) no cambia en este change; favoritos ya son reales en su
change.

#### Scenario: Icono notas real
- **WHEN** el usuario activa el icono de notas
- **THEN** ve el modal de nueva nota (formulario título + nota), no el
  empty stub
