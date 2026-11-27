# Spec Delta

## MODIFIED Requirements

### Requirement: Modal búsqueda avanzada genérico (opt-in)
El sistema SHALL proveer un componente modal genérico de búsqueda avanzada
que recibe los campos/inputs por parámetro y dispara el filtrado remoto
por el mismo canal `filter[]`. SHALL exponer un disparador canónico en la
toolbar de `ServerTable` a la izquierda de «Mostrar / ocultar columnas»
cuando el Index lo active. Integración desde ActionBar permanece opt-in
compatible pero no es el canon documentado. En el dashboard de Módulos
SHALL NOT mostrarse el botón. El modal SHALL ofrecer Limpiar, Aplicar y
flujo de presets (guardar / seleccionar sin aplicar / renombrar / borrar)
cuando el Index declare `table_key` de presets.

#### Scenario: Módulos sin búsqueda avanzada
- **WHEN** se renderiza el Index de Módulos
- **THEN** no aparece el botón de búsqueda avanzada en la ActionBar ni en
  la toolbar

#### Scenario: Abrir desde toolbar
- **WHEN** un Index opta por filtros avanzados y el actor pulsa el botón
  de la toolbar a la izquierda de columnas
- **THEN** se abre el modal con los campos declarados por ese Index

#### Scenario: Modal aplica filtros
- **WHEN** una página Index activa advanced search y el usuario aplica el
  modal
- **THEN** `ServerTable` solicita datos con los filtros del modal en el
  protocolo table (`merge` o `replace` según el Index)

#### Scenario: Limpiar en el modal
- **WHEN** el actor pulsa Limpiar en el modal
- **THEN** los campos del draft quedan vacíos y los filtros avanzados
  aplicados dejan de enviarse (la tabla vuelve a depender solo de
  cabecera u otros canales activos)

#### Scenario: Documentación alineada
- **WHEN** se consulte `frontend.md` / catálogo de componentes tras el
  change
- **THEN** describe botón en toolbar, Limpiar/Aplicar, presets y el
  consumidor Products

## ADDED Requirements

### Requirement: Kit de filtros avanzados reutilizable
El sistema SHALL exponer el kit (botón toolbar + modal con campos por
parámetro + canal `advancedFilters`) de forma reutilizable para cualquier
Index ServerTable, sin acoplar la cáscara a un dominio concreto.

#### Scenario: Campos por consumidor
- **WHEN** dos Indexes distintos activan el kit con sets de campos
  diferentes
- **THEN** cada modal muestra solo los campos que su Index declaró
