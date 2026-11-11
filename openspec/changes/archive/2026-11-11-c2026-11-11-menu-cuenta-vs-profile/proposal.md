# Proposal

## Why

El menú liga «Mi cuenta» (Functionality `profile`) a `/profile` (ficha del
User). Conceptualmente Cuenta/Mi cuenta es la **cuenta SaaS de la company**
(billing/renovaciones, futuro); la ficha propia solo se abre desde el
avatar. Hoy el lateral marca Cuenta al editar el perfil.

## What Changes

- Menú lateral: Mi cuenta → placeholder «próximamente» (no `/profile`).
- Sin enlace lateral a `/profile`; ficha self solo vía avatar (topbar).
- En `/profile*`: ningún ítem del AsideNav activo.
- Mis módulos sigue bajo Module `cuenta`.
- Breadcrumb de la ficha self: deja de presentarse como «Mi cuenta» de menú.
- Delta `menu-core-pillars` (+ ajustes shell/user-edit breadcrumb si aplica).

## Capabilities

### New Capabilities

- `account-placeholder`: placeholder Mi cuenta (cuenta SaaS company) hasta
  billing real.

### Modified Capabilities

- `menu-core-pillars`: Cuenta/Mi cuenta ya no es la ficha user; profile fuera
  del lateral hacia `/profile`.
- `user-edit`: breadcrumb/contexto nav de la ficha self (no «Mi cuenta» menú).

## Impact

- `MenuBuilder` href/label de `profile`; posible ruta + página placeholder.
- `AsideNav` (sin highlight en `/profile*` si hiciera falta).
- Breadcrumb en `Users/Edit` context profile.
- Specs menú; tests Feature/browser-light de menú JSON / assertions front.
- Docs Admin / vision menú si citan Mi cuenta = perfil.
