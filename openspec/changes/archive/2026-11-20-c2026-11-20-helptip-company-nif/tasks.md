# Tasks

## 1. Form kit + HelpTip UX

- [x] 1.1 `Field` (+ `FormField` si aplica): prop `helpCode`; layout
      control + icono derecha. Verificar composición en Story/página o
      test de render.
- [x] 1.2 Ajuste visual HelpTip (ámbar/info tipo captura v1) sin romper
      null si code ausente. Verificar UI.

## 2. Company NIF

- [x] 2.1 `CompanyForm` Create/Edit: `helpCode="company-nif"` en Field
      NIF. Verificar Create y Edit muestran tip con Help Active.

## 3. Promoción Help

- [x] 3.1 Fixture `database/data/contents_help.json` con al menos
      `company-nif` (ES/EN/CA; kind Help; status Active). Verificar
      fichero.
- [x] 3.2 `ContentsHelpSeeder` upsert por `code` (no borra ajenos).
      Verificar Feature/seeder.
- [x] 3.3 Artisan `contents:export-help` → escribe fixture desde Help
      Active globales. Verificar comando en test o dry-run documentado.
- [x] 3.4 Docs: frontend.md (helpCode) + nota Admin/ficha Contents;
      Pint + Features verdes.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Más anclas = solo `helpCode` en cada Field (sin change de infra).
