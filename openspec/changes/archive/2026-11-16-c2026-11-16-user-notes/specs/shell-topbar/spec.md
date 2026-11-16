# Spec Delta

## MODIFIED Requirements

### Requirement: Stubs de notificaciones, notas, chat y favoritos
El sistema SHALL ofrecer UI stub para notificaciones como **popover
anclado bajo el icono** (cabecera + empty state; acciones visibles sin
efecto de dominio), y stub de chat. Los favoritos SHALL abrir el
formulario real de Preference. Las notas SHALL dejar de ser stub vacío:
el icono de notas SHALL abrir un **modal/dialog** real para crear una
Note (título + nota). Los paneles stub (notificaciones, chat) SHALL
compartir anclaje bajo el icono. Los clicks stub de notificaciones y
chat SHALL resultar en empty UI. Los badges SHALL poder mostrar cero
mientras no haya dominio; el badge de notas SHALL reflejar el conteo
real del user cuando haya Notes.

#### Scenario: Popover notificaciones stub
- **WHEN** el usuario abre la campana
- **THEN** ve el panel tipo referencia (título, empty state de no hay
  notificaciones, refresh / ver todo / marcar leídas como empty UI)
  anclado bajo el icono

#### Scenario: Popover notas stub
- **WHEN** el usuario abre el icono de notas
- **THEN** ve el modal de nueva nota (formulario título + nota), no un
  empty stub

#### Scenario: Chat y favoritos stub
- **WHEN** el usuario activa chat
- **THEN** la UI no ejecuta dominio (empty UI)

#### Scenario: Kebab favoritos real
- **WHEN** el usuario activa el icono de favoritos (tres puntos)
- **THEN** ve el formulario para nombrar y guardar la vista actual (no
  empty stub)
