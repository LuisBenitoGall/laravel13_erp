# Spec Delta

## MODIFIED Requirements

### Requirement: Stubs de notificaciones, notas, chat y favoritos
El sistema SHALL ofrecer UI stub para notificaciones y notas como
**popover anclado bajo el icono** (cabecera + empty state; acciones
visibles sin efecto de dominio), y stub de chat. Los favoritos SHALL
dejar de ser stub vacío: el kebab vertical SHALL abrir el formulario
real para guardar la vista actual como Preference. Los paneles stub
SHALL compartir el mismo criterio de anclaje bajo el icono (SHALL NOT
mezclar dialog centrado con popover). Los clicks stub de notificaciones,
notas y chat SHALL resultar en empty UI (sin toast de próximamente y sin
backend). Los badges SHALL poder mostrar cero mientras no haya dominio.

#### Scenario: Popover notificaciones stub
- **WHEN** el usuario abre la campana
- **THEN** ve el panel tipo referencia (título, empty state de no hay
  notificaciones, refresh / ver todo / marcar leídas como empty UI)
  anclado bajo el icono

#### Scenario: Popover notas stub
- **WHEN** el usuario abre el icono de notas
- **THEN** ve un popover esqueleto bajo el icono sin persistencia ni
  listado real (no dialog centrado)

#### Scenario: Chat y favoritos stub
- **WHEN** el usuario activa chat
- **THEN** la UI no ejecuta dominio (empty UI)

#### Scenario: Kebab favoritos real
- **WHEN** el usuario activa el icono de favoritos (tres puntos)
- **THEN** ve el formulario para nombrar y guardar la vista actual (no
  empty stub)
