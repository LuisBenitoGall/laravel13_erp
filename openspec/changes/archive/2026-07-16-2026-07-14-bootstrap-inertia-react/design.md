## Context

El esqueleto Laravel 13 actual no tiene ningún cableado real de Inertia+React:
`resources/js/app.js` contiene solo un comentario (`//`), `routes/web.php` sirve
`view('welcome')` en la raíz, `HandleInertiaRequests` existe (`app/Http/Middleware/`) pero
no está registrado en ningún grupo de middleware, y no hay `resources/views/app.blade.php`.
Las dependencias necesarias ya están instaladas (`composer.json`:
`inertiajs/inertia-laravel` ^3.1; `package.json`: `@inertiajs/react` ^3.6.1,
`tailwindcss`/`@tailwindcss/vite` ^4, `vite` ^8, `laravel-vite-plugin` ^3.1) — no hace
falta añadir dependencias, solo cablearlas.

Stack obligatorio (`CLAUDE.md` / `.cursor/rules/00-project.mdc`): Laravel 13/PHP 8.3+,
Inertia v2 + React 19 + TypeScript estricto (R-FE-01) + Tailwind 4, Node 20+.

Este change es la Capa -1 de la Fase 0: prerrequisito de los changes
`kernel-tooling-fase0` (Capa 0) y `admin-modules-functionality` (Capa 1+2), decidido y
secuenciado en sesión de explore previa a esta propuesta.

## Goals / Non-Goals

**Goals:**
- Arranque real de Inertia: `app.tsx` montando React, `resources/views/app.blade.php`
  como único shell Blade del proyecto, `HandleInertiaRequests` activo.
- Al menos una página React real servida vía `Inertia::render()`, visible con
  `composer dev` (servidor Laravel + Vite a la vez).
- TypeScript en modo estricto (`tsconfig` `strict: true`) desde el primer `.tsx`.
- Tailwind 4 compilando y aplicando estilos reales en esa página.
- ESLint + Prettier configurados y en verde para el frontend.

**Non-Goals:**
- SSR (`ssr.tsx`): fuera de alcance; se añadirá más adelante sin romper el CSR.
- Cualquier dominio de negocio, modelo o migración más allá del esqueleto de fábrica.
- El "kernel invisible" (`BelongsToCompany`, `CurrentCompany`, `TableRequest`/
  `TableBuilder`, `NumberingService`), el generador `make:domain-entity` y el backstop de
  completitud — corresponden al change `kernel-tooling-fase0`.
- `AppLayout`, `ServerTable`, kit de formularios o cualquier componente del catálogo de
  `frontend.md` — corresponden al change `admin-modules-functionality`. La página de
  prueba de este change es un placeholder mínimo, no una pantalla de negocio.
- PHPStan/Pint (calidad estática PHP): ya existen en el esqueleto, no dependen de este
  change.

## Decisions

1. **Punto de montaje único `app.tsx` con `createInertiaApp`** (patrón oficial de
   Inertia v2). Alternativa descartada: mantener `app.js` sin tipos — incompatible con
   R-FE-01 (TypeScript estricto obligatorio desde el primer componente).
2. **`resources/views/app.blade.php` como único Blade del proyecto**, sustituyendo
   `welcome.blade.php` como vista servida. Alternativa descartada: convivir ambos
   ficheros — contradice la regla ya fijada en `module-playbook.md` ("no hay vistas
   Blade por módulo, el único Blade es la vista raíz de Inertia").
3. **Registro de `HandleInertiaRequests` en `bootstrap/app.php`** (Laravel 11+/13 ya no
   usa `app/Http/Kernel.php`): se añade al grupo `web`.
4. **ESLint + Prettier + `tsconfig` estricto entran en ESTE change**, no en
   `kernel-tooling-fase0` — decisión tomada explícitamente en la sesión de explore de
   origen: es la calidad estática del código que este mismo change introduce: no tiene
   sentido escribir el primer `.tsx` sin esas herramientas activas.
5. **Página de prueba = placeholder mínimo** (p. ej. `Pages/Welcome.tsx` servida en `/`),
   no el futuro Dashboard de Admin — evita adelantar trabajo del change
   `admin-modules-functionality`.
6. **SSR explícitamente fuera de alcance** (Non-Goal): no se crea `ssr.tsx` en este change.

## Risks / Trade-offs

- **[Riesgo]** Confusión de versión de Node activa en Laragon (histórico documentado en
  `docs/standards/e2e.md`) → **Mitigación**: verificar `node -v` ≥ 20 antes de
  `npm install`/`npm run dev`, como primer paso de implementación.
- **[Riesgo]** Vite 8 (basado en Rolldown) es reciente; posible fricción con
  `laravel-vite-plugin` → **Mitigación**: usar las versiones ya fijadas en
  `package.json` (no actualizar de partida); si aparece un bloqueo real, documentarlo y
  escalar a `10-architecture` antes de forzar un downgrade no acordado.
- **[Riesgo]** Que el nuevo `app.blade.php` acumule contenido de negocio con el tiempo →
  **Mitigación**: mantenerlo como shell mínimo (equivalente al ejemplo oficial de
  Inertia), sin lógica ni marcado de dominio.

## Migration Plan

No hay datos de producción que migrar (proyecto sin usuarios todavía). Despliegue: build
de assets (`npm run build`) antes de cualquier entorno que no sea `composer dev` en
local. Sin estrategia de rollback especial más allá de revertir el commit: es un cambio
de bootstrap sin estado persistente.

## Open Questions

Ninguna: las dos ambigüedades de alcance detectadas en la sesión de explore (encaje de
ESLint/tsconfig-estricto y encaje de SSR) ya se resolvieron explícitamente antes de esta
propuesta (ver Goals/Non-Goals).
