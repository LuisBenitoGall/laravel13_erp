# Tasks

## 1. Docs + datos

- [x] 1.1 Actualizar glosario (Content kinds Help/Article), ficha Admin
      Create (sustituir texto contents-help), module-map. Verificar docs.
- [x] 1.2 Migración `contents` (D1) + enums + modelo Spatie + factory.
      Verificar `migrate`.
- [x] 1.3 Seed Functionality `contents` + permisos. Verificar menú SA.

## 2. Backend

- [x] 2.1 Action Store (name+kind; Inactive; code unique; company_id
      null) + FormRequest. Verificar Feature alta → Inactive.
- [x] 2.2 Index table + Destroy soft-delete + Policy + rutas (index/
      create/store/edit GET/destroy). Verificar Features authz/delete.
- [x] 2.3 HelpContentMap + cache + shared `help_tips`; invalidar en
      Store/Destroy. Verificar Feature tip vacío sin error; sin JSON
      runtime.

## 3. Frontend

- [x] 3.1 Index ServerTable + Create (name + kind) Continuar.
      Verificar UI.
- [x] 3.2 Edit placeholder (name/code/kind + mensaje; Volver Index).
      Verificar redirect post-store.
- [x] 3.3 `<HelpTip code />` + i18n es/en/ca + Pint + PHPStan + Features
      verdes.

## 4. Correcciones 25 + UX Tipo radio

- [x] 4.1 Eliminar Update/Toggle Actions+Requests huérfanos.
- [x] 4.2 `Form/RadioGroup` (+ `ui/radio-group`) y Create Tipo = radios.
- [x] 4.3 `frontend.md` + Feature assert RadioGroup / no Select.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → **25-architecture-review** → 30 archive.
- Change siguiente: Edit real por kind.
- E2E 40: no aplica.
