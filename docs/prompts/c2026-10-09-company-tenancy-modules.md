<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-09-company-tenancy-modules
Rule: 10-architecture
Summary: Cerrar la Fase 0 restante: actores User + Company (todas las companies iguales),
pertenencia usuario↔empresa, tres capas de módulos (catálogo global / elegibilidad /
activación por empresa) y endurecer menú + EnsureModuleIsActive. Convierte el catálogo
Admin ya existente en un SaaS usable en sesión multi-empresa.
Goals:
- Introducir Company (shell) con al menos name, slug, tradename, nif, status, logo
  (nullable); soft delete. Todas las empresas son el mismo modelo Company (como v1); un
  autónomo es User + Company, no un tercer tipo.
- Introducir user_companies (pertenencia); sin soft delete (como v1). Policy estricta: no
  hay acceso al ERP sin ≥1 empresa activa vinculada. Usuarios en `users` sin empresa
  pueden existir (contactos, leads…) pero no entran al ERP.
- Post-login: 1 empresa → sesión automática a esa; N empresas → modal bloqueante de
  selección obligatorio antes de usar el ERP.
- Super Admin: bypass de permisos/módulos/funcionalidades, pero los datos solo de las
  companies a las que está vinculado (sin excepción “ver todas” en este change).
- Tres capas de módulos:
  (1) Catálogo Module — SuperAdmin CRUD + Active/Inactive; Inactive ⇒ invisible y
  bloqueo total de sus Functionalities.
  (2) Activación por empresa — la empresa enciende/apaga módulos a los que tiene derecho;
  en sesión solo Functionalities de módulos encendidos (+ permisos Spatie encima).
  (3) Elegibilidad (autorizado sí/no) — Module Active ≠ la empresa puede activarlo;
  SuperAdmin marca autorización; pago/invitación reales fuera de alcance.
- `modules.level` determina accesibilidad y complementa la Capa 3: por defecto se
  muestran/autorizan todos los módulos Basic; Optional requiere autorización SuperAdmin
  (Capa 3). UI de selección tipo v1 (rejilla de tarjetas color/icono/check); gris si
  requiere condición y aún no está autorizado.
- Endurecer MenuBuilder y EnsureModuleIsActive con las capas anteriores (cerrar la
  excepción R-AUT-08 parcial del change Admin).
- Seed de trabajo: companies + vinculación del superadmin + módulos Basic autorizados /
  admin usable.
NonGoals:
- customer_providers / red comercial cliente↔proveedor.
- Roles Spatie por empresa (gestor/básico…) — change siguiente.
- Onboarding self-service, consola operador Platform, facturación/pago/invitación reales
  de módulos (solo flag autorizado sí/no).
- Engordar Company al maestro completo v1 (ccc, UTE, etc.) más allá de los campos del
  Goal.
- NIF histórico, obras, contactos como dominio.
Skills:
- laravel-patterns
- laravel-specialist
- frontend-design
- tailwind-css-patterns
Notes:
- Smoke post-parón (2026-10-09): Pest 37 passed, Pint y PHPStan verdes (PHP 8.3.30
  Laragon).
- Change Admin archivado: Module/Functionality, Spatie Teams, MenuBuilder solo por
  permisos, EnsureModuleIsActive stub; excepción R-AUT-08 sin CompanyModules a cerrar
  aquí.
- Soft delete: companies y company_modules sí (R-BD-03 + v1); user_companies no (como v1).
- Referencia UI módulos empresa: pantalla v1 “Mi cuenta → Módulos de empresa” (tarjetas).
- Modelo mental = v1: Company + UserCompanies + CompanyModules; CustomerProviders es la
  relación comercial (fuera de este change). Nombre de carpeta/dominio del fichero
  Company.php: no se cerró en explore (evitar confusión con el label “Partners” del
  module-map); 10-architecture decide ubicación respetando “una sola Company, todas
  iguales” y actualiza module-map si hace falta.
- AdminOnly (`ModuleLevel`): explore cerró que level determina accesibilidad y que Basic
  se autoriza/muestra por defecto; Optional → Capa 3. El matiz fino de AdminOnly (¿como
  Optional o solo ciertas companies?) queda para 10-architecture con opciones A/B si
  hace falta, sin bloquear el propose.
