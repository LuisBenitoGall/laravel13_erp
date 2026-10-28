## ADDED Requirements

### Requirement: ActionBar de vista (bloque 3)
El sistema SHALL mostrar bajo el breadcrumb una barra de acciones de vista (`ViewActionBar`)
con botones alineados a la izquierda. Cada CTA SHALL mostrar un icono a la izquierda del
texto i18n. SHALL aceptar las acciones vía un array declarativo extensible. SHALL, si
existe URL de origen de la navegación, anteponer un botón Volver (chevron izquierda)
hacia esa URL; si no hay URL de origen, SHALL NOT mostrar Volver. SHALL, si la página
declara Eliminar, mostrar un botón trash como último de la fila, con espacio mínimo
respecto a los demás botones, y exigir confirmación antes de ejecutar. SHALL NOT colocar
en esta barra los botones de submit/cancel del formulario.

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
contrato listo para ampliar. SHALL NOT inventar acciones delete sin endpoint existente.

#### Scenario: Index con Crear en ActionBar
- **WHEN** el usuario abre un Index que antes tenía botón Crear en el contenido
- **THEN** Crear aparece en la ActionBar de vista (con icono), no suelto como único patrón
