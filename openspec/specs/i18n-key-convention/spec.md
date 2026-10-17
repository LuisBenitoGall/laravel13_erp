## Purpose

Convención de claves i18n en castellano `snake_case` reutilizables (genéricos + familias por prefijo), migración completa desde el esquema jerárquico en inglés, y CI de forma de clave.

## Requirements

### Requirement: Claves i18n en castellano reutilizables
El sistema SHALL usar índices de traducción en castellano (`snake_case`, alfabeto
`[a-z0-9_]`), reutilizables entre pantallas. Las etiquetas genéricas SHALL usarse
siempre que el texto visible sea ese concepto. Claves específicas SHALL crearse solo
cuando el texto no coincida con el genérico.

#### Scenario: Etiqueta genérica compartida
- **WHEN** varias pantallas muestran el mismo concepto genérico (p. ej. «Nombre»)
- **THEN** todas usan la misma clave (p. ej. `nombre`), no claves por dominio/pantalla

#### Scenario: Texto específico de familia
- **WHEN** se necesita un texto distinto del genérico (p. ej. «Nombre de usuario»)
- **THEN** existe una clave explícita de familia (p. ej. `usuario_nombre`)

### Requirement: Familias por prefijo
Las etiquetas de una familia de UI SHALL agruparse con prefijo (`usuario`,
`usuario_nuevo`, `usuario_editar`, `usuario_eliminar`, …).

#### Scenario: Familia coherente
- **WHEN** se listan claves de una familia
- **THEN** comparten el prefijo de familia con separador `_`

### Requirement: Migración completa al nuevo esquema
El sistema SHALL migrar todas las claves de `lang/es.json`, `lang/en.json` y
`lang/ca.json`, y todos los usos `t()` / `__()` del inventario, al nuevo esquema. El
esquema jerárquico en inglés con puntos SHALL NOT permanecer en el inventario.

#### Scenario: Inventario sin claves viejas
- **WHEN** se completa el change
- **THEN** ninguna clave del inventario contiene `.` ni sigue el patrón
  `admin.*` / `partners.*` / `auth.login.*` antiguo

### Requirement: Estándares alineados
El glosario y la norma i18n SHALL indicar que la clave UI es en castellano; el código
de dominio permanece en inglés.

#### Scenario: Documentación
- **WHEN** se consulta glosario / R-FE-05 / rule i18n
- **THEN** describe claves castellanas, genéricos y familias

### Requirement: CI de forma de clave
La suite de arquitectura SHALL fallar si una clave en los JSON no cumple
`^[a-z0-9_]+$`.

#### Scenario: Clave con punto
- **WHEN** se introduce una clave con `.` en un JSON de idioma
- **THEN** el test de arquitectura falla