- Elegibilidad Capa 3 en este change: solo SuperAdmin asigna autorizado sí/no.


## ADDED Requirements

### Requirement: Company shell y pertenencia usuario↔empresa
El sistema SHALL mantener empresas (`Company`) como único tipo de actor organizativo,
con pertenencia N:M usuario↔empresa, y SHALL impedir el acceso al ERP sin al menos una
vinculación a empresa activa.

#### Scenario: Login con una sola empresa
- **WHEN** un usuario con acceso al ERP está vinculado a exactamente una empresa activa
- **THEN** la sesión de trabajo se establece automáticamente en esa empresa

#### Scenario: Login con varias empresas
- **WHEN** un usuario con acceso al ERP está vinculado a más de una empresa activa
- **THEN** debe elegirse una empresa en un modal bloqueante antes de usar el ERP

#### Scenario: Usuario sin empresa
- **WHEN** un usuario existe en `users` pero no está vinculado a ninguna empresa
- **THEN** no puede acceder al ERP (puede existir como contacto/lead fuera del acceso)

#### Scenario: Super Admin y datos por empresa
- **WHEN** un Super Admin vinculado solo a un subconjunto de companies opera en el ERP
- **THEN** bypasa permisos/módulos/funcionalidades pero solo ve datos de las companies a
  las que está vinculado

---

### Requirement: Tres capas de módulos (catálogo, elegibilidad, activación)
El sistema SHALL aplicar (1) estado global del Module, (2) elegibilidad por empresa
(autorizado sí/no; Basic autorizados por defecto), y (3) activación por empresa, de modo
que el menú y los accesos solo permitan Functionalities de módulos globales Active,
autorizados y encendidos para la empresa de la sesión.

#### Scenario: Module global Inactive
- **WHEN** un Module está Inactive en el catálogo
- **THEN** no se muestra a nadie y se bloquea el acceso a todas sus Functionalities

#### Scenario: Module Active no autorizado para la empresa
- **WHEN** un Module está Active pero la empresa de la sesión no está autorizada
- **THEN** aparece en la UI de selección en estado no activable (gris / equivalente) y no
  puede encenderse para esa empresa

#### Scenario: Module Basic por defecto
- **WHEN** existe una company con cuenta activa y un Module de level Basic en estado
  Active
- **THEN** ese módulo queda autorizado por defecto para esa empresa (visible para
  activar/encender según Capa 2)

#### Scenario: Activación por empresa
- **WHEN** la empresa tiene un módulo autorizado y lo tiene encendido en
  company_modules
- **THEN** los usuarios en esa sesión pueden alcanzar sus Functionalities sujetos a
  permisos Spatie; si está apagado, no (con independencia de permisos)

#### Scenario: UI de módulos de empresa
- **WHEN** un usuario autorizado gestiona los módulos de su empresa
- **THEN** ve una rejilla de tarjetas (color/icono/selección) similar a v1, con los no
  autorizados en gris u estado equivalente

---

### Requirement: Menú y EnsureModuleIsActive alineados a las tres capas
El sistema SHALL construir el menú y aplicar EnsureModuleIsActive usando empresa de
sesión + elegibilidad + activación + permisos, cerrando la excepción parcial de R-AUT-08
del change Admin.

#### Scenario: Menú respeta módulos encendidos
- **WHEN** un usuario tiene permisos sobre una Functionality cuyo Module no está
  encendido para la empresa de la sesión
- **THEN** esa Functionality no aparece en el menú y el acceso por ruta queda bloqueado

---

## MODIFIED Requirements

### Requirement: Visibilidad de menú corregida
Respecto al spec de `menu-visibility` del change Admin: se reintroduce el filtro por
módulos posibles/activos para la empresa de sesión (además del comodín OR permiso de
Functionality), según las tres capas acordadas en este brief.

#### Scenario: Módulo visible solo si elegible y encendido
- **WHEN** se construye el menú para la empresa de la sesión
- **THEN** un Module solo aparece si está Active globalmente, la empresa está autorizada,
  lo tiene encendido, y el usuario cumple la regla de permisos ya definida (comodín OR
  permiso de Functionality)
