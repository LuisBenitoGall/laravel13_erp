# Spec Delta

## ADDED Requirements

### Requirement: MediaPicker en el form-kit
El sistema SHALL exponer `Form/MediaPicker` reutilizable: diálogo de
selección de MediaAsset con búsqueda y listado paginado (scroll
infinito) alimentado por el endpoint JSON de Galería, que al confirmar
expone el activo elegido (al menos id, name, alt y URL). SHALL NOT
acoplarse a TipTap ni a Content.

#### Scenario: Selección confirma activo
- **WHEN** el consumidor abre el MediaPicker, elige un activo y confirma
- **THEN** el consumidor recibe el activo seleccionado con su URL y alt

#### Scenario: Buscar y cargar más
- **WHEN** el actor escribe en el buscador y hace scroll al final del
  grid con más páginas disponibles
- **THEN** se muestran resultados filtrados y se añaden los de la
  página siguiente

#### Scenario: Cambio de búsqueda sustituye lista
- **WHEN** el actor cambia el texto de búsqueda tras haber cargado
  varias páginas
- **THEN** la lista se sustituye por la primera página del nuevo
  filtro (no se concatenan resultados del filtro anterior)


## MODIFIED Requirements

### Requirement: Wysiwyg en el form-kit
El sistema SHALL exponer `Form/Wysiwyg` reutilizable basado en
`@tiptap/react`, controlado por valor HTML string, integrable con `Field`,
con toolbar mínima de formato (negrita, cursiva, listas, headings). SHALL
soportar nodos imagen en el HTML del editor. Cuando el consumidor active
la inserción desde galería, la toolbar SHALL ofrecer un control que abre
el `Form/MediaPicker` y, al confirmar, inserta un `<img>` con `src` y
`alt` del activo elegido. SHALL NOT acoplarse a Company. SHALL NOT
exponer el selector de galería si la inserción no está activada. SHALL
NOT mantener un selector de galería distinto del MediaPicker para ese
flujo.

#### Scenario: Valor controlado
- **WHEN** un consumidor pasa `value` HTML y `onChange`
- **THEN** el editor refleja el valor y emite HTML al editar

#### Scenario: Uso con Field
- **WHEN** Wysiwyg se usa dentro de Field con error de validación
- **THEN** label y error se muestran de forma homogénea al resto del kit

#### Scenario: Insertar imagen con galería activada
- **WHEN** el consumidor activa la inserción desde galería y el actor
  elige un activo en el MediaPicker
- **THEN** el HTML emitido incluye un img con la URL y el alt de ese activo

#### Scenario: Sin inserción de galería
- **WHEN** el consumidor no activa la inserción desde galería
- **THEN** la toolbar no ofrece el selector de Galería

#### Scenario: Buscar y cargar más en el selector
- **WHEN** el actor escribe en el buscador del selector y luego hace
  scroll hasta el final del grid con más páginas disponibles
- **THEN** se muestran resultados filtrados y se añaden los de la
  página siguiente

#### Scenario: Cambio de búsqueda sustituye lista
- **WHEN** el actor cambia el texto de búsqueda tras haber cargado
  varias páginas
- **THEN** la lista se sustituye por la primera página del nuevo
  filtro (no se concatenan resultados del filtro anterior)

#### Scenario: Insertar desde galería vía MediaPicker
- **WHEN** el actor en Edit Article abre el selector de galería del
  Wysiwyg, elige un activo y confirma
- **THEN** el HTML del editor incluye un img con la URL de ese activo
