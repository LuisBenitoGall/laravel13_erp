<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-29-menu-core-pillars
Rule: 10-architecture
Summary: Reorganizar el menú núcleo (Empresas / Usuarios / Configuración SA) con labels
    acordados; sin pantallas nuevas. Permisos como ítem propio enlazando temporalmente a
    la matriz dentro de Roles (opción C). Mis empresas = solo label sobre perímetro
    vinculado.
Goals:
- No generar el menú completo del borrador MENU_ERP; solo pilares prioritarios.
- Menú / agrupación:
  - Empresas → Mis empresas (empresas vinculadas al usuario; mismo listado/arquitectura;
    solo cambiar label).
  - Usuarios → Usuarios empresa (entrada de menú; sin pantalla nueva por ahora).
  - Configuración (nivel 3 / AdminOnly, **solo Super Admin, sin excepción**) → Módulos,
    Roles, Permisos.
- Permisos: ítem de menú propio. Los permisos se generan desde funcionalidades con las 7
  acciones ya descritas; a futuro hará falta dashboard + CRUD para permisos al margen de
  los previstos. **Por ahora sin pantallas nuevas**: el ítem Permisos enlaza de forma
  temporal a la matriz de permisos dentro de Roles (opción C), hasta el CRUD propio.
- Prioridad de producto recordada: usuario apto, alguna empresa, y estructura/métodos
  para módulos, roles y permisos (gran parte ya existe en Fase 0; este change orienta
  menú/labels/navegación, no reinventar tenancy).
NonGoals:
- Sembrar o implementar Catálogo, Stocks, Pedidos, RRHH, Contabilidad, etc. del PDF.
- Pantallas nuevas (CRUD Permisos propio, listado Usuarios empresa nuevo, etc.).
- Cambiar el perímetro de companies (sigue siendo solo vinculadas).
- Abrir Configuración AdminOnly a no–Super Admin.
Skills:
- tenancy-rbac
- frontend-design
- laravel-specialist
Notes:
- Borrador de menú: MENU_ERP.pdf / Doc Drive (visión viva; sufrirá cambios).
- Leyenda PDF 1/2/3 ≈ Basic / Optional / AdminOnly; Configuración = 3 → SA only.
- Seed actual: SA + ACME; módulo admin (modules, functionalities, companies, …, roles);
  workspace (company-roles). Consola plataforma / members ya existen en distinto grado.
- Destinos temporales (explore + propose): Permisos → UI Roles (C); Usuarios empresa →
  Members de la company de sesión (decisión 10 en design del change, mismo espíritu C).
- 10 delimita Functionalities/labels/MenuBuilder y filtro del bloque Configuración SA.


## ADDED Requirements

### Requirement: Pilares de menú Empresas, Usuarios y Configuración SA
El sistema SHALL exponer en el menú (agrupación/labels) los pilares Empresas (con Mis
empresas), Usuarios (con Usuarios empresa) y Configuración solo para Super Admin, con
subítems Módulos, Roles y Permisos. Mis empresas SHALL seguir listando solo companies
vinculadas al usuario; el cambio es de label, no de arquitectura de perímetro.
Configuración y sus subítems de nivel AdminOnly SHALL ser visibles/accesibles solo para
Super Admin.

#### Scenario: Mis empresas label
- **WHEN** un usuario con companies vinculadas ve el menú
- **THEN** la entrada equivalente al listado de empresas del perímetro se etiqueta como
  Mis empresas (o agrupación Empresas → Mis empresas según design)

#### Scenario: Configuración solo SA
- **WHEN** un usuario no Super Admin obtiene el menú
- **THEN** no ve el bloque Configuración de nivel 3 / AdminOnly

#### Scenario: SA ve Configuración
- **WHEN** un Super Admin obtiene el menú
- **THEN** ve Configuración con Módulos, Roles y Permisos

---

### Requirement: Ítem Permisos temporal hacia Roles
El sistema SHALL incluir un ítem de menú Permisos distinto de Roles. Mientras no exista
el CRUD/dashboard propio de permisos, ese ítem SHALL enlazar a la matriz de permisos de
la UI de Roles existente (opción C). SHALL NOT implementar en este change el CRUD de
permisos custom al margen de funcionalidades×7 acciones.

#### Scenario: Permisos abre matriz de Roles
- **WHEN** Super Admin abre Permisos desde el menú
- **THEN** navega a la experiencia actual de matriz de permisos asociada a Roles (sin
  pantalla nueva de permisos)


## MODIFIED Requirements

### Requirement: (menu-visibility / shell-navigation — labels y agrupación núcleo)
Ajustar labels/agrupación del menú data-driven para pilares Empresas / Usuarios /
Configuración SA sin ampliar pantallas. 10 escribe el delta sobre specs de menú
existentes.
