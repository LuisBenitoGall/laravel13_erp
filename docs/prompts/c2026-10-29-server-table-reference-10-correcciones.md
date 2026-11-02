<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-29-server-table-reference
Rule: 10-architecture
Summary: Rectificar el contrato OpenSpec del change para que las Acciones del Index
    de Módulos incluyan un toggle de activar/desactivar (además de Edit), sin recarga
    de página, con aspecto alineado al tamaño del resto de botones de la columna
    Acciones. Cierra la contradicción detectada por 25-architecture-review (design/deltas
    decían «solo Edit»; implementación y task 6.13 ya tienen el toggle).
Goals:
- Actualizar los artefactos del change (`design.md`, deltas en `specs/`, y `tasks.md`
  / proposal si hace falta) para que el contrato coincida con la decisión de Luis:
  toggle de estado + Edit en Módulos.
- Dejar explícito: es un **toggle** (no un botón que navega a Edit para cambiar
  estado); la mutación **no recarga la página**; el control **encaja en tamaño** con
  el resto de botones de la columna Acciones.
- Resolver la ambigüedad A/B anotada bajo la task 6.13 (elegida **A**: el toggle se
  queda; 10 enmienda contrato).
NonGoals:
- No implementar código de producción (eso es 20, solo si tras la rectificación queda
  alguna task de UI/CSS pendiente).
- No quitar el Edit ni reabrir Delete en Módulos.
- No tocar el canal `filter[]` / AdvancedSearchModal (ya cerrado por 20).
- No migrar otros Index; no inventar acciones nuevas fuera de toggle + Edit.
- No reabrir los MEDIUM del informe 25 salvo que el propio contrato del toggle lo
  exija.
Skills:
- openspec-propose (actualizar artefactos del change activo; no crear un change nuevo)
- tenancy-rbac (solo lectura si el design menciona status de Module / shell)
Notes:
- Origen: auditoría 25 → RETURN TO IMPLEMENTATION. HIGH residual: D3 y
  `admin-ui-login` / escenario «solo Edit» vs Index + task 6.13 + `frontend.md` §3.2
  (toggle + Edit). Luis confirma **opción A**.
- Código actual (referencia, no rediseñar desde cero): `Admin/Modules/Index.tsx`
  (`kind: 'toggle'`, `patchJson` a `modules.status`), `ServerTable` rowActions toggle,
  `UpdateModule` + guard shell. El contrato debe legitimar el toggle; si el aspecto
  (tamaño vs botones de Acciones) no cumple lo pedido por Luis, añadir task explícita
  para 20 — no “arreglar” UI en este rol.
- Tras validar OpenSpec, hand-off: si solo cambian artefactos y el UI ya cumple tamaño,
  re-auditoría 25 breve; si hay task de polish visual, primero 20 y luego 25.
- Idioma del agente: español. Ficha: `docs/agents/10-architecture.md`.


## MODIFIED Requirements

### Requirement: Acciones del Index de Módulos (referencia ServerTable)
El contrato deja de exigir «Acciones = solo Edit» en el dashboard de Módulos. En esa
columna SHALL haber, en este orden: (1) un **toggle** para activar/desactivar el módulo
y (2) la acción **Edit**. SHALL NOT ofrecer Delete en Módulos (sin cambio respecto al
escenario previo de no-Delete).

El toggle SHALL persistir el cambio de estado **sin recargar la página** (mutación
async / JSON; la fila se actualiza en la tabla). SHALL ser un control tipo toggle
(switch), no un icono de Edit ni navegación a otra pantalla para cambiar el estado.

El aspecto del toggle SHALL **encajar totalmente con el tamaño** del resto de botones
de la columna Acciones (misma escala visual de la celda; no un switch desproporcionado
respecto a los botones icono).

Actualizar, como mínimo: `design.md` D3 (y texto relacionado en proposal si aún dice
«solo Edit»), el delta `specs/server-table-reference/spec.md` (escenario de Acciones
en Módulos) y el delta `specs/admin-ui-login/spec.md` (frase «Acciones solo Edit»).
Alinear `tasks.md` (cerrar la nota A/B de 6.13; añadir task de polish de tamaño del
toggle si el estado actual no cumple el criterio visual). No inventar requisitos no
listados aquí.

#### Scenario: Acciones en Módulos = toggle + Edit
- **WHEN** se muestra el listado de Módulos
- **THEN** la columna Acciones ofrece un toggle de activar/desactivar y Edit (icono +
  tooltip), y no ofrece Delete

#### Scenario: Toggle sin recarga
- **WHEN** el usuario cambia el toggle de estado de un módulo en el listado
- **THEN** el estado se persiste sin recargar la página y la fila refleja el nuevo
  estado

#### Scenario: Tamaño del toggle alineado a Acciones
- **WHEN** se renderiza la columna Acciones en Módulos
- **THEN** el toggle tiene un tamaño visual coherente con el resto de botones de esa
  columna (misma escala de la celda)

#### Scenario: Listado de Module conforme a la referencia actualizada
- **WHEN** el superadmin visita el índice de Module
- **THEN** ve un `ServerTable` con Acciones = toggle de estado + Edit, toolbar,
  filtros de cabecera e i18n, sin búsqueda avanzada en la ActionBar
