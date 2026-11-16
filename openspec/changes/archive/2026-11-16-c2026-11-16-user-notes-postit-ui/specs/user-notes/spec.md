# Spec Delta

## ADDED Requirements

### Requirement: Look post-it estilo v1 en Dashboard
El Dashboard SHALL mostrar cada Note como post-it visualmente cercano a
v1: fondo amarillo tipo `#ffc`, tamaño aproximado 140×140, sombra,
rotación ligera entre notas, fecha con hora arriba-derecha, título y
cuerpo, acción de borrado abajo-derecha. SHALL usar solo CSS/Tailwind
del proyecto (SHALL NOT añadir dependencia npm de sticky notes).

#### Scenario: Aspecto post-it
- **WHEN** el actor abre el dashboard con notas
- **THEN** ve post-its con tamaño/color/sombra/rotación tipo v1 y fecha
  con hora

#### Scenario: Sin librería
- **WHEN** se implementa el look
- **THEN** no se añade paquete npm de post-its; solo estilos del proyecto

## MODIFIED Requirements

### Requirement: Post-its en el Dashboard
El Dashboard SHALL mostrar las Notes del usuario como post-its (fecha
**con hora**, título, cuerpo) con estilo visual tipo v1 y permitir
eliminar cada una. SHALL NOT mostrar la sección «Mis notas» ni un
empty state (p. ej. «Aún no hay notas…») cuando el user no tiene Notes;
la sección solo aparece si hay al menos una nota.

#### Scenario: Ver y borrar
- **WHEN** el actor tiene notas y abre el dashboard
- **THEN** ve los post-its con look v1 (incl. fecha con hora) y puede
  eliminar una nota propia

#### Scenario: Sin notas — sin empty state
- **WHEN** el actor no tiene Notes y abre el dashboard
- **THEN** no se muestra el bloque de notas (ni título «Mis notas» ni
  mensaje vacío); el alta sigue disponible desde el icono del topbar
