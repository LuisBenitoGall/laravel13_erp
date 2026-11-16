<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-16-user-preferences-favorites
Rule: 10-architecture
Summary: Favoritos de vistas (modelo Preference): guardar desde el kebab
  del topbar el path actual (sin dominio) con un nombre; mostrar CTAs en
  el dashboard filtrados por user+company de sesión (máx. 20), con color
  de Module; eliminar desde el CTA.
Goals:
- Modelo `Preference` (referencia v1): `name`, `url` (path sin dominio),
  `user_id`, `company_id`, `module_id` (para color/icono del CTA).
- Topbar ⋮: popover/form «Mis opciones preferidas» → guardar vista
  actual con nombre.
- Dashboard: sección de favoritos con CTAs (enlace al path) coloreados
  según Module; borrado desde el CTA.
- Scope: solo favoritos de la company en sesión; tope 20 por
  (user, company).
- Sustituir el stub `FavoritesStub` / comportamiento stub de favoritos
  en shell-topbar.
NonGoals:
- Guardar URL con dominio/host.
- Favoritos globales solo por user (sin company).
- Notificaciones, notas, chat (siguen stub).
- Product favorites u otros “favoritos” de catálogo.
Skills:
- openspec-propose
- tenancy-rbac
- v1-reference
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore` + capturas v1 (popover topbar + CTAs dashboard).
- v1: `preferences` name/url/user_id/module_id; dashboard join modules
  color/icon. v2: añadir `company_id`; url = path (+ query si aplica).
- Decisión Luis: user+company; CTA color módulo; límite 20.
- Cómo resolver `module_id` desde la ruta `/admin/...`: design
  10-architecture (menú/Functionality vs heurística).
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Guardar Preference desde el topbar
El sistema SHALL permitir, desde el icono de tres puntos del topbar,
abrir un formulario para guardar la vista actual como Preference con un
`name` y la URL como path sin dominio, asociada al user y a la company
de sesión. SHALL NOT superar 20 Preferences por par user+company.

#### Scenario: Alta favorito
- **WHEN** el actor en una vista del ERP introduce un nombre y guarda
- **THEN** se crea un Preference con path actual, user_id y company_id
  de sesión

#### Scenario: Tope 20
- **WHEN** el actor ya tiene 20 favoritos en esa company y intenta otro
- **THEN** no se crea y se informa del límite

### Requirement: Dashboard CTAs de favoritos
El Dashboard SHALL listar las Preferences del user para la company de
sesión como CTAs enlazados al path guardado, con color (e icono si
aplica) del Module asociado, y permitir eliminar un favorito.

#### Scenario: Ver y abrir
- **WHEN** el actor abre el dashboard con favoritos guardados
- **THEN** ve CTAs con el nombre elegido que navegan al path guardado

#### Scenario: Eliminar
- **WHEN** el actor elimina un CTA de favorito
- **THEN** el Preference desaparece del dashboard

### Requirement: Favoritos por tenant de sesión
Las Preferences SHALL filtrarse siempre por `company_id` de la sesión
activa además de `user_id`.

#### Scenario: Cambio de empresa
- **WHEN** el actor cambia de company de sesión
- **THEN** el dashboard muestra solo los favoritos de esa company


## MODIFIED Requirements

### Requirement: Stubs de notificaciones, notas, chat y favoritos
(El topbar con stubs.) SHALL dejar de tratar favoritos como stub vacío:
el kebab vertical SHALL abrir el formulario real de alta de Preference.
Notificaciones, notas y chat SHALL permanecer stub en este change.

#### Scenario: Kebab favoritos real
- **WHEN** el usuario activa el icono de favoritos (tres puntos)
- **THEN** ve el formulario para nombrar y guardar la vista actual (no
  empty stub)
