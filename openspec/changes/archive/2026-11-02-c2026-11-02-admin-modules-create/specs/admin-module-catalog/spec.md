# Spec Delta

## ADDED Requirements

### Requirement: Alta de Module — slug y permiso
Al crear un `Module`, el cliente SHALL NOT enviar `slug`. El servidor SHALL generar el
slug desde `name`, garantizar unicidad case-insensitive e inmutabilidad tras el alta, y
provisionar el permiso comodín `{slug}.access`. Tras store exitoso SHALL redirigir a
Edit.

#### Scenario: Store sin slug en payload
- **WHEN** un usuario autorizado envía Create de Module sin campo slug y con name válido
- **THEN** el módulo se crea con slug derivado del name y permiso `{slug}.access`

#### Scenario: Slug único case-insensitive
- **WHEN** el slug derivado del name colisiona con un slug existente ignorando mayúsculas
- **THEN** la validación rechaza el alta

#### Scenario: Redirect a Edit tras alta
- **WHEN** el store de Module tiene éxito
- **THEN** la respuesta redirige a la ruta Edit de ese Module

### Requirement: Alta de Module — name y status
El alta de Module SHALL exigir `name` único case-insensitive, `level`, `icon`, `color` y
`status` (default Active si el cliente no fuerza otro valor válido).

#### Scenario: Name único case-insensitive
- **WHEN** se intenta crear un Module cuyo name colisiona con otro ignorando mayúsculas
- **THEN** la validación rechaza el alta

#### Scenario: Status por defecto Active
- **WHEN** se crea un Module sin indicar status inactivo
- **THEN** el módulo queda con status Active

### Requirement: Alta de Module — traducciones
`label` y `explanation` SHALL ser traducibles. Solo el locale principal del proyecto
(`config i18n.default`, hoy `es`) SHALL ser required; locales activos secundarios
opcionales.

#### Scenario: Traducciones — principal required
- **WHEN** falta label o explanation del locale principal
- **THEN** la validación rechaza el alta

#### Scenario: Traducciones — secundarios opcionales
- **WHEN** label y explanation del locale principal están presentes y otro locale activo
  queda vacío
- **THEN** el alta se acepta respecto a traducciones

### Requirement: UI Create de Module
La página Create de Module SHALL mostrar: nombre, nivel (select), icono (IconPicker
modal/grid), color (ColorPicker), status (toggle, default activo) y LangTabs con
label (text) + explanation (textarea simple, no Wysiwyg) por idioma activo. SHALL NOT
mostrar input de slug.

#### Scenario: Campos visibles Create
- **WHEN** el usuario abre `/admin/modules/create`
- **THEN** ve nombre, nivel, icono, color, status (toggle on) y LangTabs; no ve input
  de slug

#### Scenario: Explanation textarea
- **WHEN** edita explanation en una pestaña de LangTabs
- **THEN** el control es textarea simple (no Wysiwyg)
