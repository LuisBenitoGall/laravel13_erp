Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-16-user-preferences-favorites
Origen: informe 25-architecture-review (**RETURN TO IMPLEMENTATION**).
Hay un **HIGH** bloqueante + **MEDIUM** / **LOW** de seguimiento.
No reabran diseño.
Contrato: `openspec/changes/c2026-11-16-user-preferences-favorites/`
(design D1/D3, specs `user-preferences-favorites`).
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni los deltas de specs.
No archives. E2E N/A.

## Contexto

Alta/tope 20/dashboard/aislamiento y popover topbar están OK
(`PreferenceTest` 8/8). El fallo bloqueante es
`PreferenceUrlNormalizer`: deja pasar URLs protocol-relative
(`//evil.com/...`). El Dashboard las usa en `<Link href={preference.url}>`
→ el CTA puede sacar al usuario del ERP. Cualquier cliente puede
`POST /admin/preferences` con `url` arbitraria (no solo `page.url`).

Además (MEDIUM): `url` max 760 vs design 2048 (MySQL unique); mapa
`hrefFor` del resolver duplicado vs MenuBuilder.

## Qué hacer

### 1. HIGH — Endurecer normalización / validación de `url` (obligatorio)

Archivos: `PreferenceUrlNormalizer`, `StorePreferenceRequest` y/o
`CreatePreference`; tests.

Reglas (sin A/B; sin deps nuevas):

1. Tras normalizar (sin scheme/host/hash, conservar query):
   - MUST ser path absoluto de app: empieza por **un solo** `/`.
   - MUST NOT ser protocol-relative: rechazar si empieza por `//`.
   - MUST NOT quedar con `://` ni host.
2. Alcance de path permitido (mínimo seguro alineado al ERP):
   - Prefijos admitidos: `/admin`, `/dashboard`, `/account` (y el path
     exacto `/dashboard` / variantes ya usadas).
   - Rechazar cualquier otra raíz (`/`, `/login`, `//…`, `/http…`, etc.).
3. Si no cumple → `ValidationException` / error de validación en `url`
   (mensaje i18n; no persistir).
4. Dashboard puede seguir enlazando `preference.url` confiado tras el
   filtro de escritura (no hace falta sanitizar en React si el store es
   estricto). Opcional: defensa en lectura solo si es trivial.

Feature / unit (obligatorio):

- `normalize('//evil.com/phish')` no produce un href usable **o** el
  store con esa url → 422 / session errors en `url` y **cero** filas.
- Store con `https://erp.test/admin/companies?x=1` → persiste
  `/admin/companies?x=1` (comportamiento actual bueno).
- Store con `/admin/workplaces` OK; con `/logout` o `//x` NO.

### 2. MEDIUM — `url` 760 vs design 2048

Elegir **una** (mínima):

- **A (preferida):** dejar 760 + unique MySQL; añadir una línea en
  comentario del seeder/migración **no** reescribir design.md; en el
  PR/commit message o nota en `Admin.md` (ya existe ficha) aclarar
  «url ≤760 por índice unique utf8mb4».
- **B:** si insistís en 2048, unique por hash de url u otro esquema —
  **solo** si no rompe unique actual; no es necesario si A basta.

No inventar otra columna `code`.

### 3. MEDIUM — Drift MenuBuilder / PreferenceModuleResolver

Vía mínima (elige la más barata que evite duplicar a mano):

- Extraer el mapa slug→href a un helper/single source usado por
  MenuBuilder **o** por el resolver (sin refactor masivo del menú), **o**
- Documentar en comentario del resolver «mantener alineado con
  MenuBuilder» y añadir un Feature/assert que al menos un path de
  menú conocido (`/admin/companies`) resuelva al Module `empresas`
  (ya existe parcialmente) + lista corta de slugs críticos.

No rehacer todo el menú en este fix si el coste es alto: HIGH manda.

### 4. LOW (recomendado)

- Assert de fuente/unit del caso `//` en `PreferenceTest`.
- Confirm delete en dashboard: **no** exigir (D5 permite X como v1).

## Verificación

```text
php artisan test --compact tests/Feature/Admin/PreferenceTest.php
```

Pint dirty PHP.

## Fuera de alcance

- Functionality/menú lateral nuevos.
- Notificaciones / notas / chat (siguen stub).
- Favoritos de producto / sin `company_id`.
- Subir tope 20.
- Archive / E2E 40.
- Reescribir OpenSpec.

## Gate de salida

- [ ] `//…` y urls fuera de prefijos app no se persisten; mensaje claro.
- [ ] Paths `/admin…`, `/dashboard…`, `/account…` siguen OK tras normalizar.
- [ ] Feature Preferencest verde (incl. regresión open-redirect).
- [ ] 760 documentado en Admin.md **o** solución B; sin silencio.
- [ ] Pint OK.
- [ ] Volver a **25-architecture-review** (no a 30 hasta APPROVED).
