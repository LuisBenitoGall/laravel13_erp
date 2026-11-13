Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-12-admin-sole-domain
Origen: informe 25-architecture-review (**APPROVED WITH OBSERVATIONS**).
Estas son correcciones **documentales** (MEDIUM + LOW). No reabran diseño.
Contrato: `openspec/changes/c2026-11-12-admin-sole-domain/`
(proposal, design, specs `admin-sole-domain`).
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni los deltas de specs.
No archives. No E2E. No toques código PHP/React/rutas salvo que un
import de doc de ejemplo lo exija (no debería).

## Por qué 20 y no 10

La norma ya está cerrada en el change (Admin = único dominio PHP funcional;
Company/Bank en Admin; `/admin/...`). Aquí solo se **sincroniza** constitución,
fichas y plantillas con esa decisión para que changes futuros no reintroduzcan
`Domains/Partners` o `Domains/Banks`. Sin A/B ni alcance nuevo.

## Contexto

Runtime OK (move, rutas, menú, front, Feature smoke). Quedan textos que aún
tratan Partners/Banks como dominios vivos y una dependencia fantasma en
`Admin.md`. Eso puede desalinear briefs de 10 y scaffolds de 20.

Fuente de verdad ya correcta (no romperla):
- `docs/standards/module-map.md` (Admin sole; absorbed Partners/Banks)
- `docs/modules/Partners.md` / `Banks.md` (stubs «absorbido»)
- Código `App\Domains\Admin` + `routes/domains/admin.php`

## Qué hacer

### 1. MEDIUM — `Admin.md` dependencias

En `docs/modules/Admin.md` §Dependencias (hoy ~L118):

Sustituir:
> Admin → Partners (lectura `Company`). Sin cambio de module-map.

Por algo equivalente a (español, corto):
> Sin dependencia a otros dominios PHP en Fase 0: Company y Bank viven en
> Admin. Lecturas cross-domain futuras: ver `module-map.md`.

No inventes filas de matriz nuevas.

### 2. MEDIUM — Constitución User/Company

Alinear wording **sin** cambiar reglas de tenancy (sigue: Company = tenant;
sin `BelongsToCompany` en Company; `CurrentCompany`; SA no amplía perímetro).

| Archivo | Hoy | Debe quedar |
|---|---|---|
| `docs/standards/kernel.md` (~L25) | `Company` (dominio Partners) | `Company` (dominio **Admin**) |
| `docs/standards/architecture.md` (~L51) | `Company` de Partners | `Company` de **Admin** |
| `docs/standards/architecture.md` (~L104, R-TEN-08) | Company (Partners) **es** el tenant | Company (**Admin**) **es** el tenant |
| `.cursor/rules/01-kernel.mdc` (~L21) | Company (Partners) = único tenant… | Company (**Admin**) = único tenant… |
| `CLAUDE.md` (estado actual / Fase 0) | «Company en Partners» | «Company en **Admin**» (o equivalente fiel al sole-domain) |

Si en esos ficheros aparece «dominio Banks» o Partners como dominio PHP vivo,
corregir igual. No reescribas secciones enteras: cambio quirúrgico de naming.

### 3. LOW — Glosario

`docs/standards/glossary.md` fila Banco (~L89):

- De: «Catálogo global dominio Banks…»
- A: «Catálogo global **Admin** (sin `company_id`); Functionality `banks` bajo
  menú Configuración» (mantén el resto coherente con filas Country/Unit).

Si hay otras filas que digan «dominio Partners» o «dominio Banks» como
`Domains/*` separados, alinear a Admin. No renombres términos de negocio
(Company, Bank, etc.).

### 4. LOW — Plantillas y tooling (ejemplo Worksite)

Actualizar el ejemplo pedagógico Worksite para que nazca en **Admin**, no en
Partners (norma: entidades nuevas en Admin hasta OpenSpec que reabra dominios).

Archivos:
- `docs/standards/templates-backend.md`
- `docs/standards/templates-frontend.md`
- `docs/standards/tooling.md` (lista de paths del ejemplo)

Sustituciones mínimas coherentes:
- `App\Domains\Partners\...` → `App\Domains\Admin\...`
- `routes/domains/partners.php` → grupo existente `routes/domains/admin.php`
  (no inventes de nuevo un `partners.php`)
- `resources/js/types/partners.d.ts` → `resources/js/types/admin.d.ts`
  (ya existe en el repo; merge del ejemplo ahí)
- Imports front `@/types/partners` → `@/types/admin`
- Comentario cabecera frontend «Worksite (Partners)» → «Worksite (Admin)»

No cambies la forma del template (Actions, Policy, ServerTable, etc.): solo
el dominio/paths del ejemplo.

### 5. LOW — `docs/vision-v2.md` árbol Domains/

El árbol aspiracional aún lista `Partners/` junto a `Admin/`. Eso confunde
Fase 0.

- No borres el mapa multi-dominio a largo plazo.
- Anota de forma explícita que **hoy** Partners (y Banks como dominio) están
  **absorbidos en Admin**; Company/Bank viven en `Admin/` hasta un change
  OpenSpec que reabra dominio separado.
- Opción preferida: dejar la línea `Partners/` con sufijo
  `(absorbido en Admin — Fase 0)` y/o nota bajo el bloque; o fusionar el
  contenido «empresas…» bajo `Admin/` y marcar Partners como futuro/opcional.
  Elige la opción que menos reescriba el doc y deje inequívoco el estado actual.

## Verificación

- `rg -n "dominio Partners|Company \(Partners\)|Domains/Partners|dominio Banks|Admin → Partners" docs/standards docs/modules CLAUDE.md .cursor/rules/01-kernel.mdc`
  → sin hits de dominio vivo (stubs `Partners.md`/`Banks.md` «absorbido» OK).
- `rg -n "Domains\\\\Partners|types/partners|domains/partners" docs/standards/templates-backend.md docs/standards/templates-frontend.md docs/standards/tooling.md`
  → sin hits.
- No hace falta suite Feature completa (sin código de producción). Si tocaste
  solo markdown/rules: no Pint obligatorio.

## Fuera de alcance

- Reabrir dominios PHP Partners/Banks.
- Mover código, rutas, menú, tests.
- Rediseñar module-map (ya correcto).
- Borrar stubs `Partners.md` / `Banks.md`.
- Archive / 30-testing formal (tras esto, Luis decide si re-25 o directo 30).

## Gate de salida

- [ ] `Admin.md` §Dependencias sin «→ Partners».
- [ ] `kernel.md`, `architecture.md`, `01-kernel.mdc`, `CLAUDE.md` hablan de
      Company en **Admin**, no Partners.
- [ ] Glosario Bank = Admin.
- [ ] Templates/tooling Worksite bajo Admin + `admin.php` / `admin.d.ts`.
- [ ] `vision-v2.md` no presenta Partners como dominio PHP activo en Fase 0.
- [ ] Sin cambios de comportamiento runtime.
)
