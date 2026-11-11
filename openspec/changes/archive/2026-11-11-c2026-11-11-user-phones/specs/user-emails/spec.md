## MODIFIED Requirements

### Requirement: Tab contactos — columna emails adicionales
El tab Emails y teléfonos de la ficha de usuario SHALL mostrar dos columnas:
izquierda emails adicionales, derecha teléfonos. La izquierda SHALL listar
los emails del user en cards (no tabla), con botón de alta y acciones
mailto, copiar y eliminar. La columna derecha de teléfonos SHALL gobernarse
por la capability `user-phones` (ya no es placeholder vacío de este spec).

#### Scenario: Layout dos columnas
- **WHEN** el actor abre el tab Emails y teléfonos
- **THEN** ve la columna emails a la izquierda y la de teléfonos a la
  derecha

#### Scenario: Listado en cards
- **WHEN** el user tiene emails adicionales
- **THEN** cada uno se muestra en una card con el email y, si hay,
  observaciones; sin acción de editar
