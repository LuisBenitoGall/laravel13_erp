Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-13-admin-sectors-catalog
Origen: informe 25-architecture-review (**RETURN TO IMPLEMENTATION**).
Hay un **HIGH** bloqueante + LOWs. No reabran diseño ni inventen Brand/UTE.
Contrato: `openspec/changes/c2026-11-13-admin-sectors-catalog/`
(proposal, design, specs `admin-sectors-catalog` / `company-edit`).
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni los deltas de specs.
No archives. E2E N/A.

## Contexto

Catálogo Sector + CRUD Configuración + sync N:N en Edit Company están
mayormente bien (Feature verde). El fallo: si un sector **ya pivoteado**
pasa a Inactive, el Edit sigue metiendo ese id en `sector_ids` y
`SyncCompanySectorsRequest` exige Active → Guardar sectores → 422.
Eso contradice design Risks: *Inactive no seleccionables; pivotes pueden
quedar hasta el próximo sync; al guardar sync reemplaza el set*.

## Qué hacer

### 1. HIGH — Sync con pivotes Inactive (obligatorio)

Decisión de diseño a respetar (sin A/B):

- Selector / opciones: **solo Active**.
- Al **guardar** sync: el pivot queda **exactamente** el conjunto enviado
  (Active); los Inactive previamente pivoteados **salen** en ese sync.
- Mientras el usuario no guarde, pueden permanecer en BD (aceptado).

Corrección mínima preferida (elige esta vía; no inventes merge parcial):

1. En `CompanyController::edit`, la prop `sectors` (ids/nombres iniciales
   del panel) MUST incluir **solo** sectores pivoteados con status
   **Active** (mismo criterio que `sector_options`).
2. `CompanySectorsPanel` sigue inicializando `sector_ids` desde `sectors`
   (ya solo Active) → Guardar no envía Inactive → validación OK →
   `SyncCompanySectors` reemplaza el set y limpia pivotes Inactive.
3. Mantén `SyncCompanySectorsRequest`: `sector_ids.*` solo Active + no
   soft-deleted. Mantén Action filtrando Active (defensa).
4. **No** muestres Inactive en el multi-select. **No** exijas limpiar el
   pivot al hacer toggle Inactive en el catálogo (sigue diferido al sync).

Opcional UX (solo si es trivial, sin nuevo tab): nota i18n breve de que
sectores desactivados dejarán de atribuirse al guardar — **no** obligatorio
si no hay clave ya prevista; no inventes copy largo.

### 2. LOW — Feature del edge Inactive pivoteado

En `tests/Feature/Admin/CompanySectorsSyncTest.php` (o ampliación mínima):

- Company vinculada a sector Active; toggle/poner Inactive; pivot sigue.
- GET Edit: `sectors` **no** incluye ese id Inactive; `sector_options` tampoco.
- PUT sync con solo Active (p. ej. `[]` u otro Active) → 302/redirect.
- Tras sync: pivot ya **no** contiene el Inactive.

Cubre también (si aún no está explícito) que enviar Inactive en
`sector_ids` sigue fallando validación.

### 3. LOW — `module-map.md`

Añadir `Sector` a la lista de entidades del dominio **Admin** en
`docs/standards/module-map.md` (misma fila/tabla donde están Company,
Bank, Unit, etc.). Una mención; sin rediseñar el mapa.

### 4. LOW — Orden de `sector_options` (opcional si barato)

Hoy `orderBy('slug')` en Edit. Preferible ordenar por **name del locale
activo** (mismo espíritu Active-first+name del Index), sin N+1 absurdo.
Si el patrón de otro catálogo (Units en Edit company) ya ordena por slug,
puedes dejar slug y documentar en el PR/respuesta que se alinea al hermano
— **no bloquea** el HIGH.

## Verificación

```text
php artisan test --compact tests/Feature/Admin/SectorTest.php tests/Feature/Admin/CompanySectorsSyncTest.php
```

Pint dirty PHP (`vendor/bin/pint --dirty --format agent`).

## Fuera de alcance

- Brand / UTE / jerarquía Company / centros de coste.
- Cambiar semántica del CRUD Sectores (Index, LangTabs, Active-first).
- Auto-detach al toggle Inactive en el catálogo.
- Reescribir design/specs.
- Archive / 30-testing / E2E.

## Gate de salida

- [ ] Edit `sectors` / form solo Active; Guardar con pivote Inactive previo
      no 422 y sync reemplaza (limpia Inactive).
- [ ] Feature del edge Inactive pivoteado en verde.
- [ ] `Sector` aparece en `module-map.md` (Admin).
- [ ] `SectorTest` + `CompanySectorsSyncTest` verdes; Pint OK.
- [ ] Volver a **25-architecture-review** (no archivar aquí).
