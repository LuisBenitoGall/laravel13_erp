<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-05-users-module-indexes
Rule: 10-architecture
Summary: Module Usuarios en menú con dos entradas (Usuarios empresa + Todos los
  usuarios); Index ServerTable para ambos según reglas de alcance y acciones
  acordadas. Sin formularios Create/Edit aún.
Goals:
- Submenú **Usuarios**: «Usuarios empresa» + «Todos los usuarios».
- **Todos los usuarios**: ServerTable (Tabulator como el resto). Acciones de
  fila **muy limitadas**: por ahora solo envío de notificaciones = **máscara**
  (UI). Nada de editar, activar ni eliminar en esta vista.
- Perímetro Todos: **cualquier usuario ve todos los users** (directorio global).
  Filtro de “perfiles públicos” = **luego**, no este change.
- **Usuarios empresa**: ServerTable de users que aparecen en `user_companies`
  vinculados a la **empresa en sesión**. Columna de acciones **estándar** por
  ahora: activar/desactivar, editar y eliminar. Botón **crear** nuevo usuario =
  **máscara** por ahora (funcionalidad de alta = luego).
- Menú: no hay carga por `.json` (cache BD); si hace falta coherencia de
  `sort` en Functionalities, invalidar/cachear catálogo — no regenerar JSON.
NonGoals:
- Formularios de creación y edición de usuario (explícito: van luego; no
  pedidos en el prompt).
- Roles de empresa bajo Empresas/ (Luis: irá ahí; **olvidar por ahora**).
- Filtro de perfiles públicos en Todos.
- Envío real de notificaciones (solo máscara).
- Alta real de usuario (solo máscara del botón crear).
Skills:
- openspec-propose
- tenancy-rbac (menú Module/Functionality, company de sesión)
Notes:
- Origen: `/opsx-propose` con pedido de dudas; menú Usuarios + dos dashboards.
- Hallazgo explore: menú data-driven (`MenuCatalogCache`), no JSON; hoy
  `company-users` apunta a Members de sesión; `company-roles` aún bajo
  `usuarios` (reubicación a Empresas = fuera de este change).
- Brief realign marcó «Todos» como NonGoal; este change **lo anula** para el
  directorio global (Luis).
- Pregunta explore sobre Functionality/quién ve Todos: Luis no la entendió;
  con respuesta 1 manda “cualquier usuario ve todos”. 10 cablea permisos/menú
  sin inventar restricciones extra.
- Create/Edit forms fuera; si la acción «editar» de Usuarios empresa requiere
  pantalla, 10 propone máscara/ruta stub coherente con NonGoal de formularios
  **o** pregunta — no implementar formularios completos.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Menú Usuarios con Usuarios empresa y Todos
El sistema SHALL exponer bajo el Module de Usuarios las entradas de menú
«Usuarios empresa» y «Todos los usuarios».

#### Scenario: Submenú Usuarios
- **WHEN** un usuario con acceso al Module Usuarios obtiene el menú
- **THEN** ve Usuarios empresa y Todos los usuarios (y no se exige en este
  change la entrada Roles de empresa bajo Usuarios ni bajo Empresas)

### Requirement: Index Todos los usuarios
El sistema SHALL listar todos los users en un ServerTable. Acciones de fila
SHALL limitarse por ahora a una máscara de envío de notificaciones. SHALL NOT
ofrecer editar, activar/desactivar ni eliminar en este Index.

#### Scenario: Listado global
- **WHEN** un usuario autenticado con acceso a la vista abre Todos los usuarios
- **THEN** ve el ServerTable con el conjunto global de users (sin filtro de
  perfil público aún)

#### Scenario: Solo máscara de notificación
- **WHEN** el usuario usa la acción de notificación en una fila
- **THEN** obtiene la máscara UI acordada (sin envío real obligatorio en este
  change)

### Requirement: Index Usuarios empresa
El sistema SHALL listar en ServerTable los users presentes en `user_companies`
para la company de sesión. SHALL mostrar acciones de fila activar/desactivar,
editar y eliminar, y un control Crear usuario como máscara (sin alta real en
este change).

#### Scenario: Filas = user_companies de sesión
- **WHEN** el usuario abre Usuarios empresa con company de sesión S
- **THEN** la tabla muestra solo users vinculados a S en `user_companies`

#### Scenario: Crear es máscara
- **WHEN** el usuario pulsa Crear usuario
- **THEN** no completa un alta real de usuario (máscara / diferido a change
  posterior)


## MODIFIED Requirements

<!-- Ninguno nombrado explícitamente; 10 enlaza capabilities (menú / users) al
     redactar el delta. Anula el NonGoal «Pantalla Todos» del realign para este
     alcance. -->
