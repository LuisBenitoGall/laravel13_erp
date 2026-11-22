<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-22-user-list-quick-view
Rule: 10-architecture
Summary: Vista rápida de usuario en listados Usuarios empresa y Todos los
  usuarios: icono ojo a la izquierda del nombre abre panel derecho con
  `UserProfileReadout`; cambiar de fila recarga sin cerrar; cierre solo con X.
Goals:
- En `/admin/company-users` y `/admin/all-users`: icono a la izquierda del
  nombre (tooltip tipo «Ver detalle») que abre un panel lateral derecho con
  la ficha rápida `UserProfileReadout` (el componente ya revisado).
- Con el panel abierto, pinchar el ojo de otro usuario SHALL cargar esa ficha
  en el mismo panel **sin** tener que cerrarlo antes.
- Cierre del panel: **solo** el botón X (no clic fuera / overlay como cierre).
- **Usuarios empresa**: botón Editar en la cabecera del panel **solo si** el
  actor en sesión tiene permiso de edición de usuarios; navega al Edit
  existente de company-users.
- **Todos los usuarios**: misma vista rápida de lectura; **sin** botón Editar
  (solo lectura).
- Reutilizar el contenido de `UserProfileReadout` (no clonar el panel rico de
  v1 de la captura de referencia).
NonGoals:
- Cerrar el panel con clic fuera o Escape (no debatido; cierre = solo X).
- Acciones de teléfono/email del panel v1 (call, WhatsApp, edit/delete en
  cards).
- Vista RO de empresa ni enlaces de empresa navegables (siguen vacíos en el
  readout).
- Cambiar mass actions, rowActions ni el formulario de Edit.
- Otros listados distintos de company-users y all-users.
Skills:
- openspec-propose
- inertia-react-development
- tenancy-rbac
Notes:
- Origen: `/opsx-explore` tras ficha rápida. Referencia visual: panel derecho
  v1 con ojo «Ver detalle»; contenido v2 = `UserProfileReadout`.
- Decisiones: (1) Editar en company-users solo con permiso update; (2) también
  all-users, ahí sin Editar; (3) cierre solo X; switch de usuario sin cerrar.
- Hoy ambos Indexes usan ServerTable sin panel; no hay Sheet/Drawer en
  catálogo UI — 10 decide contenedor (añadir shadcn Sheet al canon si hace
  falta, u otro patrón del proyecto).
- Datos del readout no van en `/table`; hace falta carga al abrir/cambiar
  (endpoint o reuso de payload tipo `UserEditPageData` acotado) — 10 fija el
  contrato sin inventar campos de negocio.


## ADDED Requirements

### Requirement: Vista rápida desde listado Usuarios empresa
En el Index Usuarios empresa, un control a la izquierda del nombre SHALL abrir
un panel derecho con `UserProfileReadout` del user elegido. Cambiar a otro
user vía el mismo control SHALL actualizar el panel sin cerrarlo. El panel
SHALL cerrarse solo con el botón X. SHALL mostrar botón Editar hacia el Edit
de company-users únicamente si el actor tiene permiso de edición de usuarios.

#### Scenario: Abrir ficha desde ojo
- **WHEN** el actor autorizado pulsa el icono de ver detalle junto al nombre
- **THEN** se abre el panel derecho con la ficha rápida de ese user

#### Scenario: Cambiar user sin cerrar
- **WHEN** el panel está abierto y el actor pulsa el icono de otro user
- **THEN** el panel muestra la ficha del nuevo user sin haberse cerrado

#### Scenario: Cierre solo con X
- **WHEN** el panel está abierto
- **THEN** se cierra al pulsar X y no depende de clic fuera para cerrar

#### Scenario: Editar condicionado a permiso
- **WHEN** el actor tiene permiso de edición de usuarios
- **THEN** el panel de Usuarios empresa ofrece Editar al Edit del user
- **WHEN** el actor no tiene ese permiso
- **THEN** el panel no ofrece Editar

### Requirement: Vista rápida desde Todos los usuarios
El Index Todos los usuarios SHALL ofrecer la misma vista rápida de lectura
(icono, panel, switch sin cerrar, cierre solo X) con `UserProfileReadout`.
SHALL NOT mostrar botón Editar en ese panel.

#### Scenario: All-users solo lectura
- **WHEN** el actor abre la vista rápida desde Todos los usuarios
- **THEN** ve la ficha rápida y no ve botón Editar

---

## MODIFIED Requirements

### Requirement: Index Usuarios empresa
Además del ServerTable y acciones existentes, el Index SHALL exponer el
disparador de vista rápida en la columna nombre según el requirement de vista
rápida Usuarios empresa.

#### Scenario: Icono en nombre
- **WHEN** el actor abre Usuarios empresa
- **THEN** cada fila muestra el control de ver detalle a la izquierda del
  nombre
