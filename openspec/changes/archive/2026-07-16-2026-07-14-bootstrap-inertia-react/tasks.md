## 1. Verificación de entorno

- [x] 1.1 Confirmar Node ≥ 20 activo en Laragon (riesgo documentado en
      `docs/standards/e2e.md`) antes de instalar o ejecutar nada
- [x] 1.2 Confirmar que no hace falta añadir dependencias nuevas: todo ya está en
      `composer.json` (`inertiajs/inertia-laravel`) y `package.json`
      (`@inertiajs/react`, `tailwindcss`, `@tailwindcss/vite`, `vite`,
      `laravel-vite-plugin`)
      > Nota (20-implementation): el design asumía deps ya declaradas, pero
      > `package.json` no tenía `react`/`react-dom`/`typescript` ni tooling de
      > calidad. Se añadieron del canon Fase 0 (`libraries.md` + `tooling.md`):
      > `react`, `react-dom`, `typescript`, `@types/react`, `@types/react-dom`,
      > `@vitejs/plugin-react`, ESLint 9 + plugins React/TS, Prettier.
      > ESLint se pinó a ^9 (peer de `eslint-plugin-react`; ESLint 10 no compatible).

## 2. Backend: shell Inertia y middleware

- [x] 2.1 Crear `resources/views/app.blade.php` como shell único del proyecto (patrón
      oficial de Inertia, sin contenido de negocio)
- [x] 2.2 Registrar `HandleInertiaRequests` en el grupo de middleware `web`
      (`bootstrap/app.php`)
- [x] 2.3 Actualizar `routes/web.php`: la ruta raíz sirve `Inertia::render('Welcome')`
      en vez de `view('welcome')`
- [x] 2.4 Retirar `resources/views/welcome.blade.php` (ya no se sirve)

## 3. Frontend: TypeScript + Inertia + React

- [x] 3.1 Crear `tsconfig.json` con `strict: true` (R-FE-01)
- [x] 3.2 Sustituir `resources/js/app.js` por `resources/js/app.tsx` con
      `createInertiaApp` montando React sobre el `id` del shell Blade
- [x] 3.3 Crear `resources/js/Pages/Welcome.tsx` — placeholder mínimo (smoke test), sin
      lógica ni componentes de negocio
- [x] 3.4 Confirmar que Tailwind 4 (`@tailwindcss/vite`) aplica estilos reales en
      `Welcome.tsx`

## 4. Calidad estática del frontend

- [x] 4.1 Configurar ESLint (preset React + TypeScript)
- [x] 4.2 Configurar Prettier
- [x] 4.3 `tsc --noEmit` y ESLint en verde sobre el código creado en este change

## 5. Verificación end-to-end (Definition of Done de este change)

- [x] 5.1 `composer dev` arranca servidor Laravel + Vite a la vez, sin errores
      > Nota (20-implementation): `composer run dev` falla en Windows porque
      > `php artisan pail` exige `pcntl` (no disponible en PHP Windows) y
      > `--kill-others` tumba el resto. Verificado el objetivo (Laravel + Vite a
      > la vez) con `php artisan serve` + `npm run dev`. Escalar a
      > 10-architecture si se quiere adaptar el script `dev` a Windows.
- [x] 5.2 Visitar `/` en el navegador: se ve la página React (no
      `welcome.blade.php`), con estilos Tailwind 4 aplicados — cubre el escenario
      "Página de arranque visible" de `specs/frontend-bootstrap/spec.md`
- [x] 5.3 Editar `Welcome.tsx` y confirmar hot-reload en el navegador — cubre el
      escenario "Entorno de desarrollo funcional"
- [x] 5.4 Confirmar que no se ha tocado ningún dominio de negocio, modelo, migración,
      ni `ssr.tsx` — respeta los Non-Goals de `design.md`
