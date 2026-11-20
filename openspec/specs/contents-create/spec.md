## Purpose

Alta e Index de Contents (Información/Contenido) bajo Configuración,
redirect al Edit real tras Continuar, e infraestructura HelpTip/cache.

## Requirements

### Requirement: Create Content mínimo
El sistema SHALL permitir crear un Content con nombre y tipo
(Información | Contenido). SHALL NOT mostrar status en el Create.
SHALL persistir status Inactive por defecto. SHALL persistir
`company_id` null. Tras guardar SHALL redirigir al Edit real.

Cuando el tipo es Información (Help), el actor SHALL proporcionar un
`code` explícito; el sistema SHALL persistirlo normalizado a
minúsculas **sin** slugificar el nombre, validando formato
(`a-z0-9` con segmentos separados por `.` o `-`) y unicidad (incl.
soft-deleted). Cuando el tipo es Contenido (Article), el sistema SHALL
generar `code` único a partir del nombre (comportamiento previo).

#### Scenario: Alta Continuar
- **WHEN** el actor completa nombre y tipo Contenido (Article) y continúa
- **THEN** se crea el Content Inactive con company_id null, `code`
  generado desde el nombre, y navega al Edit real

#### Scenario: Alta Continuar Información con code
- **WHEN** el actor completa nombre, tipo Información y code
  `companies.name` (formato válido y libre) y continúa
- **THEN** se crea el Content Inactive con `code` exactamente
  `companies.name` y navega al Edit real

#### Scenario: Información sin code
- **WHEN** el actor elige Información y omite el code
- **THEN** la validación rechaza el alta y no se crea el registro

#### Scenario: Code duplicado o formato inválido
- **WHEN** el code ya existe (incl. soft-deleted) o viola el formato
- **THEN** la validación rechaza el alta

### Requirement: Create UI muestra code solo para Información
El Create SHALL mostrar el campo código únicamente cuando el tipo
seleccionado es Información, con ayuda que indique la convención
`{helpScope}.{fieldKey}` (ej. `companies.nif`).

#### Scenario: Cambio de tipo a Artículo
- **WHEN** el actor pasa de Información a Contenido en el Create
- **THEN** el campo código deja de mostrarse y no es requerido

### Requirement: Index y menú Contenidos
El sistema SHALL listar Contents en Index bajo Configuración
(Functionality + permisos Spatie) y SHALL permitir soft-delete desde el
listado.

#### Scenario: SA abre Contenidos
- **WHEN** Super Admin abre Contenidos desde Configuración
- **THEN** ve el Index

#### Scenario: Soft delete desde Index
- **WHEN** el actor elimina un Content desde el Index
- **THEN** se aplica soft delete

### Requirement: HelpTip e infraestructura de resolución
El sistema SHALL exponer un componente HelpTip por `code` y resolución
por mapa cacheado por locale (BD source of truth; sin JSON runtime).
Codes sin contenido Active usable SHALL no romper la página.

#### Scenario: Tip sin contenido útil
- **WHEN** se renderiza un code inexistente o Inactive
- **THEN** no hay error de página; el tip no muestra contenido útil
