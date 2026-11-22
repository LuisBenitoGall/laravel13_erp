# Design

## Context

`<ServerTable>` ya centraliza listados Tabulator (`rowActions` derecha, toolbar
columnas/page size/export). No hay selección ni bulk. Usuarios empresa ya tiene
fila: toggle status (`UpdateCompanyUserStatus`), detach (`DetachCompanyUser`),
edit; permisos `company-users.*` vía `CompanyUserPolicy`. Brief:
`docs/prompts/c2026-11-22-server-table-mass-actions.md`.

Sin ficha de módulo nueva: no se crea Module/Functionality; se extiende el
contrato ServerTable y el Index existente. Vinculaciones V-01..V-20 de entidad
nueva: N/A. E2E §4: N/A (CRUD / feature tests).

## Goals / Non-Goals

**Goals:**

- API opt-in dinámica `massActions` en ServerTable.
- Checkbox 1ª columna + dropdown izquierda de «Mostrar/ocultar columnas».
- Selección = página actual; clear al cambiar página/datos; header “select all
  page”.
- Dropdown `disabled` si `selected.length === 0`.
- Piloto company-users: bulk detach (confirm), bulk status true/false.
- Actualizar `frontend.md` §3 (+ norma en spec) con el contrato y la regla de
  agente («preguntar qué acciones»).

**Non-Goals:**

- Select-all filtrados / cross-page.
- Email u otras acciones fuera del piloto.
- Mass actions en otros Indexes.
- Cambiar semántica de delete fila (sigue detach).

## Decisions

1. **Prop `massActions` (opt-in)**  
   ```ts
   type MassAction<T> = {
     id: string;
     labelKey: string;
     confirm?: boolean | { titleKey?: string };
     variant?: 'default' | 'danger';
     onAction: (rows: T[]) => void | Promise<void>;
   };
   ```
   Si `massActions` ausente o `[]` → sin columna ni dropdown. Lista dinámica =
   el Index pasa el array que quiera (hoy 3 ítems; mañana N).  
   Alternativa descartada: flags booleanos fijos (`enableBulkDelete`) — no
   escala a acciones distintas por tabla.

2. **Columna checkbox**  
   Insertada al inicio de `tabulatorColumns` (antes de data cols; Acciones
   siguen a la derecha). `headerSort: false`, no entra en
   mostrar/ocultar columnas. Formatter checkbox por fila + header checkbox
   página. Estado de selección en React (`Set`/`Map` de id de fila); al
   `dataLoaded` / cambio de página se limpia.  
   Campo fila: usar `id` del row (company-users ya lo expone).

3. **Toolbar**  
   Orden: `[MassActionsDropdown?] [Mostrar/ocultar] [page size] [export?]
   [toolbarExtra]`. Dropdown outline como el de columnas; ítems = `massActions`;
   danger al final si `variant === 'danger'`. Confirm → reutilizar
   `ConfirmDialog` (mismo patrón que row delete).

4. **Backend company-users**  
   - `BulkDetachCompanyUsers`: transacción; por cada id verifica vínculo a
     `CurrentCompany` y llama `DetachCompanyUser` (o lógica equivalente).  
   - `BulkUpdateCompanyUserStatus`: igual con `UpdateCompanyUserStatus` y
     `status` bool.  
   - Rutas POST JSON p. ej.  
     `admin/company-users/bulk-detach`  
     `admin/company-users/bulk-status`  
     Body: `{ ids: int[] }` / `{ ids: int[], status: bool }`.  
   - FormRequest: `ids` required array, max razonable (p. ej. page size máx.
     100), cada id exists + vinculado a company sesión (R-TEN-07).  
   - Authz: misma Policy que fila (`deleteCompanyUser` /
     `updateCompanyUser`) — authorize el ability una vez y filtrar/abortar
     ids no vinculados; 403 si el actor no tiene el permiso.  
   Alternativa descartada: un solo endpoint `bulk` con `action` string —
   menos tipado; dos rutas dejan Actions claras.

5. **Front piloto**  
   `CompanyUsers/Index`: declarar `massActions` que POST/router a los
   endpoints (Inertia `router.post` o `patchJson`/`http` del proyecto), toast
   error, refresh tabla (`tableKey` o `setData`). Tras éxito, limpiar
   selección.

6. **Standards**  
   Actualizar `docs/standards/frontend.md` §3 toolbar + nuevo bullet
   massActions opt-in; nota para agentes: al pedir mass actions en un Index,
   incluirlas y preguntar el set. Touch `templates-frontend.md` ejemplo si el
   contrato de referencia lo exige.  
   Nota: esto es extensión del contrato ServerTable ya normativo (igual que
   changes previos de toolbar), no un PR de “corregir estándar roto” aparte.

7. **Tests**  
   Feature: bulk detach/status solo afecta users de la company de sesión
   (R-TEN-05); 403 sin permiso; validación ids ajenos.  
   Front: opcional assertion ligera o smoke; no E2E obligatorio.

## Risks / Trade-offs

- [Selección se pierde al paginar] → Mitigation: esperado (alcance página);
  documentar en UI vía comportamiento, no “select all filtered”.
- [Status es del User global, no del pivot] → Mitigation: ya es así en fila;
  bulk no cambia el modelo (solo NonGoal evitar sorpresa: desactivar afecta
  al user en todas las companies).
- [Muchos ids / timeout] → Mitigation: max ids = tope page size opciones
  ServerTable; sin jobs en este change.

## Migration Plan

1. ServerTable + i18n + frontend.md.  
2. Actions/routes/requests company-users.  
3. Index piloto.  
4. Tests. Sin migración BD.

Rollback: revertir change; Indexes sin `massActions` siguen igual.

## Open Questions

Ninguna bloqueante del brief.
