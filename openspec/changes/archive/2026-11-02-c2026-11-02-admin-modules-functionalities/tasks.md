# Tasks

> Scope: CRUD Functionalities anidado en Module Edit.
> Menú genérico / tab Módulo / E2E §4 / R-TEN-05 nuevo: N/A.

## 1. Backend — store / update / status / table nested

- [x] 1.1 Añadir rutas nested (table + store bajo `modules/{module}`) y endpoints
  update/status/destroy de Functionality según design D1. Verificar:
  `php artisan route:list --path=functionalities` muestra las rutas.
- [x] 1.2 Ajustar `StoreFunctionalityRequest` (+ CreateAction si hace falta): slug auto
  desde name, name/slug unique CI, label principal required, status default Active,
  `module_id` del route. Verificar: tests 1.5.
- [x] 1.3 Ajustar update (request/action) para modal: solo `label`; slug/name
  inmutables. Verificar: test update no cambia name/slug.
- [x] 1.4 Toggle status JSON (patch) + destroy soft-delete vía `DeleteFunctionality`
  sin borrar permisos Spatie. Verificar: tests status + delete; permisos siguen en BD.
- [x] 1.5 Feature tests: table filtrado por módulo; store OK; name CI duplicate; update
  label; toggle; delete soft + permisos intactos; 403 sin permiso. Verificar:
  `php artisan test --compact` filtrado Functionality/Module functionalities verde.
- [x] 1.6 Pint PHP: `vendor/bin/pint --dirty --format agent`. Verificar: exit 0.

## 2. Frontend — Module Edit pestaña Funcionalidades

- [x] 2.1 Sustituir placeholder por `ServerTable` (label, name, Acciones; sin slug;
  endpoint nested; exportable false si aplica). Verificar: columnas correctas en UI.
- [x] 2.2 rowActions: toggle → Edit (abre modal) → Delete confirm. Verificar: orden y
  comportamientos sin F5 en toggle.
- [x] 2.3 ActionBar: botón Nueva funcionalidad abre modal Create (name, LangTabs label,
  status default; sin slug). Tras éxito: cierra + refresca tabla. Verificar: alta
  visible en tabla.
- [x] 2.4 Modal Edit: solo LangTabs label. Verificar: name/slug no editables en UI.
- [x] 2.5 i18n claves nuevas (es/en/ca, orden alfabético). Verificar: claves en los
  tres JSON.

## 3. Cierre

- [x] 3.1 Smoke: Module Edit → Nueva → toggle → Edit label → Delete; permisos Spatie
  del slug siguen. Verificar: manual o tests 1.5.
- [x] 3.2 Pint final si hubo más PHP. Verificar: exit 0.

## 4. Correcciones 25 — locale principal

- [x] 4.1 Shared Inertia `defaultLocale` + Create/Edit Modules sin `locale === 'es'`
  (required vía `defaultLocale`). Verificar: tests fuente Create/Edit + LocalePreference
  + ModuleFunctionalities verdes; pint si hubo PHP.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → (E2E N/A) → 30 / archive.
