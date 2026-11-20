# Spec Delta

## MODIFIED Requirements

### Requirement: Wysiwyg en el form-kit
El sistema SHALL exponer `Form/Wysiwyg` reutilizable basado en
`@tiptap/react`, controlado por valor HTML string, integrable con `Field`,
con toolbar mínima de formato (negrita, cursiva, listas, headings). SHALL
soportar nodos imagen en el HTML del editor. Cuando el consumidor active
la inserción desde galería, la toolbar SHALL ofrecer un control que abre
un selector de activos y, al confirmar, inserta un `<img>` con `src` y
`alt` del activo elegido. El selector SHALL permitir buscar activos y
cargar más resultados al llegar al final del grid (scroll infinito) vía
el listado JSON paginado de Galería; al cambiar la búsqueda SHALL
reiniciar en página 1 sustituyendo la lista. SHALL NOT acoplarse a
Company. SHALL NOT exponer el selector de galería si la inserción no
está activada.

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
