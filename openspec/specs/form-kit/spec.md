## Purpose

Kit Form en `Components/Form`: Field + controles (Input, Textarea, NumberInput, Select,
DatePicker, Checkbox, Switch, LangTabs, ColorPicker, IconPicker, Dropzone, Wysiwyg).

## Requirements

### Requirement: Dropzone en el form-kit
El sistema SHALL exponer `Form/Dropzone` reutilizable (basado en
`react-dropzone`), configurable al menos por formatos admitidos (`accept`) y
tamaño máximo (`maxSize`), y opcionalmente `maxFiles` / `multiple`. SHALL NOT
acoplarse a `UserImage`.

#### Scenario: Configuración por props
- **WHEN** un consumidor pasa accept y maxSize distintos
- **THEN** el Dropzone aplica esas restricciones en cliente

#### Scenario: Rechazo cliente
- **WHEN** el usuario selecciona un fichero fuera de accept o maxSize
- **THEN** se informa del error sin subir ese fichero

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

### Requirement: Kit Form MVP en Components/Form
El sistema SHALL exponer componentes reutilizables `Form/Field`, `Input`, `Textarea`,
`NumberInput`, `Select` (combobox con búsqueda sobre opciones locales), `DatePicker`,
`Checkbox`, `Switch`, `LangTabs`, `ColorPicker`, `IconPicker`, `Dropzone` y
`Wysiwyg` integrados con el kit (RHF/zod o espejo Inertia vía Field donde aplique), con
label/error/help homogéneos vía `Field` cuando corresponda. SHALL documentar uso en el
template frontend y mantener al menos un formulario de referencia en el kit.

#### Scenario: Field unifica label y error
- **WHEN** un campo del kit se usa en un formulario RHF con error de validación
- **THEN** se muestran label y mensaje de error de forma homogénea

#### Scenario: Controles MVP disponibles
- **WHEN** un desarrollador construye un formulario nuevo con el kit
- **THEN** puede componer Field con Input, Textarea, NumberInput, Select, DatePicker,
  Checkbox, Switch, LangTabs, ColorPicker, IconPicker, Dropzone y Wysiwyg sin inventar
  primitivas ad-hoc para esos tipos

#### Scenario: Select filtra opciones locales
- **WHEN** el usuario escribe en el Select del kit con un conjunto de opciones locales
- **THEN** la lista visible se filtra por el texto introducido

#### Scenario: Input respeta maxLength
- **WHEN** se usa el Input del kit sin override de maxLength
- **THEN** aplica el default del estándar vigente (255 / R-FE-08)

#### Scenario: Fuera de alcance explícito
- **WHEN** se revisa el entregable de este change
- **THEN** Wysiwyg forma parte del kit Form (deja de estar excluido)

#### Scenario: Formulario de referencia migrado
- **WHEN** se completa el change
- **THEN** al menos un formulario existente del ERP usa el kit (migración mínima de
  referencia; Create de Module cuenta)

### Requirement: LangTabs en el form-kit
El sistema SHALL exponer `Form/LangTabs`: pestañas de idioma generadas dinámicamente
desde los idiomas activos del runtime (`supportedLocales`). El consumidor SHALL
declarar el contenido por locale (render prop / children). SHALL integrar errores de
validación por locale cuando el formulario use RHF/zod o el espejo Inertia.

#### Scenario: Tabs según idiomas activos
- **WHEN** un formulario usa LangTabs y el runtime expone N idiomas activos
- **THEN** se renderizan N pestañas, una por idioma, con etiqueta de idioma

#### Scenario: Contenido por pestaña
- **WHEN** el usuario cambia de pestaña en LangTabs
- **THEN** ve los campos traducibles declarados para ese locale (mismo layout)

### Requirement: ColorPicker en el form-kit
El sistema SHALL exponer un `Form/ColorPicker` reutilizable (sin dependencia nueva fuera
del canon) usable con Field, para elegir un color obligatorio en formularios.

#### Scenario: ColorPicker selecciona color
- **WHEN** el usuario elige un color en ColorPicker
- **THEN** el valor del campo es un color usable en CSS (p. ej. hex) y Field muestra
  label/error homogéneos

### Requirement: IconPicker en el form-kit
El sistema SHALL exponer un `Form/IconPicker` reutilizable: control que abre un modal
con grid de iconos Lucide de un catálogo curado (orientado a ERP; sin emoticonos). El
valor persistido SHALL ser el nombre Lucide seleccionado.

#### Scenario: Selección desde modal grid
- **WHEN** el usuario abre IconPicker y elige un icono del grid
- **THEN** el valor del campo es el nombre Lucide de ese icono y el control refleja la
  selección

#### Scenario: Catálogo sin emoticonos
- **WHEN** se muestra el grid del IconPicker
- **THEN** solo aparecen iconos del catálogo curado ERP (sin emoticonos / iconos
  frívolos)

### Requirement: Field helpCode para HelpTip
El componente `Form/Field` SHALL aceptar una prop opcional `helpCode`
(distinta del hint textual `help`) que coloca el icono HelpTip a la
derecha del control hijo. SHALL además resolver automáticamente
`{helpScope}.{fieldKey}` cuando el Field está bajo un helpScope de
formulario y no hay helpCode explícito. SHALL NOT sustituir ni eliminar
el hint `help` bajo el campo.

#### Scenario: helpCode en Field
- **WHEN** un Field declara helpCode y el code resuelve a un Help Active
- **THEN** el control muestra el icono HelpTip a su derecha

#### Scenario: help y helpCode coexisten
- **WHEN** un Field declara help y helpCode
- **THEN** el hint textual sigue bajo el campo y el icono queda a la
  derecha del control

#### Scenario: helpScope sin helpCode
- **WHEN** un Field bajo helpScope no declara helpCode y el code
  derivado existe Active
- **THEN** muestra el HelpTip igual que con helpCode explícito
