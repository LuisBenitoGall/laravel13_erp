## Purpose

Runtime multiidioma es/en/ca: JSON por locale, preferencia `users.locale`, selector UI, cableado i18next/`__()`, migración UI existente y enforcement CI/rule.

## Requirements

### Requirement: Runtime multiidioma es / en / ca
El sistema SHALL soportar Castellano (`es`, default), English (`en`) y Català (`ca`).
La UI SHALL resolver textos vía claves i18n. SHALL existir `lang/ca.json` además de
`lang/es.json` y `lang/en.json`.

#### Scenario: Default castellano
- **WHEN** un usuario no tiene `locale` válido (null o ausente) y no hay locale de sesión
  válido
- **THEN** la aplicación usa `es`

#### Scenario: Locale del usuario
- **WHEN** un usuario autenticado tiene `locale` `en` o `ca`
- **THEN** la UI y `App::getLocale()` usan ese idioma

### Requirement: Cero literales de UI
Ningún texto visible de producto en PHP o TSX de la UI migrada SHALL quedar hardcodeado;
SHALL usarse clave de traducción (`__()` / `t()`).

#### Scenario: Pantalla migrada
- **WHEN** se renderiza una página del inventario migrado
- **THEN** los textos de producto visibles salen de claves i18n

### Requirement: JSON por locale con claves alfabéticas
Las traducciones SHALL vivir en `lang/es.json`, `lang/en.json` y `lang/ca.json`. Las
claves DENTRO de cada fichero SHALL estar ordenadas alfabéticamente. El conjunto de
claves SHALL ser el mismo en los tres ficheros. Los **nombres de clave** SHALL seguir
la convención castellana snake_case reutilizable (capability `i18n-key-convention`),
no el esquema jerárquico en inglés con puntos.

#### Scenario: Orden y paridad
- **WHEN** corre el enforcement de CI/arquitectura sobre los JSON
- **THEN** falla si faltan claves entre locales o si el orden no es alfabético

#### Scenario: Convención de nombre de clave
- **WHEN** se inspeccionan las claves de los JSON tras este change
- **THEN** cumplen el patrón de índices en castellano sin puntos jerárquicos

### Requirement: Preferencia de idioma en el usuario
El idioma elegido por un usuario autenticado SHALL persistirse en `users.locale`.

#### Scenario: Persistencia
- **WHEN** un usuario autenticado selecciona un idioma soportado en el selector
- **THEN** se guarda en `users.locale` y se reutiliza en requests posteriores

### Requirement: Selector de idioma en la UI
La aplicación SHALL ofrecer un selector de idioma usable en pantallas guest Auth y en
el shell autenticado.

#### Scenario: Guest cambia idioma
- **WHEN** un visitante no autenticado elige un idioma en el selector
- **THEN** la sesión refleja ese locale y la UI se muestra en ese idioma

#### Scenario: Usuario autenticado cambia idioma
- **WHEN** un usuario autenticado elige un idioma en el selector
- **THEN** se actualiza `users.locale` y la UI refleja el nuevo idioma

### Requirement: Migración de UI ya generada
El sistema SHALL migrar a claves i18n Auth, Legal, Dashboard, Welcome, Partners y Admin
(páginas y formularios ya generados), más mensajes flash/UI PHP de producto asociados,
antes de completar el change.

#### Scenario: Inventario migrado
- **WHEN** se completa el change
- **THEN** el inventario declarado en tasks no conserva literales de producto en UI

### Requirement: Listo para nuevos idiomas y nuevos textos
El proyecto SHALL documentar e imponer (rule de agentes + CI) que todo texto UI nuevo
añade la clave en todos los locales soportados con orden alfabético, y que un idioma
nuevo se añade ampliando la config de locales + nuevo `lang/{code}.json` + selector.

#### Scenario: Texto nuevo
- **WHEN** un desarrollo futuro introduce un texto de UI
- **THEN** el proceso obligado incluye clave en es, en y ca (u locales entonces
  soportados) y orden alfabético

#### Scenario: Idioma nuevo
- **WHEN** se incorpora un idioma adicional
- **THEN** basta ampliar la lista de locales soportados, crear su JSON y exponerlo en el
  selector, sin cambiar el patrón de claves
