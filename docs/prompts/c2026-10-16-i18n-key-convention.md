<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-16-i18n-key-convention
Rule: 10-architecture
Summary: Sustituir el esquema de claves i18n jerárquicas en inglés
(`admin.functionalities.form.name`) por índices en castellano reutilizables (estilo v1
`textos.php`: `nombre`, familias `usuario_nuevo` / `usuario_editar`), migrando todas las
claves y usos actuales, y corrigiendo el glosario/estándares para que la clave UI sea
en castellano.
Goals:
- Índices de traducción en **castellano** (snake_case), no en inglés de dominio.
- Reutilizar genéricos siempre que se pueda (`nombre`, `guardar`, `crear`, `editar`…);
  solo crear clave específica cuando el texto no sea el genérico (ej. `usuario_nombre`
  si hace falta “Nombre de usuario”).
- Agrupar por familias con prefijo: `usuario`, `usuario_nuevo`, `usuario_editar`,
  `usuario_eliminar`, …
- Migración **completa (A)**: todas las claves de `lang/{es,en,ca}.json` y todos los
  `t()` / `__()` existentes al nuevo esquema; sin convención mixta.
- Corregir la regla del glosario / R-FE-05 / rule i18n: clave UI en castellano; el
  código de dominio sigue en inglés.
- Mantener el runtime i18n ya cableado (locales es/en/ca, selector, `users.locale`,
  paridad y orden A–Z en CI); este change cambia la **ontología de claves**, no
  reinventar el runtime.
NonGoals:
- Volver a implementar selector, middleware de locale o preferencia en `users`.
- Partir JSON por dominio (sigue un fichero por locale, salvo que 10 decida lo
  contrario sin salirse del debate: el debate mantuvo un JSON por locale del change
  i18n-runtime).
- Completar traducciones EN incompletas de v1 como proyecto aparte (solo migrar lo
  que haya en v2 con valores correctos es/en/ca).
Skills:
- frontend-design
- laravel-specialist
- v1-reference
Notes:
- Motivación: claves actuales más largas que el valor, sin semántica útil, y
  duplicadas por pantalla (`admin.*.form.name`, `partners.*.form.name`, …).
- Referencia v1: `resources/lang/{es,en}/textos.php` — índices castellanos planos,
  familias `usuario_*` / `administrador_*`, ~3900 entradas; patrón a seguir en
  concepto, no clonar el stack Livewire.
- Decisión explore §3 (óptimo): **change nuevo** de convención + migración total;
  **no** reabrir el runtime. Si `c2026-10-15-i18n-runtime` aún no está archivado,
  archivarlo (o dejarlo a 30-testing) como housekeeping; este change no depende de
  re-proponer el runtime.
- Separador de familias: `_` (como v1), no puntos `a.b.c`.
- Claves sin tildes/ñ en el índice (estilo v1: `aceptar`, no caracteres especiales
  en la key), salvo que 10-architecture documente excepción puntual.
- Explore no implementa: propose → apply hacen la migración.


## ADDED Requirements

### Requirement: Claves i18n en castellano reutilizables
El sistema SHALL usar índices de traducción en castellano (snake_case), reutilizables
entre pantallas. Las etiquetas genéricas (`nombre`, `guardar`, `crear`, `editar`, etc.)
SHALL usarse en toda ocasión posible. Claves específicas (p. ej. `usuario_nombre`)
SHALL crearse solo cuando el texto visible no coincida con el genérico.

#### Scenario: Etiqueta genérica compartida
- **WHEN** varias pantallas muestran el mismo concepto “Nombre”
- **THEN** todas usan la clave `nombre` (no claves por dominio/pantalla)

#### Scenario: Texto específico de familia
- **WHEN** se necesita un texto distinto del genérico (p. ej. “Nombre de usuario”)
- **THEN** existe una clave de familia explícita (p. ej. `usuario_nombre`) y no se
  reutiliza mal el genérico

### Requirement: Familias por prefijo
Las etiquetas de una funcionalidad/entidad UI SHALL agruparse con prefijo de familia
(`usuario`, `usuario_nuevo`, `usuario_editar`, `usuario_eliminar`, …).

#### Scenario: Familia usuario
- **WHEN** se listan claves de la familia usuario
- **THEN** comparten el prefijo `usuario` / `usuario_*` de forma coherente

### Requirement: Migración completa al nuevo esquema
Todas las claves existentes en `lang/es.json`, `lang/en.json` y `lang/ca.json`, y todos
los usos en TSX/PHP (`t` / `__`), SHALL migrarse al nuevo esquema en este change. No
SHALL quedar la convención antigua `dominio.entidad.*` en inglés en el inventario
migrado.

#### Scenario: Inventario sin claves viejas
- **WHEN** se completa el change
- **THEN** no quedan claves del estilo `admin.functionalities.form.name` (ni equivalentes
  jerárquicos en inglés) en los JSON ni en las llamadas i18n del inventario

### Requirement: Estándares alineados
El glosario y la norma i18n (R-FE-05 / rule de agentes) SHALL indicar que la **clave UI**
es en castellano; el código de dominio (modelos, rutas, permisos) sigue en inglés según
glosario.

#### Scenario: Regla documentada
- **WHEN** un agente o desarrollador consulta el estándar i18n / glosario
- **THEN** encuentra la regla de claves en castellano y el patrón de familias /
  genéricos

---

## MODIFIED Requirements

### Requirement: Runtime multiidioma (convención de claves)
Respecto al runtime i18n ya existente (es/en/ca, un JSON por locale, paridad y orden
A–Z): la **forma de nombrar las claves** cambia a castellano snake_case reutilizable;
el runtime (locale, selector, persistencia) se conserva.

#### Scenario: Mismo runtime, nuevas claves
- **WHEN** la aplicación resuelve textos tras este change
- **THEN** sigue usando `lang/{locale}.json` y el locale activo, pero con índices en
  castellano del nuevo esquema
