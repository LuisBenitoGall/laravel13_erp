# Design

## Context

El aside de `AppLayout` ya reserva dos franjas (ERP encima, company debajo) y
consume `branding.logo_url` + `tenant.logo_url` vía `HandleInertiaRequests`.
`config/branding.php` apunta al placeholder `/images/erp-logo.svg`;
`app.blade.php` no declara favicon; `public/favicon.ico` es el de Laravel.
La UI usa acentos Tailwind `teal-*` y auth `--auth-primary: #0f766e`.
Fuente de assets: kit Coopmercio en
`C:\Users\Luis\Documents\Proyectos\erp\logos` (ver `LEEME.txt`: colores
planos `#10A37F` / `#633D56`; favicon/, svg/, png/).

Motivación: ver `proposal.md` — Why.

## Goals / Non-Goals

**Goals:**

- Copiar al `public/` del proyecto solo los assets necesarios (no el kit entero
  con preview/).
- Favicon + apple-touch + manifest operativos en todas las páginas Inertia.
- Aside expandido y aside móvil abierto: wordmark horizontal color por encima
  del logo/nombre de company.
- Aside rail (md+ colapsado): símbolo color del ERP (y company reducido como hoy).
- Nombre de producto Coopmercio en `APP_NAME` / título de documento.
- Shared props tipados: `branding.logo_url` (ampliado) y `branding.mark_url`
  (símbolo).
- Tokens de marca en `@theme`: primary `#10A37F`, secondary `#633D56`, más
  hover/muted derivados; sustituir usos de acento `teal-*` / `#0f766e` del
  producto por esos tokens.

**Non-Goals:**

- Rediseño estructural del sliding login/register (layout/animación); solo
  alinear color a la marca.
- Cambiar upload/storage del logo de company ni el contrato `tenant.logo_url`.
- Theming por tenant o por módulo más allá del `color`/`icon` ya existente
  (R-AUT-07).
- PWA instalable completa, service worker, splash screens.
- i18n del nombre de marca (Coopmercio es nombre propio).
- Regenerar una escala Tailwind completa `50–950` de teal; basta primary,
  hover, muted, foreground y secondary (+ hover).

## Decisions

1. **Inventario mínimo en `public/`**
   - Favicon kit → raíz pública: `favicon.svg`, `favicon.ico`,
     `favicon-16/32/48/192/512.png`, `apple-touch-icon.png`, `site.webmanifest`
     (rutas absolutas `/…` en el manifest; `theme_color` `#10A37F`).
   - Branding UI → `public/images/branding/`:
     - `coopmercio-horizontal-color.svg` (aside expandido / móvil abierto)
     - `coopmercio-symbol-color.svg` (rail)
   - Default = SVG; sin preview/ ni variantes blanco/negro en inventorio UI.
   - Alternativa descartada: servir desde storage o CDN.

2. **`config/branding.php`**
   ```php
   'name' => env('APP_BRAND_NAME', 'Coopmercio'),
   'logo_url' => env('APP_LOGO_URL', '/images/branding/coopmercio-horizontal-color.svg'),
   'mark_url' => env('APP_MARK_URL', '/images/branding/coopmercio-symbol-color.svg'),
   'colors' => [
       'primary' => '#10A37F',
       'secondary' => '#633D56',
   ],
   ```
   - `APP_NAME` default / `.env.example` → `Coopmercio`.
   - Colores canónicos en config (documentación / posible share); la fuente
     operativa del front es `@theme` en CSS (no runtime theming).

3. **Tokens CSS (`resources/css/app.css` `@theme`)**
   | Token | Hex | Uso |
   |---|---|---|
   | `--color-brand` | `#10A37F` | primary (botones, focus, links, rail fallback) |
   | `--color-brand-hover` | `#0D8A6B` | hover/active primary |
   | `--color-brand-muted` | `#E7F6F1` | fondos suaves (ex-teal-50) |
   | `--color-brand-foreground` | `#FFFFFF` | texto sobre primary |
   | `--color-brand-ink` | `#0A5C4A` | texto primary sobre fondo claro (ex-teal-800) |
   | `--color-brand-secondary` | `#633D56` | acento secundario / énfasis |
   | `--color-brand-secondary-hover` | `#4E2F43` | hover secondary |

   Utilidades Tailwind 4: `bg-brand`, `text-brand`, `border-brand`,
   `ring-brand/30`, `bg-brand-muted`, `text-brand-ink`, `bg-brand-secondary`, etc.

   Alternativa descartada: redefinir la escala `teal-*` entera — acoplaría el
   nombre “teal” a un verde de marca y confundiría lecturas futuras.

4. **Sustitución de acentos**
   - Reemplazar en TSX/CSS del producto las clases `teal-50/700/800` y vars
     `--auth-primary` / `#0f766e` por tokens `brand*`.
   - `AuthLayout`: `--auth-primary: var(--color-brand)` (o hex `#10A37F`).
   - No tocar colores semánticos ajenos (rojo error, ámbar warning, post-its
     dashboard, `Module.color` por ítem de menú).

5. **Share Inertia**
   - `branding`: `{ name, logo_url, mark_url, colors?: { primary, secondary } }`.
   - `AppLayout`: rail → `mark_url`; expandido/móvil abierto → `logo_url`.

6. **Head / favicon** — links según `LEEME.txt` en `app.blade.php`.

7. **Placeholder** — dejar de referenciar / borrar `public/images/erp-logo.svg`.

8. **Tests**
   - Feature: head con `rel=icon` + shared branding paths.
   - Assertion ligera: `app.css` (o config) declara `#10A37F` / `#633D56`.
   - Smoke visual opcional.

## Risks / Trade-offs

- [Muchas ocurrencias `teal-*`] → Mitigation: búsqueda global + replace
  sistemático a tokens `brand*`; Pint/ESLint no cubren clases CSS.
- [Contraste secondary `#633D56` sobre blanco] → Mitigation: secondary solo
  como acento/texto; primary sigue siendo la acción principal.
- [Auth-sliding change en paralelo] → Mitigation: este change fija el contrato
  de color; si auth-sliding aún hardcodea teal, se alinea aquí o en apply
  conjunto.
- [Rutas absolutas `/images/...`] → Mitigation: vhost Laragon en raíz.

## Migration Plan

1. Copiar assets + manifest (`theme_color` `#10A37F`).
2. Tokens `@theme` + `config/branding.php` + `.env.example`.
3. Blade head + HandleInertiaRequests + AppLayout.
4. Sustituir acentos `teal-*` / auth primary por `brand*`.
5. Borrar placeholder; tests; Pint en PHP tocado.

Rollback: revertir commit de assets/config/CSS/UI.

## Open Questions

Ninguna bloqueante.
