# Spec Delta

## ADDED Requirements

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
