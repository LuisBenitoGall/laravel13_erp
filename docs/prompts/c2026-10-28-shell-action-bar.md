<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-28-shell-action-bar
Rule: 10-architecture
Summary: Añadir el bloque 3 de chrome de vista (ActionBar): botones de acción alineados
    a la izquierda con icono, Volver desde URL de origen si existe, Delete (trash +
    confirm) siempre el último con espacio mínimo; array declarativo extensible. Migrar
    páginas AppLayout. Botones de formulario fuera de este bloque.
Goals:
- Barra de acciones de vista bajo el breadcrumb, estructura fija: botones alineados a la
  izquierda (no a la derecha de la pantalla).
- CTAs con icono representativo a la izquierda del label; textos vía i18n.
- Si la vista proviene de otra (URL de origen disponible), el primer botón es Volver con
  chevron izquierda hacia esa URL; si no hay URL heredada/origen, no mostrar Volver (sin
  fallback al index del breadcrumb).
- Si hay Eliminar: icono trash, con confirmación; siempre el último de la fila, con
  espacio mínimo respecto al resto de botones (no empujar al extremo derecho del layout).
- Contrato por array declarativo/extensible (poder añadir tipos de acción después;
  dropdowns no obligatorios ahora pero el diseño no debe cerrar esa puerta).
- Aplicar en todas las páginas AppLayout (alcance B); se irán enriqueciendo acciones.
- Separación clara: este bloque = acciones de **vista**; botones de **formulario**
  (guardar/cancelar, etc.) viven dentro del div del formulario (arriba del form OK),
  porque puede haber varios formularios o tabs en la misma vista.
NonGoals:
- Implementar FormActions / barra guardar-cancelar del kit de formularios (otro sitio).
- Dominio real de topbar stubs; ActionBar de ciclo de vida de documento (facturar,
  confirmar asiento…) salvo que coincida visualmente y 10 lo unifique sin inventar
  requisitos de negocio.
- Dropdowns de acciones en este change (solo dejar el contrato abierto).
- Inventar pantallas o maestros nuevos.
Skills:
- frontend-design
- laravel-specialist
Notes:
- Bloques previos: topbar (1), breadcrumb (2). Este es el bloque 3.
- Referencia v1: fila de botones bajo “Gestión de ventas / Editar venta”; Volver con
  chevron; delete rojo al final del grupo.
- Catálogo `frontend.md`: existe `Records/ActionBar` y `ConfirmDialog`; 10 decide
  naming (ActionBar de vista vs FormActions) sin mezclar botones de form en el bloque 3.
- Delete siempre con confirm (patrón ConfirmDialog del estándar).
- Origen del Volver: URL de donde proviene el usuario; sin origen → sin botón.


## ADDED Requirements

### Requirement: ActionBar de vista (bloque 3)
El sistema SHALL mostrar bajo el breadcrumb una barra de acciones de vista con botones
alineados a la izquierda. Cada CTA SHALL mostrar un icono a la izquierda del texto i18n.
SHALL, si existe URL de origen de la navegación, mostrar como primer botón un Volver
(chevron izquierda) hacia esa URL; si no hay URL de origen, SHALL NOT mostrar Volver.
SHALL, si la página declara Eliminar, mostrar un botón trash como último de la fila, con
espacio mínimo respecto a los demás botones, y exigir confirmación antes de ejecutar.
SHALL aceptar las acciones vía un array declarativo extensible. SHALL NOT colocar en esta
barra los botones de submit/cancel del formulario.

#### Scenario: Fila alineada a la izquierda
- **WHEN** una página declara acciones de vista
- **THEN** los botones se alinean a la izquierda bajo el breadcrumb

#### Scenario: Volver con origen
- **WHEN** el usuario llega a la vista desde otra URL (origen disponible)
- **THEN** el primer botón es Volver con chevron hacia esa URL

#### Scenario: Sin origen no hay Volver
- **WHEN** no hay URL de origen heredada
- **THEN** no se muestra el botón Volver

#### Scenario: Delete último con confirm
- **WHEN** la página declara acción delete
- **THEN** el trash es el último botón, con espacio mínimo respecto al resto, y al
  activarlo se pide confirmación antes de borrar

#### Scenario: Form buttons fuera del bloque 3
- **WHEN** una vista tiene formulario(s)
- **THEN** guardar/cancelar (u equivalentes) están en el contenedor del formulario, no en
  la ActionBar de vista

---

### Requirement: Migración AppLayout con ActionBar
El sistema SHALL adaptar las páginas que usan AppLayout para declarar el array de
acciones de vista (mínimo las que ya existan en UI, p. ej. Crear en Index), dejando el
contrato listo para ampliar.

#### Scenario: Index con Crear en ActionBar
- **WHEN** el usuario abre un Index que antes tenía botón Crear en el contenido
- **THEN** Crear aparece en la ActionBar de vista (con icono), no suelto como único patrón


## MODIFIED Requirements

### Requirement: (shell / AppLayout — frontera bloque 3)
El chrome de vista bajo breadcrumb SHALL incluir la ActionBar de acciones de vista según
el contrato anterior. Los botones de formulario permanecen en el form. 10 escribe deltas
sobre shell-breadcrumb / frontend catálogo según archive.
