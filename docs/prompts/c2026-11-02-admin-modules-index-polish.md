<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-02-admin-modules-index-polish
Rule: 10-architecture
Summary: Pulir el Index de Módulos (`/admin/modules`) antes de tocar otros Index:
    icono Lucide con `title`, Nivel traducido, quitar la columna Estado (el toggle basta)
    y ofrecer toggle de activar/desactivar en **todas** las filas (incluidos Admin y
    Workspace), reutilizando el componente de toggle ya existente.
Goals:
- Proponer un **change nuevo** (no hay changes abiertos) que cubra el polish del Index
  de Módulos acordado en explore.
- Icono: renderizar el icono Lucide correspondiente; el nombre del icono (`settings`,
  etc.) va en el atributo HTML `title`.
- Nivel: mostrar la etiqueta traducida (no el valor crudo tipo `admin_only` / `basic`).
- Quitar la columna Estado del listado: el toggle de Acciones es la señal visual de
  activo/inactivo.
- Acciones: toggle disponible para **todos** los módulos, incluidos Admin y Workspace
  (eliminar la restricción actual que oculta el toggle en esos slugs). Reutilizar el
  componente/toggle ya existente en ServerTable/Módulos.
NonGoals:
- No migrar ni pulir otros Index distintos de Módulos en este change (este Index va
  primero; el resto queda fuera de alcance).
- No inventar pantallas, flujos ni reglas nuevas de “acceso solo administradores
  durante pruebas” más allá de lo que ya implica activar/desactivar el módulo en el
  catálogo; el motivo de negocio queda como contexto en Notes.
Skills:
- openspec-propose (crear change nuevo + artefactos)
- tenancy-rbac (status de Module / impacto al desactivar módulos shell)
- inertia-react-development (Index Inertia + React)
Notes:
- Origen: sesión `/opsx-explore` post-archivado de `c2026-10-29-server-table-reference`.
  `openspec list` vacío → change **nuevo**.
- Estado actual de referencia (código, no rediseñar desde cero):
  `resources/js/Pages/Admin/Modules/Index.tsx` ya tiene `kind: 'toggle'` + Edit, pero
  oculta el toggle cuando `slug === 'admin' || slug === 'workspace'` (`shellCritical`).
  Luis elige **opción 2**: hay que poder activar/desactivar **también** esos módulos.
- Motivo de negocio (Luis): habrá módulos nuevos en pruebas (solo acceso para
  administradores hasta validar) y desactivaciones por mantenimiento; hace falta la
  opción de activar/desactivar para todos los módulos.
- Consecuencia acordada al quitar Estado: el toggle es la única señal de activo/inactivo
  en el listado.
- Idioma del agente: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Columna Icono del Index de Módulos
En el listado de Módulos, la columna de icono SHALL renderizar el icono Lucide
correspondiente al valor almacenado. El nombre del icono (p. ej. `settings`) SHALL
exponerse en el atributo HTML `title` del elemento del icono (tooltip nativo).

#### Scenario: Icono dibujado con title = nombre
- **WHEN** se muestra una fila de módulo cuyo icono es un nombre Lucide válido
- **THEN** la celda muestra el icono dibujado y el atributo `title` del elemento
  contiene ese nombre de icono

### Requirement: Columna Nivel traducida
En el listado de Módulos, la columna Nivel SHALL mostrar la etiqueta traducida del
nivel (i18n), no el valor crudo del enum/string (`admin_only`, `basic`, etc.).

#### Scenario: Nivel legible vía i18n
- **WHEN** se muestra una fila de módulo con un nivel conocido
- **THEN** la celda Nivel muestra el texto traducido correspondiente, no el slug/código
  crudo

### Requirement: Sin columna Estado; toggle en todas las filas
El Index de Módulos SHALL NOT mostrar una columna Estado. El estado activo/inactivo
SHALL verse vía el toggle de la columna Acciones.

La columna Acciones SHALL ofrecer el toggle de activar/desactivar para **todas** las
filas de módulo, incluidos los slugs Admin y Workspace. SHALL reutilizar el mecanismo
de toggle ya existente (mismo tipo de control / mutación async sin recarga de página
ya usado en Módulos). Edit permanece; no se reabre Delete en este change.

#### Scenario: Sin columna Estado
- **WHEN** el usuario visita el índice de Módulos
- **THEN** la tabla no incluye columna Estado y el activo/inactivo se refleja en el
  toggle de Acciones

#### Scenario: Toggle también en Admin y Workspace
- **WHEN** se muestra una fila de módulo con slug `admin` o `workspace`
- **THEN** la columna Acciones ofrece el mismo toggle de activar/desactivar que el resto
  de módulos (además de Edit)

#### Scenario: Toggle sin recarga (sin cambio de contrato previo)
- **WHEN** el usuario cambia el toggle de estado de cualquier módulo en el listado
- **THEN** el estado se persiste sin recargar la página y la fila refleja el nuevo
  estado
