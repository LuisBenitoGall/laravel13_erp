## Purpose

Calendarios laborales 1:1 por Workplace en el dominio Calendar, con días
nombrados tipados por DayType de la Company, rejilla anual, herencia entre
años y copia entre sedes.

## Requirements

### Requirement: Dominio Calendar y menú Calendarios
El núcleo de calendarios laborales SHALL vivir en el dominio `Calendar`. El
Module `rrhh` SHALL exponer Functionality `calendars` y menú **Calendarios**.
SHALL NOT colocar los modelos núcleo solo bajo `App\Domains\HR`.

#### Scenario: Acceso menú
- **WHEN** un usuario con `rrhh.access` y `calendars.view` obtiene el menú
- **THEN** ve Calendarios y puede abrir la funcionalidad

### Requirement: Un WorkCalendar por Workplace
El sistema SHALL asociar como máximo un `WorkCalendar` por Workplace, con
`workplace_id` único y `company_id` igual a la matriz dueña del workplace.
SHALL permitir find-or-create al seleccionar la sede.

#### Scenario: Unique por sede
- **WHEN** ya existe calendario para la sede W y se solicita otro para W
- **THEN** se reutiliza el existente (no hay dos filas)

#### Scenario: Perímetro
- **WHEN** un actor intenta operar el calendario de un workplace fuera de la
  company en sesión
- **THEN** se deniega

### Requirement: Días con nombre y tipo de la empresa
Cada día del calendario laboral SHALL tener fecha única por calendario,
nombre y `day_type_id` de un DayType de la misma Company. La sede SHALL
poder crear, editar y eliminar esos días desde la rejilla anual.

#### Scenario: Alta de día
- **WHEN** el actor asigna en la sede W una fecha con nombre y tipo de la
  company
- **THEN** queda persistido en el WorkCalendar de W

#### Scenario: Tipo de otra company
- **WHEN** se intenta usar un day_type_id de otra company
- **THEN** se rechaza

### Requirement: Rejilla anual reutilizable
La UI SHALL mostrar una rejilla anual (componente wrapper reutilizable) con
navegación por año. SHALL NOT depender de FullCalendar en este change.

#### Scenario: Cambio de año
- **WHEN** el actor pasa del año N al N+1 en el calendario de una sede
- **THEN** ve los días del año N+1 de ese WorkCalendar

### Requirement: Heredar entre años
El sistema SHALL permitir heredar los días de un año origen a un año destino
en el mismo WorkCalendar (upsert/overwrite por fecha; 29-feb omitido si el
destino no es bisiesto).

#### Scenario: Heredar N → N+1
- **WHEN** el actor hereda el año N hacia N+1 en la sede W
- **THEN** los días aplicables de N existen en N+1 con el mismo nombre y tipo

### Requirement: Copiar entre sedes
El sistema SHALL permitir copiar los días de un año desde el WorkCalendar de
una sede origen al de una sede destino del mismo `company_id` (matriz), con
find-or-create del destino y overwrite de fechas de ese año.

#### Scenario: Copia W1 → W2
- **WHEN** el actor copia el año Y de W1 a W2 (misma company)
- **THEN** el calendario de W2 contiene esos días para Y
