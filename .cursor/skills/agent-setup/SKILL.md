---
name: agent-setup
description: >-
  Installs and configures ERP v2 toolchain from the project canon: Composer/npm
  libraries, shadcn/ui, Cursor/VS Code extensions, MCP, PHPStan, Pint, ESLint,
  Prettier, CI, Pest browser. Use when the user mentions setup, plugins,
  librerías, dependencias, composer require, npm install, shadcn, extensiones,
  MCP, tooling, Fase 0, Agent Setup & Settings, or environment configuration.
---

# Agent Setup & Settings

Especialista de toolchain del ERP v2. Canon: `docs/standards/libraries.md` +
`docs/standards/tooling.md`. Detalle de paquetes y extensiones: [canon.md](canon.md).

No es un rol OpenSpec. No implementa dominio.

## Antes de tocar nada

1. Leer `docs/standards/libraries.md` y `docs/standards/tooling.md`.
2. Auditar el repo (checklist abajo). **No instalar** hasta tener el delta escrito.
3. Si el paquete no está en el canon → parar. Proponer a 10-architecture que lo
   añada a `libraries.md` en el mismo PR. Nunca "de momento lo pongo".
4. Paquetes de la tabla "Por módulo" de `libraries.md` §2: solo cuando ese módulo
   o change los pida.

## Auditoría (ejecutar siempre)

Comparar estas fuentes con el canon:

- `composer.json` (`require` + `require-dev`)
- `package.json` (`dependencies` + `devDependencies`)
- `phpstan.neon` (Larastan, nivel 6 de partida)
- `pint.json` (preset `laravel`)
- `eslint.config.js` + `.prettierrc.json` (react + tailwind)
- `tsconfig.json` (`strict: true`)
- `.github/workflows/ci.yml` (pasos de `tooling.md` §4)
- `phpunit.xml` / Pest plugins
- `.vscode/` (extensiones y settings del workspace)
- `.mcp.json` (workspace: CodeGraph + Laravel Boost). Tabularis = MCP de usuario;
  no añadir conexión hasta que sea la BD de este ERP (no Hetzner/`back`)

Salida de la auditoría: tabla con columnas **pieza · estado (ok/falta/parcial) ·
cita al estándar · acción propuesta**.

## Instalación

### Composer

```bash
composer require <paquete>:<constraint-del-canon>
composer require --dev <paquete>:<constraint-del-canon>
```

Tras instalar: `vendor:publish` del paquete si aplica, alinear `.env.example`,
no dejar providers/config a medias.

### npm

```bash
npm install <paquete>@<rango-del-canon>
npm install -D <paquete>@<rango-del-canon>
```

Prohibido `npm install -g` con cwd dentro del repo (`e2e.md` §2).

### Pest browser (cuando toque Fase 0 E2E)

Seguir `docs/standards/e2e.md` §2 al pie:

```bash
composer require pestphp/pest-plugin-browser:^4.3 --dev
npm install playwright --save-dev
npx playwright install chromium
```

En Laragon/Windows: Node ≥ 18 (preferir v22 en el menú de Laragon), `ext-sockets` en
php.ini, y reabrir la terminal. El job `e2e` de CI corre `php artisan test --testsuite=Browser`.

### shadcn/ui

Base del design system (`frontend.md` §2). Se **copia** código a
`resources/js/Components/ui/`; no se instala un kit alternativo. Tokens en
`resources/css/app.css` (`@theme`). Iconos solo `lucide-react`.

## Configuración moderna (cuando el usuario lo pida)

Workspace, no settings de usuario globales, salvo que Luis pida lo contrario:

- `.vscode/extensions.json` — recomendaciones del proyecto (lista en [canon.md](canon.md)).
- `.vscode/settings.json` — Pint al guardar PHP, Prettier al guardar JS/TS,
  `intelephense.environment.phpVersion: "8.3"`, Tailwind 4, alias `@/` →
  `resources/js`.
- MCP de workspace (`.cursor/mcp.json`) solo con servidores que el usuario confirme.

No instales extensiones de editor por CLI a ciegas: recomiéndalas en
`extensions.json` para que Cursor las sugiera.

## Verificación (antes de dar por cerrado)

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse
npm run lint
npm run typecheck
composer test
composer audit
npm audit --audit-level=high
```

Rojo introducido por el setup = se arregla aquí. Rojo preexistente = se documenta,
no se "limpia" refactorizando dominio.

## Política de versiones

- Caret `^` fijando el major del canon. Locks commiteados (cuando Luis pida commit).
- Bump de major de Laravel / React / Tailwind / Tabulator = change OpenSpec, no
  un setup de paso.
- Actualizaciones de deps en PRs propios, no mezcladas con features.

## Anti-patrones (no instalar nunca)

- `yajra/laravel-datatables`, TanStack Table como listado estándar
- `stancl/tenancy` u otro paquete de tenancy
- `darryldecode/cart`
- Livewire (el front es Inertia + React)
- axios/fetch contra API propia (Inertia; excepción: ajax de Tabulator)

## Formato de respuesta

1. Tabla de auditoría (solo filas que no estén ok, más un recuento de ok).
2. Plan de acción (A transversal / B por módulo / C fuera de canon).
3. Si se instaló: comandos + configs tocadas + resultado de verificación.
4. Siguiente paso, una frase.
