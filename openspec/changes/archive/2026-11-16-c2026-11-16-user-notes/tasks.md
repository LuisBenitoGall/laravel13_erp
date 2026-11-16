# Tasks

## 1. Datos + docs

- [x] 1.1 Migración `notes` (user_id, title, note; sin company_id/status).
      Verificar `migrate`.
- [x] 1.2 Modelo `Note` + factory + `MAX_PER_USER = 50`. Verificar
      factory.
- [x] 1.3 Glosario Nota interna / `Note` (≠ DeliveryNote) + Admin/shell.
      Verificar docs.

## 2. Backend

- [x] 2.1 Action Store (title/note required; tope 50) + FormRequest.
      Verificar Feature alta/tope.
- [x] 2.2 Action Destroy + Policy ownership. Verificar 403/404 ajeno.
- [x] 2.3 Rutas `admin.notes` store/destroy; Dashboard props `notes`;
      shared `notes_count`. Verificar Feature (incl. cambio company no
      filtra notas).

## 3. Frontend

- [x] 3.1 Sustituir `NotesStub`: modal Dialog alta + badge real + enlace
      Ver todas → dashboard. Verificar UI.
- [x] 3.2 Dashboard: sección post-its + delete (junto a favoritos).
      Verificar UI.
- [x] 3.3 i18n es/en/ca + Pint + PHPStan + Features verdes.

## 4. Correcciones 25 LOW (max note)

- [x] 4.1 `StoreNoteRequest`: `note` max:1000 + Textarea maxLength.
- [x] 4.2 Feature rechazo 1001 / alta ≤1000.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.

