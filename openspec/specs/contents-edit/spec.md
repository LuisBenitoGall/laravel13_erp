## Purpose

Edición real de Contents: cabecera de ficha con historial y tipo
solo-lectura, formulario multiidioma según kind, sin cambiar code/kind.

## Requirements

### Requirement: Cabecera Edit con historial y tipo RO
La página Edit de Content SHALL mostrar `RecordSummary` con título del
registro, identificador `code`, el tipo (`kind`) como campo de solo
lectura junto a las fechas de creación y actualización. SHALL NOT
permitir editar `kind` ni `code`.

#### Scenario: Summary visible
- **WHEN** el actor abre el Edit de un Content
- **THEN** ve título, badge code, tipo RO y fechas de historial

#### Scenario: Eliminar desde Edit
- **WHEN** el actor confirma Eliminar en las acciones superiores
- **THEN** se soft-borra el Content y vuelve al Index

#### Scenario: Kind no editable
- **WHEN** el actor guarda el formulario
- **THEN** el `kind` del Content no cambia

### Requirement: Update Content según kind
El sistema SHALL permitir actualizar name, status, title, excerpt,
referrer y video; y el body `content` (Wysiwyg) cuando kind es Article.
Help SHALL NOT exigir body `content`. Tras guardar SHALL invalidar la
cache de HelpTip. SHALL NOT usar `serialize()`.
`referrer` y `video` SHALL persistirse como JSON array de strings
(múltiples valores; vacío = `[]`). `video` SHALL ser opcional en todo
kind (Help y Article) y SHALL NOT exigirse como campo obligatorio ni
como array no vacío. En el formulario Edit, referrer y video SHALL
aparecer **antes** de los campos traducibles (LangTabs); los campos con
traducción SHALL ser los últimos del formulario.

#### Scenario: Guardar Help
- **WHEN** el actor edita title/excerpt de un Help y guarda
- **THEN** se persisten traducciones y el tip puede resolverse si Active

#### Scenario: Guardar Article
- **WHEN** el actor edita un Article con content y guarda
- **THEN** se persiste el HTML allowlist del body

#### Scenario: Guardar Article sin vídeo
- **WHEN** el actor guarda un Article sin URLs de vídeo (video vacío o
  ausente como lista vacía)
- **THEN** el guardado se acepta y video queda como `[]`

#### Scenario: Code inmutable en update
- **WHEN** el actor intenta alterar code vía request
- **THEN** el code persistido no cambia

#### Scenario: Varios referrer/video
- **WHEN** el actor guarda varios enlaces en referrer o video
- **THEN** se persisten como array JSON y HelpTip puede exponerlos

#### Scenario: Orden del formulario Edit
- **WHEN** el actor abre Edit
- **THEN** referrer/video están por encima de LangTabs (traducciones)

### Requirement: Insertar imagen de galería en Wysiwyg Article
El sistema SHALL permitir, desde el Wysiwyg del cuerpo de un Content
Article, abrir un selector de MediaAsset y, al elegir uno, insertar un
`<img>` con `src` = URL del activo y `alt` = alt del activo (vacío si no
hay). SHALL NOT ofrecer esa inserción en Content Help ni en excerpt.

#### Scenario: Insertar desde galería
- **WHEN** el actor en Edit Article abre el selector, elige un activo y
  confirma
- **THEN** el HTML del editor incluye un img con la URL de ese activo

#### Scenario: Help sin selector
- **WHEN** el actor edita un Content Help
- **THEN** el formulario no ofrece el selector de Galería en un Wysiwyg
  de body

### Requirement: Persistencia img en allowlist al guardar Article
El sistema SHALL permitir que un `<img>` con `src` y `alt` seguros
sobreviva al sanitizado al guardar el body de un Content Article, de modo
que tras recargar el Edit la imagen siga presente.

#### Scenario: Guardar Article con imagen
- **WHEN** el actor guarda un Article cuyo body contiene un img insertado
  desde galería
- **THEN** tras recargar el Edit, la imagen sigue presente

#### Scenario: Rechazo src inseguro
- **WHEN** el body enviado incluye un img con src de esquema no permitido
- **THEN** ese img no se persiste como HTML ejecutable inseguro
