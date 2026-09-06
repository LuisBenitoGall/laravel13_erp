Change: c2026-07-16-kernel-tooling-fase0
Rule: 10-architecture
Summary: Construir el "arnés" invisible de la Fase 0 — piezas de soporte transversales
(BelongsToCompany, Auditable, CurrentCompany, TableRequest/TableBuilder, NumberingService,
EnsureModuleIsActive), el generador make:domain-entity, el backstop de completitud (test +
comando module:audit), los tests de arquitectura y el pipeline de CI — antes de construir
ningún módulo de negocio. Es la Capa 0 de la Fase 0; no introduce ninguna entidad de
dominio.
Goals:
- Instalar Pest (pestphp/pest + pest-plugin-laravel + pest-plugin-arch), sustituyendo el
  PHPUnit puro del esqueleto — imprescindible para que existan tests y arch tests.
- Construir las piezas de soporte de app/Support/ ya especificadas en
  templates-backend.md §12: BelongsToCompany (global scope + fill de company_id en
  creating + belongsToCurrentCompany()), Auditable (created_by/updated_by), CurrentCompany
  (singleton por request + middleware SetCurrentCompany), TableRequest/TableBuilder
  (contrato de tablas de frontend.md §3.1), NumberingService (next(docType) con
  SELECT...FOR UPDATE, R-BD-05).
- Construir el middleware EnsureModuleIsActive (alias de ruta module:), listo para que el
  próximo change lo use aunque todavía no exista ningún Module real.
- Construir el generador make:domain-entity con sus stubs (tooling.md §1): firma completa
  con flags --status/--numbered/--translatable/--lines/--seed/--notifiable/--exportable/
  --no-frontend, incluida la auto-provisión de Functionality + permisos (R-AUT-07) cuando
  corresponda.
- Construir el backstop de completitud: test de completitud
  (tests/Architecture/EntityCompletenessTest.php) + comando
  artisan module:audit {domain?} {entity?} (tooling.md §2), incluyendo el chequeo de
  integridad Functionality↔permisos.
- Añadir los tests de arquitectura iniciales (tooling.md §2: estructura/capas, tenancy,
  capas, higiene general).
- Configurar PHPStan (nivel 6, larastan) y afinar Pint (preset laravel) — quedaron
  pendientes tras el change bootstrap-inertia-react.
- Escribir el pipeline de CI (tooling.md §4) como fichero de workflow.
- Helper Pest withCurrentCompany() en tests/Pest.php.
NonGoals:
- Ninguna entidad de dominio ni módulo de negocio (Module, Functionality, Spatie
  Permission/Teams, CompanyModules, autenticación...) — eso es el siguiente change
  (nombre provisional: admin-catalog-permissions), que se construye DESPUÉS de este, ya
  usando el generador.
- BelongsToCompany/CurrentCompany se testean contra un modelo/tabla de prueba desechable
  (fixture), NO contra una tabla `companies` real — esa tabla no existe todavía y no hace
  falta crearla aquí.
- El generador se valida con tests automatizados propios (invocan el comando Artisan
  contra un dominio/entidad de scratch dentro del propio test, verifican los ficheros
  generados y limpian después) — no se deja ninguna entidad de ejemplo permanente en el
  proyecto.
- Ejecución real del pipeline de CI en un runner remoto: se escribe el fichero de
  workflow, pero su ejecución depende de que exista un remoto Git configurado (fuera de
  alcance decidir esto aquí).
- Nada de frontend más allá de lo que el generador es capaz de producir genéricamente (no
  hay entidad real que renderizar en pantalla en este change).
Skills:
- laravel-patterns
- laravel-specialist
- php-pro
Notes:
- El change anterior (bootstrap-inertia-react) ya está completado: propuesto, aplicado y
  archivado — Inertia+React+TS+Tailwind4 renderiza de verdad.
- Decisión de secuenciación de esta sesión: este change va ANTES del change de Admin
  (Module/Functionality/Spatie Teams/UI catalog/auth mínimo propio, nombre provisional
  admin-catalog-permissions) — ese otro change quedó en pausa, con todo su alcance ya
  debatido y conservado, a la espera de que este generador exista.
