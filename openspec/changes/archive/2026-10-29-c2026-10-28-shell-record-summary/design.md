## Context

Brief: `docs/prompts/c2026-10-28-shell-record-summary.md`. Chrome 1–3 existen. Catálogo
cita `RecordEditLayout` (aún no implementado) y `statusBadge` / `lib/status-colors.ts`
(pendiente). Alcance: solo componente + catálogo.

## Goals / Non-Goals

**Goals:** RecordSummary card; title + id badge + fields[]; audit helpers; status tone;
frontend.md; test render.

**Non-Goals:** totales; lápiz inline; migrar Edits; dominio pedidos; RecordEditLayout
completo.

## Decisions

1. **Nombre y ubicación**  
   `Components/Records/RecordSummary.tsx` — pieza de cabecera edit/show; no montar en
   `AppLayout` automáticamente (la page lo coloca bajo ViewActionBar).  
   Alternativa descartada: prop global en AppLayout (Index/Create no lo usan).

2. **Contrato**  
   ```ts
   type RecordSummaryField = {
     labelKey: string;
     value: ReactNode; // string | formatted date | …
     tone?: 'default' | 'muted' | 'status';
   };

   type RecordSummaryProps = {
     titleKey: string;       // i18n del tipo de documento/entidad
     identifier: string;     // badge (número, slug, id visible)
     fields: RecordSummaryField[];
   };
   ```  
   Title vía `t(titleKey)`; identifier texto crudo (ya formateado por la page).  
   Alternativa descartada: title como ReactNode libre (rompe i18n).

3. **Auditoría**  
   Helper `auditRecordSummaryFields(input: { created_at?, created_by?, updated_at?, updated_by? })`  
   que devuelve `RecordSummaryField[]` con labelKeys estándar (`creado`, `autor`, …)
   omitiendo undefined. La page concatena `...audit..., ...custom`.

4. **Tono status**  
   `tone: 'status'` → clase ámbar/naranja (look v1 Pendiente) sin acoplar aún a enums de
   dominio. Cuando exista `lib/status-colors.ts`, las pages mapearán enum → clase o se
   ampliará el tone. Badge del identifier: estilo badge destacado (ámbar) distinto del
   texto de estado si hace falta.

5. **Layout**  
   Card `bg-white rounded-lg border …`; fila 1 título + badge; fila 2 flex wrap de
   `label: value`. Responsive wrap. Sin columna de totales.

6. **Demo / test**  
   Test unitario/RTL del render (title, badge, un campo status). Sin página demo
   obligatoria ni migración Edit.

7. **E2E**  
   N/A.

## Risks / Trade-offs

- **[Riesgo]** Status colors ad hoc vs mapa futuro → Mitigation: tone genérico + nota en
  frontend.md.  
- **[Trade-off]** Solo componente: nadie lo ve en UI hasta un change consumidor —
  aceptable (Luis: solo componente).

## Migration Plan

1. RecordSummary + helper audit + i18n.  
2. frontend.md.  
3. Test render.  
4. Calidad front.

## Open Questions

Ninguna.

## Excepciones a standards

Ninguna.

## E2E

No aplica.
