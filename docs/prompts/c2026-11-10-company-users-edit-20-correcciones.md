Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-10-company-users-edit
Origen: informe 25-architecture-review (**RETURN TO IMPLEMENTATION**).
Contrato: `openspec/changes/c2026-11-10-company-users-edit/`
(proposal, design §6 / D5–D6, specs `user-edit`, tasks §2.2 / §2.4).
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni los deltas de specs.
No archives. No escribas tests/Browser/ (E2E N/A en design). No amplíes
alcance (avatar, CRUD emails/tels/empresas, slug final del permiso de
flags, relación empresa en `/profile`).

## Problema (HIGH) — perímetro SA en Edit/update ajeno

La ficha unificada (tabs, Perfil, Contraseña self-only, gancho
`UserProfileAuthorization`) está bien encaminada. El Feature
`tests/Feature/Admin/UserEditTest.php` pasa (11 tests), incluido
password self-only ni SA.

Falla el **perímetro tenant** en el camino company-users:

- `CompanyUserController::edit` / `update` solo hacen
  `$this->authorize('updateCompanyUser', $user)`.
- `Gate::before` de Super Admin devuelve `true` → la Policy
  (`memberBelongsToCurrentCompany`) **no se ejecuta**.
- `UpdateUserProfile` **no** aserta que el target esté en
  `user_companies` de `CurrentCompany`.

Efecto: un SA con sesión en company A puede GET/PUT la ficha de un user
vinculado solo a company B. Eso rompe:

- design §6 («`company-users.update` + member en CurrentCompany»),
- spec «Update ajeno fuera de perímetro»,
- kernel: SA bypasa permisos, **no** el perímetro de companies.

Contraste ya correcto en el mismo dominio:

- `DetachCompanyUser` / `UpdateCompanyUserStatus` comprueban
  `$member->companies()->where(..., $company->id)->exists()`.
- Password: `abort_unless($actor->id === $user->id, 403)` **antes** de
  Gate + assert en `UpdateOwnPassword` (no tocar ese patrón).

## Qué hacer (obligatorio HIGH)

### 1. Assert de perímetro independiente de Gate::before

En el camino **company** (no en `/profile` self):

1. Resolver `CurrentCompany` → `Company` (como ya hace el controller).
2. Antes o dentro de `UpdateUserProfile` (y al abrir `edit`), rechazar si el
   user target **no** está vinculado a esa company (403 / ValidationException
   coherente con siblings: p. ej. `__('usuario_no_vinculado_empresa')`).
3. El assert MUST vivir en capa que SA no pueda saltarse vía Policy
   (Action y/o check explícito en controller **después** del authorize, o
   Action llamada siempre con `$company` + assert interno). Preferir
   reforzar la **Action** (o helper compartido con Detach/UpdateStatus) para
   no depender solo del authorize.

No inventes framework nuevo: reutiliza el patrón de
`DetachCompanyUser` / `UpdateCompanyUserStatus` (o un trait/helper mínimo
si ya hay uno cercano; no copies el de roles salvo que encaje).

`/profile` (self): sin exigir vínculo a empresa (NonGoal / design).

Password: no cambiar el assert self-only.

### 2. Feature que falle en rojo sin el fix (obligatorio)

Añadir en `UserEditTest` (o ampliar el existente):

- Actor **Super Admin** con CurrentCompany = A.
- User vinculado **solo** a company B.
- `GET admin.company-users.edit` → 403 (o NotFound si el proyecto unifica así;
  debe ser inequívoco: **no** 200 con ficha usable).
- `PUT admin.company-users.update` → mismo rechazo; datos del user B
  intactos.

Mantener verdes los escenarios ya existentes (self, ajeno sin update, ajeno
en perímetro, password SA→ajeno 403, flags sin permiso futuro).

### 3. Verificación

```text
php artisan test --compact tests/Feature/Admin/UserEditTest.php
```

(y cualquier Feature company-users tocado). Pint paths PHP dirty. No PHPStan
de más fuera de lo tocado.

## UI / estilos — coherencia (obligatorio si retocas front)

Si al corregir tocas `Users/Edit`, layout o tabs: **no inventes** look & feel.

- Reutiliza componentes y patrones de páginas ya resueltas:
  `Admin/Modules/Edit.tsx` (AppLayout + `showBack` / ViewActionBar del layout +
  `RecordSummary` + `auditRecordSummaryFields`), Form kit
  (`Field`/`Input`/`Switch`/`Checkbox`/`DatePicker`), `RecordEditLayout`,
  `UserProfileReadout`.
- Misma jerarquía tipográfica, bordes (`border-slate-200`), radios, paddings
  y grid ya usados en esas fichas / en el Create company-users.
- No nuevos esquemas de color, sombras multilayers, pills, ni CSS ad-hoc
  “bonito”. Catálogo: `docs/standards/frontend.md`.
- No sustituyas `RecordEditLayout` ni dupliques la página; no metas RHF+zod
  en este fix si la página ya usa `useForm` Inertia como Create.

Si el HIGH no exige cambios de UI, **no retques estilos**.

## Fuera de alcance

- Definir slug final de `visibility_permission`.
- Avatar upload, CRUD emails/tels/empresas.
- Ampliar SA a “ver todas las companies”.
- Reabrir Create/link company-users salvo si un helper compartido de
  perímetro lo toca de forma mínima.

## Gate de salida

- [ ] Edit/update company-users rechazan user fuera de CurrentCompany también
      para Super Admin.
- [ ] Feature SA + user solo otra company en rojo sin el assert, verde con él.
- [ ] Password self-only intacto.
- [ ] Suite `UserEditTest` verde; sin inventar UI.
- [ ] Volver a 25-architecture-review (no archive).
