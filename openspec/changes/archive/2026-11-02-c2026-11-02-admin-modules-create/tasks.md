# Tasks

> Scope: LangTabs/ColorPicker/IconPicker en form-kit + Create Module.
> Edit/Delete/Index: fuera. E2E §4: N/A. R-TEN-05 nuevo: N/A (Module global).
> V-01..V-20 entidad nueva: N/A (ficha Admin ya existe).

## 1. Catálogo de iconos ERP

- [x] 1.1 Ampliar el mapa Lucide curado (`menuIcons.ts` o `erpIcons.ts`) con un set
  ERP-oriented exportable (`ERP_ICON_NAMES` + `resolveMenuIcon`); sin emoticonos.
  Verificar: TypeScript compila; resolver un icono del set y uno desconocido
  (fallback).

## 2. Form-kit — controles nuevos

- [x] 2.1 Implementar `Form/LangTabs` (tabs desde `supportedLocales`, children por
  locale, i18n de etiquetas de tab). Verificar: montaje en Story/página de prueba o
  Create; N tabs = N locales.
- [x] 2.2 Implementar `Form/ColorPicker` (input color nativo + Field; sin dep nueva).
  Verificar: cambia valor hex/CSS usable.
- [x] 2.3 Implementar `Form/IconPicker` (Dialog + grid del catálogo 1.1; valor =
  nombre Lucide). Verificar: seleccionar icono actualiza el valor del form.
- [x] 2.4 Exportar los tres desde `Components/Form/index.ts` y actualizar
  `docs/standards/frontend.md` (LangTabs ya no pendiente; filas ColorPicker/IconPicker).
  Verificar: tabla del catálogo refleja el kit.

## 3. Backend — Store Module

- [x] 3.1 Ajustar `StoreModuleRequest`: sin `slug` del cliente; `name` unique CI;
  `label`/`explanation` required solo en locale default (`es`); `color`/`icon`/`level`
  required; `status` default Active o accepted enum; icon in:catálogo. Verificar:
  tests Feature de validación (3.3).
- [x] 3.2 Ajustar `CreateModule` (y/o prepareForValidation) para derivar
  `Str::slug(name)`, validar slug unique CI, crear `{slug}.access` (ya existente).
  Verificar: store feliz crea slug + permiso.
- [x] 3.3 Tests Feature: alta OK → redirect Edit; name CI duplicate fail; slug CI
  duplicate fail; falta label/explanation principal fail; secundario vacío OK;
  sin permiso create → 403. Verificar:
  `php artisan test --compact --filter=Module` (o fichero dedicado Create) verde.
- [x] 3.4 Pint PHP tocado: `vendor/bin/pint --dirty --format agent`. Verificar: exit 0.

## 4. Frontend — Create Module

- [x] 4.1 Rehacer `Admin/Modules/Create` (form propio o ModuleForm solo-create) con
  nombre, Select nivel, IconPicker, ColorPicker, Switch status (default on), LangTabs
  (label Input + explanation Textarea); sin input slug; submit POST `/admin/modules`.
  Verificar: UI muestra los campos; no hay campo slug.
- [x] 4.2 i18n de labels/errores nuevos del Create/kit en `lang/{es,en,ca}.json` (claves
  alfabéticas). Verificar: CI/rule de i18n o revisión de claves presentes en los tres.
- [x] 4.3 No migrar Edit en este change (dejar Edit con form anterior). Verificar:
  Edit sigue renderizando; Create no comparte campos fuera de contrato.
- [x] 4.4 Create usa el ancho del contenido: dos columnas desde `lg`, una columna
  por debajo. El borde del card es `border-slate-200` (igual que el listado) también
  en Edit, Funcionalidades, Roles y Roles de empresa.

## 5. Cierre de integración

- [x] 5.1 Smoke manual o Feature+build: Create → redirect Edit; icono/color/status/
  LangTabs persisten en BD. Verificar: un alta de prueba en local o test de store que
  lea el modelo creado.
- [x] 5.2 `vendor/bin/pint --dirty --format agent` final si hubo más PHP. Verificar:
  exit 0.
- [x] 5.3 El POST de alta devolvía 500 intermitente (`MissingAppKeyException`,
  entorno `production`) porque Apache en Windows comparte `putenv` entre hilos
  mientras el layout hace polling. `Env::disablePutenv()` en `bootstrap/app.php`
  antes de arrancar la app. Verificar: altas en paralelo sin 500 de clave.

## Workflow follow-up

- Revisión humana (Luis) antes de `/opsx-apply`.
- Tras apply: 25-architecture-review → (E2E N/A) → 30-testing / archive.
- Change posterior: Edit Module (reutilizar LangTabs/IconPicker/ColorPicker).
