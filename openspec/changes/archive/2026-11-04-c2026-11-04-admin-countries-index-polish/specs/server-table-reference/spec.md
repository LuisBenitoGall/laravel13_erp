# Spec Delta

## ADDED Requirements

### Requirement: Acciones respetan icono Lucide declarado
Cuando un `RowAction` declare `icon` (componente Lucide), el formatter de Acciones de
`ServerTable` SHALL renderizar ese icono en el botón. SHALL NOT sustituirlo por un
lápiz hardcodeado en botones no-danger. La variante danger MAY seguir usando el
icono de papelera por defecto si no declara `icon`.

#### Scenario: Icono custom en acción no-danger
- **WHEN** una fila declara una acción con `icon` Lucide distinto de Pencil
- **THEN** el botón de Acciones muestra ese icono, no el SVG de lápiz genérico

### Requirement: Ancho de columna Acciones ajustado al contenido
La columna Acciones de `ServerTable` SHALL dimensionarse de forma coherente con el
número de controles (toggles + botones), con `widthGrow` que no robe espacio vacío
desproporcionado al grupo de acciones (p. ej. `widthGrow: 0` y ancho por slots).

#### Scenario: Sin hueco muerto amplio en Acciones
- **WHEN** se renderiza un Index con varias Acciones (p. ej. toggle + 3 botones)
- **THEN** la columna Acciones no deja un hueco vacío amplio a un lado del grupo de
  controles
