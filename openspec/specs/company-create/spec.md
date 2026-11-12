## Purpose

Alta de Company desde Mis Empresas: formulario mínimo (name, tradename, nif,
status), slug automático, NIF único con formato, redirect a Edit.

## Requirements

### Requirement: Formulario Create mínimo
El Create de Company SHALL pedir solo name (required), tradename (nullable),
nif (required) y status (required, enum CompanyStatus). SHALL NOT pedir slug
ni logo en este formulario. Authz: `companies.create`.

#### Scenario: Alta válida
- **WHEN** un actor autorizado envía name, nif válido único y status
- **THEN** se crea la Company y se redirige a su Edit

#### Scenario: Sin slug ni logo en UI
- **WHEN** se muestra el Create
- **THEN** no hay campos de slug ni logo

### Requirement: Slug automático en Create
El sistema SHALL generar el slug en servidor a partir del name (helper
UniqueSlug / colisión con sufijo), sin input del cliente.

#### Scenario: Slug derivado
- **WHEN** se crea una company con name dado
- **THEN** persiste un slug único derivado de ese name

### Requirement: NIF único y con formato
El nif en Create SHALL ser único entre companies no soft-deleted y SHALL
pasar validación de formato (NIF/NIE/CIF españoles y EIN EE.UU. según la
regla portada de v1). El Input SHALL declarar maxLength alineado al
FormRequest (máx. 12).

#### Scenario: NIF duplicado
- **WHEN** el nif ya existe en otra company activa
- **THEN** se rechaza con error de validación

#### Scenario: NIF formato inválido
- **WHEN** el nif no cumple el formato/validador
- **THEN** se rechaza con error de validación

#### Scenario: maxLength nif
- **WHEN** se renderiza el campo nif del Create
- **THEN** el Input aplica maxLength 12 y el Request usa max:12
