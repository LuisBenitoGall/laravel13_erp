## Why

El esqueleto de Laravel 13 no tiene todavía ningún cableado real de Inertia+React:
`resources/js/app.js` está vacío (`//`) y la ruta raíz sirve la vista Blade de fábrica
(`welcome.blade.php`). Ningún otro change de la Fase 0 (kernel/tooling, módulo Admin) se
puede verificar en navegador mientras esto no exista — es el prerrequisito literal de todo
el proyecto (Capa -1).

## What Changes

- Sustituir `resources/js/app.js` por un `app.tsx` que monta React sobre Inertia.
- Crear `resources/views/app.blade.php` como **único** shell Blade del proyecto (todo lo
  demás son páginas React).
- Activar `HandleInertiaRequests` (ya existe en `app/Http/Middleware/`) en el stack de
  middleware de la aplicación.
- Servir al menos una página real vía `Inertia::render()` desde una ruta Laravel,
  sustituyendo `view('welcome')` en la ruta raíz.
- Configurar TypeScript en modo estricto (`tsconfig.json`, R-FE-01).
- Confirmar que Tailwind 4 (`@tailwindcss/vite`) compila y aplica estilos reales sobre esa
  página.
- Configurar ESLint + Prettier para el frontend, en verde.
- **BREAKING**: la ruta raíz deja de servir `welcome.blade.php`.

## Capabilities

### New Capabilities
- `frontend-bootstrap`: arranque real de Inertia v2 + React 19 + TypeScript estricto +
  Tailwind 4 sobre Laravel 13, sirviendo al menos una página real de punta a punta (sin
  SSR, sin dominios de negocio).

### Modified Capabilities

(ninguna — no hay specs previas en el proyecto; `openspec/specs/` está vacío)

## Impact

- `resources/js/app.js` → `resources/js/app.tsx` + primera página en `resources/js/Pages/`.
- `resources/views/welcome.blade.php` → `resources/views/app.blade.php` (shell único).
- `routes/web.php`: la ruta raíz pasa de `view('welcome')` a `Inertia::render(...)`.
- Middleware: `HandleInertiaRequests` se registra en `bootstrap/app.php`.
- Configuración nueva: `tsconfig.json`, ESLint, Prettier — sin dependencias nuevas (todo ya
  está en `composer.json`/`package.json`, solo falta cablearlo).
- Ningún dominio de negocio, modelo ni migración afectados.
