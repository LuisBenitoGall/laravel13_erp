# Spec Delta

## Purpose

Notas internas personales del usuario (`Note`): alta desde modal del
topbar (título + nota), visualización como post-its en el dashboard con
borrado, sin scope de company y con tope de 50 por user.

## ADDED Requirements

### Requirement: Crear Note desde el topbar
El sistema SHALL permitir, desde el icono de notas del topbar, abrir un
modal con formulario (título y nota requeridos) para crear un `Note` del
usuario autenticado. SHALL NOT superar 50 Notes por user. SHALL NOT
asociar `company_id`.

#### Scenario: Alta nota
- **WHEN** el actor introduce título y nota y guarda
- **THEN** se crea un Note con su user_id y aparece en el dashboard

#### Scenario: Tope 50
- **WHEN** el actor ya tiene 50 notas e intenta otra
- **THEN** no se crea y se informa del límite

### Requirement: Post-its en el Dashboard
El Dashboard SHALL mostrar las Notes del usuario como post-its (fecha,
título, cuerpo) y permitir eliminar cada una.

#### Scenario: Ver y borrar
- **WHEN** el actor tiene notas y abre el dashboard
- **THEN** ve los post-its y puede eliminar una nota propia

### Requirement: Notas personales sin tenant
Las Notes SHALL pertenecer solo al user; SHALL NOT filtrarse por company
de sesión.

#### Scenario: Cambio de empresa
- **WHEN** el actor cambia de company de sesión
- **THEN** sigue viendo las mismas notas personales en el dashboard

#### Scenario: Ownership
- **WHEN** un actor intenta borrar una Note de otro user
- **THEN** la operación falla (403/404)
