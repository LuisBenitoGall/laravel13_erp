## Context

Brief: `docs/prompts/c2026-10-28-shell-action-bar.md`. Bloques 1–2 hechos (`AppTopbar`,
`PageBreadcrumb`). Index aún pone `Crear` en children. Catálogo: `Records/ActionBar`
(documento) y `Form/FormActions` (pendiente) — **no** mezclar con chrome de vista.

## Goals / Non-Goals

**Goals:** ViewActionBar bajo breadcrumb; array extensible; Volver si origen; Delete
último + confirm; migrar AppLayout; form buttons en el form.

**Non-Goals:** FormActions; dropdowns implementados; ciclo de vida documento (facturar…);
stubs topbar.

## Decisions

1. **Naming**  
   Componente `Components/ViewActionBar` (chrome bloque 3). Reservar
   `Records/ActionBar` para ciclo de vida de documento. Form submit → futuro
   `Form/FormActions` dentro del form. Actualizar `frontend.md`.

2. **Contrato de acciones** (discriminated union extensible)  
   ```ts
   type ViewAction =
     | { type: 'link'; labelKey: string; href: string; icon: LucideIcon }
     | { type: 'button'; labelKey: string; icon: LucideIcon; onClick: () => void }
     | { type: 'delete'; onConfirm: () => void; labelKey?: string } // trash; confirm
     | { type: 'dropdown'; ... } // tipado opcional / stub type para no cerrar puerta;
                                  // no UI en este change
   ```  
   `AppLayout` recibe `actions?: ViewAction[]`. Orden: Volver (inyectado) → actions sin
   delete → delete al final. La page **no** declara `type: 'back'` en el array; el layout
   lo antepone si hay origen.

3. **URL de origen**  
   Al navegar Inertia (evento `navigate` / `router.on('navigate')`), guardar la URL
   previa en `sessionStorage` (clave `erp.nav.origin` o similar) solo si el destino es
   distinta. Al montar la vista, leer origen; si vacío o igual a la URL actual → no
   Volver. Volver = `Link`/`router.visit(origin)` (no solo `history.back()`, más
   predecible en SPA). Limpiar o rotar el valor según diseño 20 (documentar).  
   Alternativa descartada: siempre `optionHref` del breadcrumb (Luis: sin origen = sin
   botón).

4. **Layout visual**  
   Flex row, `justify-start`, `gap` pequeño entre CTAs; tras el último CTA no-delete,
   `gap` mínimo antes del trash (p. ej. mismo gap o `gap-1` extra — **no** `ml-auto` a
   pantalla completa). Trash variante destructive (rojo). Icono Lucide a la izquierda del
   label.

5. **Confirm delete**  
   `Feedback/ConfirmDialog` (crear si no existe) — i18n confirm/cancel; solo entonces
   `onConfirm`.

6. **Migración**  
   Todas las AppLayout: declarar `actions` (puede ser `[]`). Mover `Crear` de Index a
   `{ type: 'link', labelKey: 'crear', href, icon: Plus }`. Create/Edit: sin Crear
   obligatorio; Volver automático si hay origen. Delete solo donde ya exista borrado en
   UI o ruta destroy clara; **no inventar** deletes nuevos sin endpoint.

7. **Form buttons**  
   No mover saves al ViewActionBar. Si un form tiene botones abajo, pueden subirse
   **dentro** del contenedor del form (arriba) en el mismo change solo si hace falta
   coherencia mínima — no es FormActions completo.

8. **E2E**  
   N/A. Feature: Index muestra Crear en barra; navigate Create → Volver visible si origen
   guardado; delete abre confirm (si hay página con delete).

## Risks / Trade-offs

- **[Riesgo]** Origen incorrecto tras refresh → Mitigation: sessionStorage sobrevive
  refresh de la página destino; si el user abre URL en pestaña nueva, sin origen = sin
  Volver (correcto).  
- **[Riesgo]** Confundir ViewActionBar con FormActions → Mitigation: naming + docs.  
- **[Trade-off]** Tipar `dropdown` sin UI — evita romper el union al ampliar.

## Migration Plan

1. Origin helper + ConfirmDialog + ViewActionBar.  
2. AppLayout prop `actions`.  
3. Migrar páginas + i18n.  
4. frontend.md + tests.

## Open Questions

Ninguna bloqueante.

## Excepciones a standards

Ninguna.

## E2E

No aplica.
