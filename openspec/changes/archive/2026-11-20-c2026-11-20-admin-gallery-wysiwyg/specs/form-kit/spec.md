# Spec Delta

## MODIFIED Requirements

### Requirement: Wysiwyg en el form-kit
El sistema SHALL exponer `Form/Wysiwyg` reutilizable basado en
`@tiptap/react`, controlado por valor HTML string, integrable con `Field`,
con toolbar mínima de formato (negrita, cursiva, listas, headings). SHALL
soportar nodos imagen en el HTML del editor. Cuando el consumidor active
la inserción desde galería, la toolbar SHALL ofrecer un control que abre
un selector de activos y, al confirmar, inserta un `<img>` con `src` y
`alt` del activo elegido. SHALL NOT acoplarse a Company. SHALL NOT
exponer el selector de galería si la inserción no está activada.

#### Scenario: Valor controlado
- **WHEN** un consumidor pasa `value` HTML y `onChange`
- **THEN** el editor refleja el valor y emite HTML al editar

#### Scenario: Uso con Field
- **WHEN** Wysiwyg se usa dentro de Field con error de validación
- **THEN** label y error se muestran de forma homogénea al resto del kit

#### Scenario: Insertar imagen con galería activada
- **WHEN** el consumidor activa la inserción desde galería y el actor
  elige un activo en el selector
- **THEN** el HTML emitido incluye un img con la URL y el alt de ese activo

#### Scenario: Sin inserción de galería
- **WHEN** el consumidor no activa la inserción desde galería
- **THEN** la toolbar no ofrece el selector de Galería
