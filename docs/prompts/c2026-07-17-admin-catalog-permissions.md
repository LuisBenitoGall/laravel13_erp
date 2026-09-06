Change: c2026-07-17-admin-catalog-permissions
Rule: 10-architecture
Summary: Construir el catálogo de administración del ERP — Module y Functionality (los
primeros catálogos globales sin tenancy, usando el nuevo flag --global del generador),
RBAC real con Spatie Permission (función Teams, rol Super Admin, provisión automática de
permisos por Functionality), la política de visibilidad de menú corregida respecto a v1,
el catálogo UI mínimo (AppLayout, menú data-driven, ServerTable) y un login mínimo propio
para poder trabajar con un usuario superadmin. Es la Capa 1+2 de la Fase 0, construida ya
con el generador real (change kernel-tooling-fase0, completado y verificado).
Goals:
- Extender el generador make:domain-entity con el flag --global (catálogos sin tenancy):
  omite company_id/BelongsToCompany en migration/model, exists/unique sin scoping de
  tenant en el FormRequest, sin belongsToCurrentCompany() en la Policy, sin company_id en
  la Factory, y sustituye el test de aislamiento de tenant por uno que confirma que el
  dato se ve igual desde cualquier empresa.
- Construir Module (Admin, --global): name, slug, label (traducible), color, icon, level
  (enum ModuleLevel: AdminOnly/Basic/Optional), status (enum ModuleStatus:
  Active/Inactive), explanation (traducible) — generado con
  make:domain-entity Admin Module --global --status --translatable.
- Construir Functionality (Admin, --global): module_id (FK a Module), name/slug
  (inmutable tras crearse), label (traducible), status (enum Active/Inactive) — generado
  con make:domain-entity Admin Functionality --global --status --translatable.
- Al crear cada Functionality, auto-provisionar sus 5 permisos
  (viewAny/view/create/update/delete) vía Permission::firstOrCreate (R-AUT-07) —
  mecanismo ya construido en el generador (kernel-tooling-fase0), aplicarlo aquí de
  verdad por primera vez contra entidades reales.
- Instalar y configurar Spatie Permission con la función Teams activada
  ('teams' => true, 'team_foreign_key' => 'company_id') — roles y permisos quedan con
  company_id nullable de forma nativa, sin necesitar una columna Universal como en v1.
- Sembrar el rol "Super Admin" (nomenclatura que reconoce Spatie) con company_id = NULL;
  bypass total vía Gate::before (R-AUT-03) — no necesita permisos explícitos en
  role_has_permissions.
- Sembrar un usuario superadmin de trabajo (credenciales configurables, no literales
  hardcodeadas en código) y asignarle el rol Super Admin en model_has_roles.
- Corregir la política de visibilidad de menú respecto a v1 (R-AUT-08): un Module se
  muestra si el usuario tiene su permiso comodín <module-slug>.access (que implica todas
  sus Functionalities) O al menos un permiso de alguna Functionality del módulo; una
  Functionality se muestra si el usuario tiene el comodín de su módulo O su propio
  permiso viewAny. Mecanismo único para los 3 niveles de Module (nunca los dos sistemas
  paralelos de v1, basic-menu.json/secondary-menu.json).
- Construir el catálogo UI mínimo (Capa 1): AppLayout (sidebar con Module/Functionality,
  coloreado por Module.color/icon, + topbar), adaptando patrones visuales de la plantilla
  de referencia shadcn-admin (frontend.md §2) — no clonarla como scaffold completo.
- Adoptar ServerTable (Tabulator) por primera vez de verdad: páginas Index de Module y de
  Functionality.
- Kit de formularios mínimo para las páginas Create/Edit de Module y Functionality.
- Menú construido en el servidor como prop de Inertia a partir de Module/Functionality +
  permisos del usuario (sustituye a los .json de storage/ de v1).
- Login/logout mínimo propio: guard de sesión estándar de Laravel, sin registro público
  ni recuperación de contraseña todavía.
NonGoals:
- CompanyModules: no existe todavía companies, así que el gate de plan SaaS
  (¿la empresa tiene el módulo activo?) queda fuera de la visibilidad de menú de este
  change — se endurece cuando exista companies/CompanyModules en un change posterior.
- Roles por empresa (gestor/basic/etc., patrón v1 company_id/rol): siguen esperando a que
  existan companies reales.
- role_has_permissions / model_has_permissions poblados de verdad: vacíos por ahora
  (Super Admin no los necesita).
- users/user_companies (relación N:M usuario↔empresas): fuera de alcance — es un diseño
  aparte, no una excepción --global, y solo importa cuando existan companies reales.
- Registro público, recuperación de contraseña, verificación de email: fuera de alcance
  del login mínimo de este change.
- Ninguna otra entidad de negocio fuera de Module/Functionality (Partners, Sales,
  etc.) — eso es de fases posteriores.
- Otros catálogos globales mencionados como ejemplo (currencies, countries, provinces,
  banks): no se construyen en este change, solo se deja listo el mecanismo (--global)
  que los hará triviales cuando les toque.
Skills:
- laravel-patterns
- laravel-specialist
- frontend-design
- tailwind-css-patterns
Notes:
- Change anterior (kernel-tooling-fase0) completado y verificado en los tres ejes:
  composer test (21 passed, 1 risky esperado — arch test vacuamente cierto sin entidades
  de dominio todavía), vendor/bin/pint --test (52 ficheros, verde), vendor/bin/phpstan
  analyse (23 ficheros, sin errores).
