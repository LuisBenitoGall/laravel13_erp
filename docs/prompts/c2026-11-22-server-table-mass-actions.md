<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-22-server-table-mass-actions
Rule: 10-architecture
Summary: Capacidad opt-in de acciones masivas en `<ServerTable>` (checkbox
  1ª columna + desplegable en toolbar) y piloto en Usuarios empresa con
  eliminar / activar / desactivar; API dinámica para más acciones después.
Goals:
- Añadir en `<ServerTable>` soporte opt-in de selección por checkbox en la
  **primera columna** (izquierda) y un desplegable de acciones masivas a la
  **izquierda** de «Mostrar/ocultar columnas».
- Selección = **solo página actual** (no “todos los filtrados”).
- Desplegable **deshabilitado** hasta marcar al menos un checkbox.
- Contrato **dinámico**: cada tabla declara su lista de acciones masivas;
  no todas las tablas llevan la columna (opt-in); se podrán añadir más
  acciones en el futuro sin rediseñar el mecanismo.
- Piloto: Index **Usuarios empresa** con acciones masivas **eliminar**
  (quitar vínculo `user_companies` de la company de sesión, con confirm),
  **activar** y **desactivar** (status del user).
- Backend bulk = Action dedicada del dominio + Policy (no endpoint genérico
  mágico).
- Dejar en estándares/contrato que, cuando Luis pida acciones masivas en
  otra tabla, el agente las incluya y **pregunte qué acciones** lleva.
NonGoals:
- Selección de todos los registros filtrados (across pages).
- Enviar emails u otras acciones masivas fuera de eliminar / activar /
  desactivar en este change.
- Activar la columna en tablas distintas de Usuarios empresa en este change.
- Cambiar el significado de eliminar fila en Usuarios empresa (sigue siendo
  detach de vínculo, no borrado del User global).
Skills:
- openspec-propose
- inertia-react-development
- tenancy-rbac
- laravel-best-practices
Notes:
- Origen: `/opsx-explore` columna acciones masivas Tabulator. Decisiones:
  (1) alcance selección = página actual; (2) dropdown disabled con 0
  seleccionados; (3) piloto Usuarios empresa; (4) Action+Policy por dominio;
  (5) acciones piloto = eliminar, activar/desactivar; lista dinámica.
- Hoy `ServerTable` tiene `rowActions` (columna derecha) + toolbar
  columnas/page size/export; no hay selección ni bulk.
- Eliminar en Usuarios empresa ya documentado: quita `user_companies` de
  sesión; User global no se borra.
- «Crear usuario» vive en ViewActionBar; el desplegable masivo es del
  toolbar de la tabla (fila de Mostrar/ocultar columnas), no de la
  ActionBar.


## ADDED Requirements

### Requirement: Acciones masivas opt-in en ServerTable
El sistema SHALL permitir que un `<ServerTable>` declare de forma opt-in una
lista dinámica de acciones masivas. Si la declara, SHALL mostrar una columna
de checkbox como primera columna a la izquierda y un desplegable de esas
acciones a la izquierda del botón «Mostrar/ocultar columnas». SHALL NOT
mostrar columna ni desplegable si la tabla no declara acciones masivas.
La selección SHALL limitarse a las filas de la página actual. El desplegable
SHALL permanecer deshabilitado mientras no haya al menos una fila marcada.

#### Scenario: Tabla sin mass actions
- **WHEN** un Index usa ServerTable sin declarar acciones masivas
- **THEN** no aparece columna de checkbox ni desplegable de acciones masivas

#### Scenario: Tabla con mass actions
- **WHEN** un Index declara una lista de acciones masivas en ServerTable
- **THEN** aparece la columna checkbox a la izquierda y el desplegable a la
  izquierda de «Mostrar/ocultar columnas» con esas acciones

#### Scenario: Desplegable sin selección
- **WHEN** hay acciones masivas pero ninguna fila marcada
- **THEN** el desplegable está deshabilitado

#### Scenario: Selección solo página actual
- **WHEN** el usuario marca checkboxes y cambia de página
- **THEN** la selección no implica filas de otras páginas (alcance = página
  actual)

### Requirement: Piloto Usuarios empresa — eliminar, activar, desactivar
El Index Usuarios empresa SHALL declarar acciones masivas dinámicas:
eliminar (detach del vínculo con la company de sesión, con confirmación),
activar y desactivar el status de los users seleccionados de la página
actual. El backend SHALL ejecutar cada bulk vía Action del dominio con
autorización por Policy. SHALL ser posible ampliar la lista de acciones
masivas de esa u otras tablas en changes futuros sin cambiar el mecanismo
base del ServerTable.

#### Scenario: Eliminar masivo con confirm
- **WHEN** el actor autorizado selecciona uno o más users de la página y
  confirma la acción masiva eliminar
- **THEN** se eliminan los vínculos `user_companies` con la company de
  sesión para esos users y los User globales no se borran por esa acción

#### Scenario: Activar masivo
- **WHEN** el actor autorizado selecciona uno o más users de la página y
  elige activar
- **THEN** el status de esos users queda activo

#### Scenario: Desactivar masivo
- **WHEN** el actor autorizado selecciona uno o más users de la página y
  elige desactivar
- **THEN** el status de esos users queda inactivo

---

## MODIFIED Requirements

### Requirement: Index Usuarios empresa
Además de las acciones de fila existentes (activar/desactivar, eliminar
vínculo, editar) y el CTA Crear, el Index SHALL exponer las acciones
masivas del requirement piloto (eliminar, activar, desactivar) sobre la
selección de la página actual vía el contrato opt-in de ServerTable.

#### Scenario: Toolbar con acciones masivas
- **WHEN** el usuario autorizado abre Usuarios empresa
- **THEN** ve el desplegable de acciones masivas a la izquierda de
  «Mostrar/ocultar columnas» (deshabilitado hasta seleccionar filas)
