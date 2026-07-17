Change: 2026-07-14-bootstrap-inertia-react
Rule: 10-architecture
Summary: Encender el stack real de frontend (Inertia v2 + React 19 + TypeScript estricto +
Tailwind 4) sobre el esqueleto Laravel 13 actual, hasta lograr que una página real
renderice de punta a punta. Es la Capa -1 de Fase 0: prerrequisito de cualquier otro
change (kernel, generador, Admin); no toca lógica de negocio ni dominios.
Goals:
- Sustituir el arranque actual (resources/js/app.js vacío "//" + routes/web.php sirviendo
  welcome.blade.php) por un arranque real de Inertia: app.tsx montando React,
  resources/views/app.blade.php como único shell Blade de todo el proyecto,
  HandleInertiaRequests activo en el stack de middleware.
- Al menos una página React (.tsx) real, servida vía Inertia::render() desde una ruta de
  Laravel, visible en el navegador con composer dev (server + vite) funcionando a la vez.
- TypeScript en modo estricto (tsconfig strict:true, R-FE-01) desde el primer .tsx.
- Tailwind 4 compilando y aplicando estilos reales en esa página (prueba de que
  @tailwindcss/vite está bien enganchado).
- ESLint + Prettier configurados y en verde (decisión de esta sesión: entran en ESTE
  change, no en el de tooling).
NonGoals:
- SSR (ssr.tsx) — decisión explícita de esta sesión: fuera de alcance; se añadirá más
  adelante sin romper el CSR.
- Cualquier dominio de negocio, modelo o migración más allá de lo que ya trae el esqueleto
  Laravel de fábrica.
- El "kernel invisible" (BelongsToCompany, CurrentCompany, TableRequest/TableBuilder,
  NumberingService), el generador make:domain-entity y el backstop de completitud — eso es
  el change "kernel-tooling-fase0" (change 2 de la Fase 0).
- AppLayout, ServerTable, kit de formularios o cualquier componente del catálogo de
  frontend.md — eso es el change "admin-modules-functionality" (change 3). La página de
  prueba de ESTE change es un placeholder mínimo, no una pantalla de negocio real.
- PHPStan/Pint (calidad estática del lado PHP): ya existen en el esqueleto y no dependen de
  este change.
Skills:
- vite
- tailwind-css-patterns
- laravel-patterns
Notes:
- Estado verificado en sesión de explore (2026-07-14): resources/js/app.js solo contiene
  "//"; routes/web.php sirve view('welcome'); HandleInertiaRequests existe pero no está
  enganchado a ningún middleware group; no existe resources/views/app.blade.php; no hay
  carpeta Pages ni tipos TS.
- Dependencias ya instaladas, no hace falta añadir nada nuevo, solo cablear: composer.json
  (inertiajs/inertia-laravel ^3.1, laravel/framework ^13.8), package.json
  (@inertiajs/react ^3.6.1, tailwindcss ^4, @tailwindcss/vite ^4, vite ^8,
  laravel-vite-plugin ^3.1).
- Stack obligatorio (00-project.mdc / CLAUDE.md): Node 20+, React 19, TypeScript estricto.
- Riesgo de entorno conocido (Laragon): confirmar la versión de Node activa antes de
  ejecutar npm/vite (histórico documentado en docs/standards/e2e.md).
- Es el change 1 de 3 de la Fase 0, decidido en esta sesión de explore: 1) bootstrap-
  inertia-react (este), 2) kernel-tooling-fase0, 3) admin-modules-functionality (con
  Module/Functionality/CompanyModules, construido con el generador del change 2).


## ADDED Requirements

### Requirement: Arranque real de Inertia + React + TypeScript + Tailwind
El proyecto debe servir al menos una página mediante Inertia::render() desde Laravel,
renderizada por un componente React real en TypeScript, con Tailwind 4 aplicando estilos,
sin pasar por ninguna vista Blade de contenido (Blade solo como shell raíz único).

#### Scenario: Página de arranque visible
- **WHEN** un usuario visita la ruta raíz (`/`) de la aplicación
- **THEN** el servidor responde con una página Inertia servida por React (no
  `welcome.blade.php`), compilada con TypeScript estricto y con estilos Tailwind 4
  aplicados

#### Scenario: Entorno de desarrollo funcional
- **WHEN** se ejecuta `composer dev`
- **THEN** arrancan a la vez el servidor Laravel y Vite, y los cambios en un componente
  `.tsx` se reflejan en caliente en el navegador

#### Scenario: Calidad estática mínima en verde
- **WHEN** se ejecuta el typecheck/lint del frontend (`tsc --noEmit`, ESLint)
- **THEN** no hay errores, con `tsconfig` en modo `strict: true`

---

## MODIFIED Requirements

No aplica — es el primer change del proyecto, no hay capability previa que modificar.
