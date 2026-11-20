# Design

## Context

Brief `docs/prompts/c2026-11-20-admin-gallery.md`. Contenidos globales ya viven
en Configuración; `UserImage` es ficha User (otro bounded context). v1
`FilesGallery`/`File_` = referencia funcional (title/alt, company), no stack.

E2E Pest browser (§4 e2e.md): **no aplica** — coverage Feature HTTP + policy.

## Goals / Non-Goals

**Goals:** Fase 1 librería + URL + grid 6 cols + tenancy nullable + menú.

**Non-Goals:** 1b crop; 2–3 picker; vídeo server; upload empresa; parent_id;
thumbs multi; Livewire/v1 clone.

## Decisions

### D1 — Naming

- Entidad **`MediaAsset`**, tabla `media_assets`, Functionality slug **`gallery`**,
  UI «Galería». ≠ `UserImage` / `Content`.
- Rutas: `admin.gallery.*` → `/admin/gallery`.

### D2 — Tenancy sin `BelongsToCompany` obligatorio

- Como `Content`: `company_id` **nullable**, **sin** trait `BelongsToCompany`
  (el trait fuerza company siempre).
- Fase 1 creates: **siempre** `company_id = null` (SA / Configuración).
- Listado / policy:
  - `null` visible si SA **o** `gallery.view` (viewAny).
  - filas con `company_id` set: solo si el actor tiene esa company en
    perímetro (vinculadas); SA no ve ajenas.
- Upload empresa: Action/Request **rechazan** `company_id` no-null en Fase 1
  (422/403); UI sin selector de empresa.
- R-TEN-05: factory crea assets company A/B; actor A no update/delete/list B.

### D3 — Storage y límites

- Disk **`public`**. Path: `gallery/{uuid}.{ext}` (plataforma). Futuro empresa:
  `gallery/companies/{company_id}/{uuid}.{ext}` (no usado en Fase 1).
- Config `config/gallery.php` (espejo `user_images`):
  - mimes: `jpg,jpeg,png,gif,webp`
  - `max_kilobytes`: **2048**
  - sin tope de cantidad en Fase 1 (revisable).
- URL: `Storage::disk('public')->url($path)` expuesta al front para copiar.
- SoftDeletes: sí; borrar fichero del disco en delete (como UserImage) o al
  forceDelete — **elegido:** borrar archivo en soft-delete del activo para no
  dejar huérfanos públicos (documentar; si restore futuro hace falta, 1b+).

### D4 — UI

- Index único (no Create página aparte): dropzone + grid **6 columnas**
  (`lg:grid-cols-6`), card con thumb, name, acciones (copiar URL, editar
  name/alt modal/panel, eliminar confirm).
- **Excepción ServerTable** (como company-directory): mediática ≠ listado
  tabular; declarar en ficha Admin + frontend exception note en design.
- `react-dropzone`: añadir uso real; ya listado en libraries «por módulo» —
  task de canon en el mismo PR.

### D5 — Authz / menú

- Module existente `configuracion` (AdminOnly). V-09 N/A (no módulo nuevo).
- Functionality `gallery` + Spatie `gallery.view|viewAny|create|update|delete`.
- Policy: viewAny/create/update/delete; create solo si puede crear plataforma
  (Fase 1: de facto SA vía Gate::before + menú Configuración; test con user
  con `gallery.create` pero sin ser SA: create null OK si tiene permiso;
  create con company_id → blocked).
- RealignMenuCatalog: `ensureGallery` + insertar `gallery` **justo después**
  de `contents` en `FUNCTIONALITY_MAP`.
- `MenuFunctionalityHref` + label i18n `galeria`.

### D6 — Crop (solo nota para 1b)

- Explore: crop cliente. Fase 1 **no** implementa; no añadir Intervention aún.

### D7 — Vinculaciones V-01..V-21

| V | ¿Sí? | Notas |
|---|---|---|
| V-07 | Sí | `gallery.*` |
| V-08 | Sí | menú Configuración |
| V-09 | No | Module `configuracion` existente |
| V-12 | No | name/alt un locale (string) |
| V-15 | No | no migrar File_ |
| V-20 | No | sin activity log en Fase 1 |
| V-21 | Sí | rutas bajo configuracion; test capas+permiso |
| resto | No | |

## Risks / Trade-offs

- [Soft-delete borra fichero] → Mitigation: irreversible restore sin archivo;
  aceptable Fase 1; alternativa forceDelete-only si Luis objeta.
- [Permiso gallery a no-SA sin menú Configuración] → Mitigation: menú sigue
  AdminOnly module; otra URL futura reutiliza grid.
- [Confusión UserImage] → Mitigation: glosario + dominio Admin `MediaAsset`.

## Migration Plan

- Migración `media_assets` + realign menú + seed permisos vía CreateFunctionality.
- `php artisan storage:link` ya esperado en local.
- Rollback: revert code; drop table.

## Excepciones a standards

- **Listado sin ServerTable**: grid 6 cols mediática (análogo directorio).
- **Sin BelongsToCompany**: `company_id` nullable plataforma (análogo Content).

## Open Questions

_(ninguna bloqueante)_
