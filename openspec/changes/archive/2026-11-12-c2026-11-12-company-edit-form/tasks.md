# Tasks

## 1. Backend Info + slug

- [x] 1.1 `UpdateCompanyRequest`: name, tradename, nif (required +
      `ValidCompanyTaxId` + unique ignore self + max:12), status; **sin**
      slug ni logo. Verificar Feature rechazo nif inválido/duplicado.
- [x] 1.2 `UpdateCompany`: regenerar slug con `UniqueSlug` desde name;
      `exists` ignora el id propio (`withTrashed` + `whereKeyNot`). No
      aceptar slug del cliente. Verificar Feature update regenera slug y
      colisión con otra company.
- [x] 1.3 Perímetro: update rechazado si company no vinculada al actor
      (R-TEN-05 / `assertLinked`). Verificar Feature 403.

## 2. Backend logo

- [x] 2.1 Action `UploadCompanyLogo` + FormRequest (imagen, max ~512 KB /
      500 KB UI) + ruta POST dedicada; borra fichero anterior; authz
      update + vínculo. Verificar Feature upload OK y rechazo > límite.
- [x] 2.2 Tras upload, respuesta Inertia que refresca shared; si company =
      CurrentCompany, `tenant.logo_url` apunta al nuevo path; si no, no
      cambia. Verificar Feature (sesión vs otra company).
- [x] 2.3 Quitar `members_url` / logo string del flujo Edit controller +
      Resource si aplica. Verificar props Edit sin members_url.

## 3. Frontend Edit

- [x] 3.1 `Edit.tsx`: `RecordEditLayout` + tabs `info` / `images` +
      `?tab=`; sin link Miembros. Verificar source/UI.
- [x] 3.2 Tab Info: `CompanyProfileReadout` (logo ▭ centrado V+H) + form
      name/tradename/nif (maxLength 12)/status; sin slug/logo. Ajustar
      `CompanyForm` modo edit. Verificar UI.
- [x] 3.3 Tab Imágenes: input file simple; POST logo; i18n. Verificar UI
      upload.

## 4. Docs + calidad

- [x] 4.1 `docs/modules/Partners.md`: fila Edit tabs Info/Imágenes, slug
      regen Update, logo único, Members diferido. Verificar fila.
- [x] 4.2 Pint + PHPStan + Feature Partners edit/logo en verde.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica (`e2e.md` §4).
- Tab Miembros: change futuro.