- El arch test "FormRequests viven en Http/Requests" y el comando module:audit solo se
  han probado contra el caso vacío hasta ahora — este change es la primera vez que se
  ejercitan de verdad contra entidades reales (Module, Functionality); vale la pena
  observar si de verdad atrapan una violación real, no solo si pasan en verde.
- Riesgo a verificar en implementación: el comportamiento exacto de hasRole()/Gate con la
  función Teams de Spatie activada cuando el team_id es NULL (rol Super Admin) — depende
  de la versión concreta del paquete, no asumir sin comprobar contra la documentación
  oficial de Spatie.
- Decisión de naming de permisos (ya en R-AUT-02/R-AUT-07): prefijo = slug de la
  Functionality, no del Module contenedor; el comodín de módulo usa notación con punto
  (<module-slug>.access), no el module_<slug> con guión bajo de v1.
- Decisión de mapeo de acciones (ya acordada): los 7 métodos de v1
  (index/create/show/search/edit/update/destroy) se colapsan en 5
  (viewAny/view/create/update/delete) — search se absorbe en viewAny, edit+update se
  funden en update.
- Plantilla de referencia de UI ya decidida y capturada en frontend.md §2: shadcn-admin
  (satnaing, MIT) — se adapta, no se clona el scaffold completo (fuera su TanStack Router
  y su integración Clerk).
- Branding por tenant (solo logo, vista + documentos) y color por módulo conservado ya
  capturados en frontend.md §2 — no hay tenant real todavía para ejercitarlo, pero el
  AppLayout debe dejar el hueco listo.


## ADDED Requirements

### Requirement: Generador con soporte de catálogos globales
El generador make:domain-entity SHALL admitir un flag --global que produzca entidades sin
tenancy (sin company_id, sin BelongsToCompany), reemplazando el test de aislamiento de
tenant por uno que confirme visibilidad compartida entre empresas.

#### Scenario: Entidad global generada sin tenancy
- **WHEN** se ejecuta make:domain-entity con --global sobre una entidad de prueba
- **THEN** el modelo generado no usa BelongsToCompany, la migración no lleva company_id,
  y el test generado confirma que el registro es visible igual desde cualquier empresa

---

### Requirement: Catálogo Module y Functionality
El sistema SHALL mantener un catálogo global de Module (unidad SaaS-activable) y
Functionality (unidad de permiso y menú dentro de un módulo), con slug de Functionality
inmutable tras crearse.

#### Scenario: Alta de Functionality provisiona sus permisos
- **WHEN** se crea una Functionality nueva bajo un Module
- **THEN** se provisionan automáticamente sus 5 permisos
  (viewAny/view/create/update/delete) con prefijo igual al slug de la Functionality

#### Scenario: Slug de Functionality inmutable
- **WHEN** se edita una Functionality existente
- **THEN** solo se puede modificar label, status y module_id — el slug no es editable

---

### Requirement: RBAC con Spatie Teams y Super Admin
El sistema SHALL usar Spatie Permission con la función Teams activada (company_id como
team key) y un rol Super Admin con company_id nulo que bypasa toda comprobación de
permiso.

#### Scenario: Super Admin bypasa cualquier permiso
- **WHEN** un usuario con el rol Super Admin accede a cualquier acción protegida por
  permiso
- **THEN** el acceso se concede sin comprobar permisos individuales (Gate::before)

#### Scenario: Rol Super Admin sin company_id
- **WHEN** se siembra el rol Super Admin
- **THEN** su company_id es NULL, sin necesitar ninguna columna adicional tipo
  "Universal"

---

### Requirement: Visibilidad de menú corregida
El sistema SHALL mostrar un Module en el menú si el usuario tiene su permiso comodín de
módulo o al menos un permiso de alguna de sus Functionalities, y SHALL mostrar cada
Functionality si el usuario tiene el comodín de su módulo o su propio permiso viewAny —
nunca exigiendo ambos a la vez.

#### Scenario: Módulo visible por permiso de Functionality sin comodín
- **WHEN** un usuario tiene el permiso viewAny de una Functionality pero no el comodín
  <module-slug>.access de su módulo
- **THEN** el módulo aparece en el menú con esa Functionality visible

#### Scenario: Comodín de módulo implica todas sus Functionalities
- **WHEN** un usuario tiene el permiso comodín <module-slug>.access
- **THEN** ve todas las Functionalities de ese módulo en el submenú, tenga o no permisos
  individuales sobre cada una

---

### Requirement: Catálogo UI mínimo y login
El sistema SHALL ofrecer un AppLayout con sidebar data-driven (Module/Functionality,
coloreado por módulo), páginas Index de Module y Functionality con ServerTable, y un login
mínimo con el guard de sesión estándar de Laravel.

#### Scenario: Login del superadmin
- **WHEN** el superadmin sembrado introduce sus credenciales en el login
- **THEN** accede a la aplicación y ve el AppLayout con el menú construido a partir de
  Module/Functionality

#### Scenario: Listado de Module con ServerTable
- **WHEN** el superadmin visita el índice de Module
- **THEN** ve un ServerTable (Tabulator) paginado server-side, conforme al contrato de
  frontend.md §3


## MODIFIED Requirements

No aplica — no hay capability previa relacionada con Admin/RBAC/menú que modificar (las
existentes, frontend-bootstrap y las cuatro de kernel-tooling-fase0, no se tocan).
