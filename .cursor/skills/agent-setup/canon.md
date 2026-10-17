# Canon de setup — referencia rápida

Fuente: `docs/standards/libraries.md`, `docs/standards/tooling.md`,
`docs/standards/e2e.md`, `docs/standards/frontend.md`. Si chocan, manda el
estándar, no este resumen.

## Backend — ya esperado en el esqueleto

| Paquete | Constraint |
|---|---|
| `laravel/framework` | ^13 |
| `inertiajs/inertia-laravel` | ^3 |
| `laravel/tinker` | * |
| `laravel/pint` | require-dev |

## Backend — transversales Fase 0

| Paquete | Constraint / nota |
|---|---|
| `spatie/laravel-permission` | ^8 (Teams) |
| `spatie/laravel-translatable` | ^6 |
| `spatie/laravel-activitylog` | auditoría R-TRV-02 |
| `laravel/reverb` + `laravel-echo` / `pusher-js` | realtime |
| `laravel/sanctum` | API tokens V-19 |
| `barryvdh/laravel-dompdf` | PDF; evaluar `spatie/laravel-pdf` más adelante |
| `maatwebsite/excel` | ^3 |
| `laravel/horizon` | opcional, solo si Redis |

## Backend — calidad require-dev Fase 0

| Paquete | Nota |
|---|---|
| `pestphp/pest` ^4 + `pest-plugin-laravel` | tests |
| `pestphp/pest-plugin-arch` | arch tests |
| `pestphp/pest-plugin-browser` + `playwright` (npm) | E2E |
| `larastan/larastan` | PHPStan nivel 6 |
| `laravel/boost` | MCP `boost:mcp`; no `boost:install` que pise CLAUDE.md |
| `barryvdh/laravel-ide-helper` | opcional, DX |

## Frontend — núcleo

`@inertiajs/react` ^3, `tailwindcss` ^4 + `@tailwindcss/vite`, `vite` ^8,
`laravel-vite-plugin`, `react` / `react-dom` 19, `typescript` (strict).

## Frontend — transversales Fase 0

| Paquete | Uso |
|---|---|
| `tabulator-tables` + tipos | `<ServerTable>` |
| `react-hook-form` + `zod` + `@hookform/resolvers` | formularios |
| shadcn/ui (`radix-ui`, `class-variance-authority`, `clsx`, `tailwind-merge`) | design system |
| `lucide-react` | únicos iconos |
| `zustand` | estado global mínimo |
| `date-fns` | fechas es-ES |
| `laravel-echo` + `pusher-js` | cliente Reverb |
| `i18next` + `react-i18next` | i18n React |
| `sonner` | toasts |

## Frontend — por módulo (no adelantar)

`@tiptap/react` (WYSIWYG), `react-dropzone` (adjuntos), `@dnd-kit/core`
(LineItemsEditor / kanban), `recharts` (dashboard Fase 6).

## Calidad estática y CI

- Pint: preset `laravel`.
- PHPStan/Larastan: `phpstan.neon` nivel 6 (subir a 8 cuando la base sea estable).
- ESLint + Prettier: preset react + **tailwind**.
- TypeScript: `strict: true`, `npm run typecheck`.
- CI: composer + npm ci → Pint → PHPStan → ESLint → tsc → Pest (MySQL strict) →
  `npm run build` → Pest browser (stage aparte) → `composer audit` +
  `npm audit` (high = fail).

## Extensiones recomendadas (workspace, no instalar a ciegas)

Cuando el usuario pida `.vscode/extensions.json`:

- `laravel.vscode-laravel` — Laravel
- `bmewburn.vscode-intelephense-client` — PHP
- `open-southeners.laravel-pint` — Pint on save
- `bradlc.vscode-tailwindcss` — Tailwind 4
- `esbenp.prettier-vscode` — Prettier
- `dbaeumer.vscode-eslint` — ESLint
- `yoavbls.pretty-ts-errors` — errores TS legibles

No recomendar extensiones que dupliquen Intelephense (p. ej. PHP IntelliSense de
Felix) ni temas/packs de relleno.

## Entorno local (Laragon / Windows)

- PHP 8.3+, MySQL strict ON, Node 20+ (preferir 22 en el menú de Laragon).
- Playwright: reabrir terminal tras cambiar Node; nunca `npm i -g` dentro del repo.
- App local típica: `http://laravel13_erp.test` (no asumir `localhost` si Laragon
  sirve el vhost).
