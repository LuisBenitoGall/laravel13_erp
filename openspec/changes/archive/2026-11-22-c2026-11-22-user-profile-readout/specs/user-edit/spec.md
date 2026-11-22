# Spec Delta

## ADDED Requirements

### Requirement: Ficha rápida sin NIF fecha activo ni público
La ficha rápida RO de usuario SHALL NOT mostrar NIF, fecha de nacimiento,
activo ni perfil público. SHALL mantener cabecera con avatar, nombre y email
principal.

#### Scenario: Campos ocultos en readout
- **WHEN** el actor abre el tab Perfil en `/admin/profile` o en Edit de
  Usuarios empresa
- **THEN** la ficha rápida no muestra NIF, fecha de nacimiento, activo ni
  perfil público

### Requirement: Emails adicionales copiables sin observación
En la ficha rápida, los emails adicionales SHALL mostrar solo la dirección
(sin observación/descripción) y un control que copia esa dirección al
portapapeles con toast de confirmación.

#### Scenario: Email sin observación
- **WHEN** el user tiene un email adicional con descripción
- **THEN** la ficha muestra la dirección sin el texto de observación

#### Scenario: Copiar email
- **WHEN** el actor pulsa el icono de copiar de un email adicional
- **THEN** la dirección queda en el portapapeles y se muestra un toast de
  confirmación

### Requirement: Empresas vinculadas en ficha rápida
Si el user tiene companies vinculadas, la ficha rápida SHALL listar solo el
nombre de cada una. El enlace a vista de empresa SHALL quedar vacío/no
navegable en este change. Si no hay empresas, SHALL NOT mostrar la sección.

#### Scenario: Con empresas
- **WHEN** el user tiene una o más companies vinculadas
- **THEN** la ficha lista el nombre de cada una sin navegar a una vista de
  empresa

#### Scenario: Sin empresas
- **WHEN** el user no tiene companies vinculadas
- **THEN** la ficha no muestra la sección de empresas

## MODIFIED Requirements

### Requirement: Tab Perfil con resumen RO y formulario
El tab Perfil SHALL mostrar dos columnas: izquierda un componente reutilizable
de resumen solo lectura (avatar = featured del user o placeholder; cabecera
nombre/email; emails adicionales sin observación + copiar; teléfonos; empresas
vinculadas si las hay; sin NIF/fecha/activo/perfil público en el resumen);
derecha el formulario con name, surname, email principal, `is_public`, fecha
de nacimiento, nif y `status` (sin `is_admin`). Un submit del tab SHALL
persistir esos campos según authz. Este change SHALL NOT alterar el formulario
de la columna derecha.

#### Scenario: Layout dos columnas
- **WHEN** el actor abre el tab Perfil
- **THEN** ve resumen RO a la izquierda (avatar featured o placeholder) y el
  formulario a la derecha

#### Scenario: Layout dos columnas sin tocar form
- **WHEN** el actor abre el tab Perfil tras este change
- **THEN** el formulario de la derecha sigue ofreciendo los mismos campos de
  perfil y la ficha rápida refleja el contenido revisado

#### Scenario: Guardar perfil
- **WHEN** envía el formulario del tab Perfil con datos válidos y autorización
- **THEN** se persisten los campos del tab (incluidos `is_public` y `status`)
