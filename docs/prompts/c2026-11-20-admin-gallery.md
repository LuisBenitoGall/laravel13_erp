<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-20-admin-gallery
Rule: 10-architecture
Summary: Fase 1 de Galería bajo Configuración — librería de imágenes
  reutilizables (estilo WP light) con name, alt y copiar URL; grid 6
  columnas; `company_id` nullable.
Goals:
- Menú Configuración → Galería (debajo de Contenidos); Functionality
  propia; ruta bajo `/admin/...` de Configuración.
- Subir solo imágenes; metadatos nombre + alt; obtener/copiar URL
  pública para reutilizar en Contenidos u otros sitios.
- Index en layout grid de 6 columnas (excepción ServerTable; UX tipo
  mediática WP light).
- Modelo con `company_id` nullable: altas SA sin company (`null` =
  plataforma); visibles a Super Admin o a quien tenga permisos de la
  Functionality de galería.
- Upload con `company_id` de empresa: previsto en modelo, **bloqueado
  de momento** (Fase 1 = flujo Configuración / SA).
- Más adelante otra URL para otros usuarios, **mismo** componente grid.
NonGoals:
- Fase 1b: crop / redimensión / «crear copia» (activo nuevo; sin
  `parent_id` cuando llegue).
- Fase 2–3: insertar desde Wysiwyg / `<MediaPicker>` genérico.
- Thumbs automáticos multi-medida estilo WordPress.
- `parent_id` / linaje entre original y copia.
- Vídeos en servidor (solo URL remota, como en Contenidos).
- Desbloquear upload no-SA / otra URL (mismo componente, change futuro).
- Migrar `File_` / FilesGallery de v1 tal cual (referencia solo lectura).
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- v1-reference
Notes:
- Origen: `/opsx-explore`. Fases acordadas: 1 librería+URL → 1b crop
  (cliente preferido por explore por eficiencia; 10 confirma) → 2
  Wysiwyg → 3 picker genérico. Este brief = **solo Fase 1**.
- v1: Livewire `FilesGallery` + `File_` (title/alt, por company) —
  no clonar stack.
- Contenidos v2 ya anotan galería como futuro; vídeo Content = URL.
- Naming entidad: explore usó candidata `MediaAsset` / Functionality
  `gallery`; 10 fija glosario + ficha Admin + menú.
- Disk, límites de tamaño/mime, soft-delete, policy exacta: no fijados
  en debate — decide 10 sin inventar negocio contradictorio; perímetro
  SA (no companies ajenas) manda.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.
- Tras brief: usuario lanza 10-architecture en sesión nueva (explore
  no invoca).


## ADDED Requirements

### Requirement: Galería en Configuración (Fase 1)
El sistema SHALL exponer una Functionality de Galería bajo Configuración
(junto/debajo de Contenidos) con pantalla Index en grid de 6 columnas
para gestionar imágenes reutilizables (nombre, alt, URL).

#### Scenario: SA abre Galería
- **WHEN** un Super Admin con acceso a Configuración/Galería entra a
  la ruta de Galería
- **THEN** ve el grid de 6 columnas de activos de imagen

### Requirement: Alta de imagen plataforma
El sistema SHALL permitir a Super Admin subir imágenes con
`company_id` null, persistiendo nombre (editable) y alt, y SHALL
exponer una URL reutilizable (copiable) del fichero almacenado.
SHALL aceptar solo imágenes (no vídeo en servidor).

#### Scenario: Subida SA
- **WHEN** el Super Admin sube una imagen válida y completa nombre/alt
- **THEN** se crea el activo con `company_id` null y puede copiar su URL

### Requirement: Visibilidad de activos globales
Los activos con `company_id` null SHALL ser visibles para Super Admin
o para usuarios con permisos de la Functionality de Galería.

#### Scenario: Actor con permiso gallery
- **WHEN** un actor autorizado a la Functionality de Galería lista
  activos
- **THEN** puede ver los de `company_id` null (además del perímetro
  que 10 defina para no-null cuando existan)

### Requirement: Upload por empresa bloqueado en Fase 1
El modelo SHALL admitir `company_id` de empresa, pero en Fase 1 el
sistema SHALL NOT permitir el alta no-SA / vinculada a empresa
(queda bloqueado; desbloqueo = change futuro con otra URL y mismo
componente).

#### Scenario: Intento upload empresa en Fase 1
- **WHEN** en Fase 1 se intenta el flujo de alta con company de
  empresa (no-SA)
- **THEN** no está disponible / queda bloqueado


## MODIFIED Requirements

_(ninguno — capability nueva; menú/ficha Admin los define 10 en la
propuesta)_
