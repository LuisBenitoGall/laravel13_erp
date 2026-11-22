# Design

## Context

`UserProfileReadout` ya cubre la ficha RO. Indexes `CompanyUsers/Index` y
`AllUsers/Index` usan ServerTable sin panel. No hay Sheet en `Components/ui`.
Payload completo de ficha vive en `UserEditPageData` (Edit). Brief:
`docs/prompts/c2026-11-22-user-list-quick-view.md`.

Sin módulo nuevo. E2E §4: N/A. V-01..V-20 entidad nueva: N/A.

## Goals / Non-Goals

**Goals:**

- Trigger ojo + tooltip i18n a la izquierda del nombre.
- Panel derecho reutilizable con readout; switch de user sin unmount de
  cierre; close = solo X.
- Company-users: `can_edit` → botón Editar a `/admin/company-users/{id}/edit`.
- All-users: `can_edit: false` siempre en UI.
- JSON quick por contexto con authz correcta.

**Non-Goals:**

- Cierre por overlay/Escape; panel v1 rico; RO empresa; mass/row actions.

## Decisions

1. **Contenedor UI: shadcn `Sheet` (lado right)**  
   Añadir `Components/ui/sheet` (radix Dialog primitives, ya en canon shadcn).
   Configurar `onInteractOutside` / `onPointerDownOutside` →
   `preventDefault()` para **no** cerrar al clic fuera. Escape: también
   prevenir cierre (brief: solo X). Overlay MAY existir visualmente pero no
   cierra.  
   Alternativa descartada: panel fixed custom sin primitiva — más CSS ad hoc;
   Sheet alinea al design system.

2. **Componente `UserQuickViewPanel`**  
   Props: `open`, `onClose`, `loading`, payload readout, `editHref?: string |
   null`. Cabecera: título i18n «Usuario», X, nombre, Editar si `editHref`.
   Cuerpo: `UserProfileReadout`. Compartido por ambos Indexes.

3. **Carga de datos**  
   - `GET /admin/company-users/{user}/quick` → JSON  
     `{ user, emails, phones, companies, avatar_url, can_edit }`  
     Authz: `viewCompanyUser` / vínculo a company de sesión; `can_edit` =
     `updateCompanyUser`.  
   - `GET /admin/all-users/{user}/quick` → mismo shape sin `can_edit` útil
     (siempre false) o `can_edit: false`. Authz: `viewAnyAllUser` + target
     `is_public = true`; 404/403 si no público.  
   Helper `UserQuickViewData` (o método en `UserEditPageData`) que proyecta
   solo campos del readout (sin form/can tabs).  
   Front: `useHttp` / fetch JSON al abrir y al cambiar `userId` (panel open).  
   Alternativa descartada: embeber emails/phones en `/table` — pesa y
   acopla listado.

4. **Trigger en columna nombre**  
   Formatter Tabulator: botón icono Eye (`title`/`aria-label` = i18n ver
   detalle) + texto nombre. `stopPropagation` en click. Index registra
   `onQuickView(row)`.

5. **Estado Index**  
   `quickUserId: number | null`; al setear id se abre panel y fetch; al
   cambiar id con panel abierto solo refetch; `onClose` → null.  
   **Importante:** el estado del panel vive en el Index junto a ServerTable;
   `rowActions` / `columns` / `massActions` MUST tener identidad estable
   (`useCallback`/`useMemo`). Si no, `ServerTable` recrea Tabulator al abrir
   la ficha y vuelve a pedir `/table` (bug observado en apply).

6. **Standards**  
   Documentar Sheet + `UserQuickViewPanel` en `frontend.md` catálogo §4.

## Risks / Trade-offs

- [Sheet/radix cierra con Escape por defecto] → Mitigation: handlers que
  previenen dismiss excepto X.
- [All-users ve datos de contactos de users públicos] → Mitigation: ya
  listables en directorio; mismo perímetro `is_public`.
- [Race al cambiar rápido de user] → Mitigation: abort/ignore response si
  `userId` ya no coincide.
- [Abrir ficha recarga la tabla] → Mitigation: callbacks de Index estables
  para no invalidar deps de recreate de ServerTable (task 3.4).

## Migration Plan

1. Sheet + panel.  
2. Helper + routes quick.  
3. Indexes.  
4. Tests. Sin BD.

## Open Questions

Ninguna bloqueante.
