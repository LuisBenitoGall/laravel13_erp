## Context

Brief: `docs/prompts/c2026-10-28-shell-breadcrumb.md`. v1 `$header` option/suboption/
module/slug; blade pinta option+suboption. v2: `AppLayout` solo `title` → `<Head>`;
páginas con subtítulos ad hoc. Topbar ya sin título de vista.

## Goals / Non-Goals

**Goals:** breadcrumb bajo topbar; contrato i18n; option→index; Index→Dashboard;
migrar AppLayout pages; look v2.

**Non-Goals:** ActionBar; stubs topbar dominio; module/slug aside; pantallas nuevas.

## Decisions

1. **API de página → layout**  
   Prop en `AppLayout` (o children wrapper):  
   `breadcrumb={{ optionKey, suboptionKey, optionHref }}`  
   Textos: `t(optionKey)`, `t(suboptionKey)`. `optionHref` = ruta index de la
   funcionalidad (string path o named via Ziggy si existe; hoy paths absolutos como el
   resto del front).  
   Alternativa descartada B: resolver labels desde menú/catálogo por slug.

2. **`<Head>` / document title**  
   Mantener `title?: string` opcional en AppLayout para `<Head>`, o derivar
   `${t(optionKey)} · ${t(suboptionKey)}` si no se pasa title. Preferencia: **derivar por
   defecto** del breadcrumb y permitir override `title` si hace falta.

3. **Componente**  
   `Components/PageBreadcrumb` (añadir a catálogo `frontend.md`). Estructura: option
   (Link, tipografía grande) + suboption (span, más pequeño, al lado). Sin separador tipo
   `>` obligatorio (v1 no usa chevron en el ejemplo; textos adyacentes).  
   Alternativa descartada: meter esto solo dentro de `RecordEditLayout` (el Index también
   lo necesita).

4. **Index**  
   `suboptionKey: 'dashboard'` siempre en listados Index (clave existente).

5. **Create/Edit**  
   `suboptionKey` específicas ya existentes o nuevas snake_case (`empresa_nueva`,
   `modulo_editar`, etc.) — reutilizar claves de `lang/*.json` cuando existan.

6. **Casos especiales**  
   - `Dashboard.tsx`: option = `dashboard` (o `inicio` si es la clave de menú actual);
     suboption `dashboard` o omitir secundario vacío — **preferencia**: option al propio
     dashboard (`optionHref: '/dashboard'`), suboption `dashboard` (redundante OK) o
     20 usa la misma clave de menú que AsideNav para option.  
   - `Profile/Show`: option `perfil`, href `/profile`, suboption `dashboard` o
     `proximamente` — preferencia Index-like: suboption `dashboard`.  
   - Forms embebidos en components que envuelven AppLayout: el padre Create/Edit declara
     breadcrumb.

7. **Migración**  
   Checklist de todas las páginas con `AppLayout`; quitar H1/subtítulos duplicados que
   solo compensaban la falta de breadcrumb (mantener CTA “Crear” en Index dentro de
   children).

8. **E2E**  
   N/A. Feature: smoke breadcrumb en una página Index + una Edit.

## Risks / Trade-offs

- **[Riesgo]** Claves option no alineadas al label del menú → Mitigation: usar las mismas
  claves i18n que el menú/funcionalidad donde existan.  
- **[Trade-off]** Dashboard con option=suboption “Dashboard” es redundante visualmente —
  aceptable hasta haber copy distinto.

## Migration Plan

1. `PageBreadcrumb` + prop en `AppLayout`.  
2. Migrar páginas AppLayout + i18n gaps.  
3. Catálogo frontend.md.  
4. Tests + calidad.

## Open Questions

Ninguna bloqueante.

## Excepciones a standards

Ninguna.

## E2E

No aplica.
