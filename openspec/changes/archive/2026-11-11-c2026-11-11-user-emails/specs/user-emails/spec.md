# Spec Delta

## Purpose

Emails adicionales del User (N por usuario): alta por modal, listado en cards
con mailto/copiar/eliminar, sin edición in-place; schema y authz alineados a
la ficha de usuario.

## ADDED Requirements

### Requirement: Tab contactos — columna emails adicionales
El tab Emails y teléfonos de la ficha de usuario SHALL mostrar dos columnas:
izquierda emails adicionales, derecha teléfonos. En este change la derecha
SHALL quedar vacía/placeholder. La izquierda SHALL listar los emails del user
en cards (no tabla), con botón de alta y acciones mailto, copiar y eliminar.

#### Scenario: Layout dos columnas
- **WHEN** el actor abre el tab Emails y teléfonos
- **THEN** ve la columna emails a la izquierda y la de teléfonos vacía a la
  derecha

#### Scenario: Listado en cards
- **WHEN** el user tiene emails adicionales
- **THEN** cada uno se muestra en una card con el email y, si hay,
  observaciones; sin acción de editar

### Requirement: Alta de email adicional por modal
El sistema SHALL permitir añadir un email adicional vía modal con email
obligatorio (formato email) y observaciones opcionales. El email SHALL ser
único en `user_emails` y distinto del email principal del user. Authz: self o
`company-users.update` + perímetro.

#### Scenario: Alta válida
- **WHEN** un actor autorizado envía email válido no duplicado ni igual al
  principal, con o sin observaciones
- **THEN** se persiste el `UserEmail` y aparece en el listado

#### Scenario: Duplicado o igual al principal
- **WHEN** el email ya existe en `user_emails` o coincide con `users.email`
  del mismo user
- **THEN** la operación se rechaza con error de validación

### Requirement: Eliminar email adicional
El sistema SHALL permitir eliminar un email adicional (con confirmación).
SHALL NOT ofrecer edición in-place.

#### Scenario: Eliminar
- **WHEN** el actor autorizado confirma eliminar un email adicional
- **THEN** la fila desaparece de `user_emails` y del listado

### Requirement: Acciones mailto y copiar
Cada card SHALL ofrecer abrir `mailto:` con ese email y copiar el email al
portapapeles.

#### Scenario: Mailto y copiar disponibles
- **WHEN** se muestra una card de email
- **THEN** hay controles usables de mailto y de copiar

### Requirement: Schema N emails por user
El sistema SHALL permitir N filas `user_emails` por `user_id` (eliminar la
restricción unique solo sobre `user_id`).

#### Scenario: Varios emails del mismo user
- **WHEN** se crean dos emails distintos para el mismo user
- **THEN** ambas filas persisten
