## ADDED Requirements

### Requirement: Arranque real de Inertia + React + TypeScript + Tailwind
El sistema SHALL servir al menos una página mediante `Inertia::render()` desde Laravel,
renderizada por un componente React real en TypeScript, con Tailwind 4 aplicando estilos,
sin pasar por ninguna vista Blade de contenido (Blade únicamente como shell raíz único del
proyecto).

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
