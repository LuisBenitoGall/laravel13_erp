# Tasks

> Sin entidad de negocio ni `company_id`: no aplica R-TEN-05 ni V-01..V-20 de
> módulo. Alcance = assets estáticos + config + tokens + shell/UI.

## 1. Assets públicos Coopmercio

- [x] 1.1 Crear `public/images/branding/` y copiar desde
  `C:\Users\Luis\Documents\Proyectos\erp\logos\svg\` los ficheros
  `coopmercio-horizontal-color.svg` y `coopmercio-symbol-color.svg`; verificar
  que GET a ambas rutas bajo `/images/branding/` responde 200
- [x] 1.2 Copiar el contenido de `logos\favicon\` a `public/` (favicon.svg,
  favicon.ico, favicon-16/32/48/192/512.png, apple-touch-icon.png,
  site.webmanifest); ajustar el manifest a paths absolutos `/favicon-192.png`
  y `/favicon-512.png` con name/short_name Coopmercio y `theme_color`
  `#10A37F`; verificar que los ficheros existen en `public/`
- [x] 1.3 Eliminar `public/images/erp-logo.svg` si ya no hay referencias;
  verificar con búsqueda en repo que no queda default a ese path

## 2. Configuración, tokens y share Inertia

- [x] 2.1 Actualizar `config/branding.php` con `name`, `logo_url`, `mark_url` y
  `colors.primary`/`colors.secondary` (`#10A37F` / `#633D56`); verificar
  `php artisan config:show branding`
- [x] 2.2 Poner default `APP_NAME=Coopmercio` en `.env.example`; verificar que
  el ejemplo ya no dice `Laravel` como nombre de producto
- [x] 2.3 Definir en `resources/css/app.css` `@theme` los tokens `brand`,
  `brand-hover`, `brand-muted`, `brand-foreground`, `brand-ink`,
  `brand-secondary`, `brand-secondary-hover` según design; verificar que las
  utilidades `bg-brand` / `text-brand-secondary` existen tras build o en
  fuente
- [x] 2.4 Ampliar `HandleInertiaRequests` para compartir
  `branding: { name, logo_url, mark_url, colors }`; verificar con test
  feature que las props coinciden con `config('branding.*')`

## 3. Head, aside y sustitución de acentos

- [x] 3.1 Añadir en `resources/views/app.blade.php` los links de favicon SVG,
  ICO (`sizes=any`), apple-touch-icon y manifest; verificar con test
  feature/HTTP que el HTML del shell incluye esos `rel`
- [x] 3.2 Actualizar tipados Inertia de `branding` y en `AppLayout` usar
  `logo_url` (expandido/móvil abierto) y `mark_url` (rail), ERP encima de
  company; fallback de inicial con `bg-brand` en lugar de `bg-teal-700`
- [x] 3.3 Sustituir acentos de producto `teal-*` / `#0f766e` /
  `--auth-primary` en componentes UI, páginas Admin/Auth y layouts por
  tokens `brand*`; verificar con búsqueda que no quedan `teal-700` /
  `teal-50` / `teal-800` de acento de marca (salvo falsos positivos ajenos)
- [x] 3.4 Test Pest: shared branding + assets + head + presencia de hex de
  marca en tema/config; `php artisan test --compact` filtrado al archivo
  nuevo; Pint en PHP tocado

## Workflow follow-up

- Auditoría 25 → (E2E 40 solo si se añade Browser) → 30-testing → `/opsx-archive`
- Ejecutar `/opsx:apply` para implementar