- Todas las piezas de este change ya están especificadas con detalle en:
  docs/standards/templates-backend.md §12 (piezas de soporte), docs/standards/tooling.md
  §1-§4 (generador, arch tests, backstop, calidad estática, CI), docs/standards/
  architecture.md (reglas R-* que estas piezas deben cumplir), docs/standards/
  libraries.md (paquetes ya canonizados: pestphp/pest ^4, pest-plugin-laravel,
  pest-plugin-arch, larastan/larastan, laravel/pint ya instalado).
- Pest no está instalado todavía (el esqueleto trae PHPUnit puro) — instalarlo es parte
  de este change.
- Riesgo: NumberingService y TableBuilder se validan con casos sintéticos (tipos de
  documento/tablas de prueba), no con un documento de negocio real — su validación
  end-to-end ocurrirá cuando el change de Admin (o posteriores) los usen de verdad.


## ADDED Requirements

### Requirement: Kernel de tenancy y soporte transversal
El sistema SHALL proporcionar los mecanismos base de tenancy y auditoría
(BelongsToCompany, Auditable, CurrentCompany, NumberingService, TableRequest/TableBuilder)
como piezas reutilizables en app/Support/, verificadas con modelos/tablas de prueba, sin
depender de ninguna entidad de negocio real.

#### Scenario: Aislamiento de tenant verificado con fixture
- **WHEN** un test crea dos registros de un modelo de prueba con BelongsToCompany, cada
  uno perteneciente a una "empresa" distinta (fixture)
- **THEN** una consulta desde el contexto de una de las empresas solo devuelve sus
  propios registros

#### Scenario: Numeración concurrente sin duplicados
- **WHEN** dos procesos solicitan a la vez el siguiente número de un tipo de documento de
  prueba vía NumberingService
- **THEN** cada uno recibe un número distinto y consecutivo, sin colisiones (bloqueo
  FOR UPDATE)

---

### Requirement: Generador make:domain-entity
El sistema SHALL proporcionar un comando `make:domain-entity {domain} {entity}` que, con
sus flags, genere de una vez todos los artefactos "Siempre" de la matriz de
module-playbook.md, sin sobreescribir ficheros existentes.

#### Scenario: Generación completa de una entidad
- **WHEN** se ejecuta `make:domain-entity` contra un dominio/entidad de prueba con un
  flag activado (p. ej. `--status`)
- **THEN** se crean migración, modelo, enum, policy, actions, requests, resource,
  controller, factory, test feature, tipos TS y páginas React correspondientes,
  conformes a los templates

#### Scenario: No sobreescritura
- **WHEN** se ejecuta el generador sobre una entidad cuyos ficheros ya existen
- **THEN** no se sobreescribe ningún fichero salvo que se pase `--force` explícito

---

### Requirement: Backstop de completitud
El sistema SHALL detectar automáticamente cuando a una entidad le falta alguno de sus
artefactos "Siempre", tanto en CI (test de completitud) como bajo demanda (comando
module:audit).

#### Scenario: CI detecta un artefacto faltante
- **WHEN** un modelo de dominio no tiene su Policy, Factory, FormRequest, Resource o
  Controller hermano
- **THEN** el test de completitud falla señalando el fichero exacto ausente

#### Scenario: Auditoría legible bajo demanda
- **WHEN** se ejecuta `artisan module:audit <domain> <entity>`
- **THEN** se muestra una tabla en consola con cada artefacto y su estado ✓/✗

---

### Requirement: Calidad estática y CI
El sistema SHALL tener PHPStan (nivel 6) y Pint en verde sobre el código existente, y un
pipeline de CI que ejecute lint/typecheck/tests en orden.

#### Scenario: Calidad estática en verde
- **WHEN** se ejecuta `vendor/bin/phpstan analyse` y `vendor/bin/pint --test`
- **THEN** ambos terminan sin errores

#### Scenario: Pipeline de CI definido
- **WHEN** se revisa el repositorio
- **THEN** existe un fichero de workflow de CI que ejecuta, en orden, instalación →
  calidad estática → tests (unit/feature/arch) → build de frontend → auditoría de
  dependencias, según tooling.md §4

---

## MODIFIED Requirements

No aplica — no hay capability previa relacionada con el kernel o el tooling que
modificar (la única capability existente, `frontend-bootstrap`, es del change anterior y
no se toca).
