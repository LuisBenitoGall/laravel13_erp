# Spec Delta

## Purpose

Alta e Index de Contents (Información/Contenido) bajo Configuración,
Edit placeholder tras Continuar, e infraestructura HelpTip/cache sin
formulario de edición completo.

## ADDED Requirements

### Requirement: Create Content mínimo
El sistema SHALL permitir crear un Content con solo nombre y tipo
(Información | Contenido). SHALL NOT mostrar status en el Create.
SHALL persistir status Inactive por defecto. SHALL generar `code`
único. Tras guardar SHALL redirigir al Edit placeholder. SHALL
persistir `company_id` null.

#### Scenario: Alta Continuar
- **WHEN** el actor completa nombre y tipo y continúa
- **THEN** se crea el Content Inactive con company_id null y navega al
  Edit placeholder

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

### Requirement: Edit placeholder
Tras el Create, el sistema SHALL mostrar una página Edit placeholder
(sin formulario de edición completo de este change).

#### Scenario: Placeholder
- **WHEN** el actor llega al Edit tras Continuar
- **THEN** ve la página placeholder (no el Edit real por tipo)

### Requirement: HelpTip e infraestructura de resolución
El sistema SHALL exponer un componente HelpTip por `code` y resolución
por mapa cacheado por locale (BD source of truth; sin JSON runtime).
Codes sin contenido Active usable SHALL no romper la página.

#### Scenario: Tip sin contenido útil
- **WHEN** se renderiza un code inexistente o Inactive
- **THEN** no hay error de página; el tip no muestra contenido útil
