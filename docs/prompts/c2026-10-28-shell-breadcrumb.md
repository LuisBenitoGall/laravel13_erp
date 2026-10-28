<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-28-shell-breadcrumb
Rule: 10-architecture
Summary: Añadir el bloque breadcrumb bajo el topbar (option + suboption vía i18n), con
    option clicable al index de la funcionalidad y suboption texto; migrar las páginas
    AppLayout actuales. Sin ActionBar.
Goals:
- Bloque bajo el topbar (breadcrumb) con dos fragmentos: (1) título alineado al punto de
  menú que da acceso (option), (2) funcionalidad secundaria anidada (suboption: create,
  edit, dashboard de listado, etc.), estructura como en la referencia v1 / captura.
- Contrato tipo A: la página/controller aporta claves i18n (`optionKey`, `suboptionKey`)
  — textos siempre vía i18n, cero literales.
- En pantallas Index, suboption = Dashboard (clave i18n `dashboard` existente).
- Clickeable C1: solo el título grande (option) enlaza al index de esa funcionalidad;
  suboption es texto plano.
- No incluir `module`/`slug` del `$header` v1 en el breadcrumb (aside activo por URL).
- Adaptar ya todas las páginas que usan `AppLayout`.
- Estética coherente con shell v2; estructura fiel al ejemplo (option grande + suboption
  más pequeña al lado).
NonGoals:
- ActionBar / botones de acciones de vista (bloque posterior).
- Completar funcionalidad real de topbar stubs (notificaciones, notas, chat, favoritos).
- Portar `module`/`slug` del header v1 para marcar aside.
- Inventar maestros o pantallas de negocio nuevas.
Skills:
- frontend-design
- laravel-specialist
Notes:
- Topbar bloque 1 ya existe (polish mini-aside puede ir en paralelo o ya aplicado); este
  change es el bloque 2 breadcrumb únicamente.
- v1: `$header = ['option' => …, 'suboption' => 'textos.…', 'module' => …, 'slug' => …]`;
  en blade, option → `textos.{option}`, suboption → clave de traducción completa; module/
  slug servían al aside.
- Hoy `AppLayout` recibe `title` solo para `<Head>`; las páginas inventan subtítulos
  sueltos en children — unificar en el breadcrumb.
- Referencia visual: captura “Gestión de ventas” + “Editar venta” bajo el topbar.
- Index → suboption Dashboard acordado explícitamente.


## ADDED Requirements

### Requirement: Bloque breadcrumb option + suboption
El sistema SHALL mostrar, bajo el topbar del AppLayout, un bloque breadcrumb con el
título principal (`option`) y el secundario (`suboption`), resueltos siempre por claves
i18n. SHALL aceptar un contrato de claves i18n (p. ej. `optionKey`, `suboptionKey`) desde
la página. En pantallas Index, `suboptionKey` SHALL ser la clave `dashboard`. El título
principal SHALL ser un enlace al index de la funcionalidad correspondiente; el secundario
SHALL ser texto no enlazado. SHALL NOT incluir campos `module`/`slug` del header v1 en
este bloque. SHALL NOT incluir ActionBar en este change.

#### Scenario: Breadcrumb create/edit
- **WHEN** una página Create/Edit declara optionKey y suboptionKey (p. ej. editar)
- **THEN** bajo el topbar se muestra t(optionKey) como enlace al index y t(suboptionKey)
  como texto secundario

#### Scenario: Breadcrumb index con Dashboard
- **WHEN** una página Index declara optionKey y suboptionKey `dashboard`
- **THEN** se muestra el título de la funcionalidad y el secundario “Dashboard” (i18n)

#### Scenario: Sin ActionBar
- **WHEN** se renderiza el bloque breadcrumb
- **THEN** no aparece la barra de acciones de vista (bloque posterior)

---

### Requirement: Migración de páginas AppLayout existentes
El sistema SHALL adaptar las páginas que ya usan AppLayout para declarar el contrato del
breadcrumb (option/suboption i18n + destino del enlace option) en lugar de depender solo
del title en el header o subtítulos ad hoc incoherentes con el bloque.

#### Scenario: Páginas actuales con breadcrumb
- **WHEN** el usuario abre una pantalla ERP existente que usa AppLayout (Admin, Partners,
  Dashboard, Profile, etc.)
- **THEN** ve el bloque breadcrumb según el contrato, con textos i18n


## MODIFIED Requirements

### Requirement: (AppLayout / shell — título de vista en bloque breadcrumb)
El layout ERP SHALL renderizar la cabecera de contexto de vista (option + suboption) en el
bloque breadcrumb bajo el topbar, no en el chrome del bloque 1. El prop/document title
puede seguir existiendo para `<Head>` si 10 lo delimita; el ActionBar queda fuera.
