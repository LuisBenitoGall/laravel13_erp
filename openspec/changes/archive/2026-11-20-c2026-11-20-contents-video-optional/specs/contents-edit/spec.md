# Spec Delta

## MODIFIED Requirements

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
