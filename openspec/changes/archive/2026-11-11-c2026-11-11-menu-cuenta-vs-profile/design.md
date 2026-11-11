# Design

## Context

See proposal.md — Why. Functionality `profile` bajo Module `cuenta` apunta a
`route('profile.show')` (`MenuBuilder::hrefFor`). AsideNav activa por
`pathMatches`. Breadcrumb ficha self: `optionKey: 'mi_cuenta'`. Avatar
`UserMenu` → `/profile` (correcto y se mantiene).

## Goals / Non-Goals

**Goals:** desacoplar Mi cuenta de ficha user; placeholder próximamente;
lateral sin `/profile`; `/profile*` sin highlight; Mis módulos intacto;
breadcrumb self alineado.

**Non-Goals:** billing/renovaciones reales; mover Mis módulos; CRUD ficha
user; highlight Usuarios en profile.

**E2E:** no aplica (`e2e.md` §4) salvo que se considere menú crítico — **no**
está en la lista §4; Feature + aserciones menú JSON bastan.

**Excepciones a standards:** ninguna.

## Decisions

1. **Slug Functionality `profile`**  
   Se **conserva** el slug `profile` (menos churn Spatie/`profile.view`) pero
   su semántica de menú pasa a «Mi cuenta» = cuenta SaaS company
   (placeholder). La ruta de ficha user sigue siendo `/profile*` fuera del
   menú.  
   *Alt. descartada:* renombrar slug a `account` ahora (migrar permisos;
   diferible al change de billing).

2. **Href Mi cuenta**  
   Nueva ruta nombrada p. ej. `account.show` → `/account` (o `/mi-cuenta`)
   que renderiza página Inertia mínima «próximamente» (cuenta company /
   renovaciones). `MenuBuilder::hrefFor('profile')` apunta ahí, **no** a
   `profile.show`. Visibilidad menú: sigue `cuenta.access` + `profile.view`.

3. **Lateral sin enlace a ficha user**  
   Ningún otro slug enlaza a `/profile`. Tras (2), el menú JSON no contiene
   href `/profile`.

4. **Sin highlight en `/profile*`**  
   Con (2), `pathMatches` no marca Cuenta. Defensa: en `AsideNav`, si el
   path es `/profile` o prefijo `/profile/`, forzar **ningún** activo
   (`itemIsActive` / `pathMatches` early-return false para no casar hijos).
   Cubre futuros ítems que accidentalmente coincidan.

5. **Breadcrumb ficha self**  
   En `Users/Edit` context `profile`: dejar `mi_cuenta` / optionHref menú.
   Usar p. ej. `optionKey: 'perfil'`, `suboptionKey` acorde (dashboard o
   edición), `optionHref: '/profile'` solo como ancla de breadcrumb (no
   ítem de menú). i18n clave `perfil` si falta.

6. **Avatar**  
   Sin cambio de destino: sigue `/profile`. Label del ítem puede seguir
   «Perfil».

7. **Capability `account-placeholder`**  
   Spec del placeholder; `menu-core-pillars` MODIFIED para el pilar Cuenta;
   `user-edit` MODIFIED solo breadcrumb/nav presentation si el requirement
   de tabs no lo cubre — añadir escenario o requirement breve de breadcrumb
   self ≠ Mi cuenta menú.

8. **Docs**  
   Admin.md / cualquier texto «Mi cuenta → profile ficha».

## Risks / Trade-offs

- [slug `profile` confuso] → documentar; rename en billing.  
- [doble concepto profile ruta vs Functionality] → aceptable corto plazo.  
- [placeholder sin permiso nuevo] → reutiliza `profile.view` para ver el
  ítem; OK hasta billing.

## Migration Plan

1. Ruta + página placeholder; MenuBuilder href.  
2. AsideNav guard `/profile*`.  
3. Breadcrumb Edit profile.  
4. Specs/docs/tests.  
Rollback: revertir href a `profile.show` (no deseable).

## Open Questions

Ninguna.
