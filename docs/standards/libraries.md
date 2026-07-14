# Canon de librerías

> Documento normativo. **No se añade ninguna dependencia que no esté aquí**; si un módulo
> necesita una nueva, se justifica en su ficha y se añade al canon en el mismo PR
> (playbook §1). Antes de añadir: comprobar mantenimiento activo, compatibilidad Laravel 13 /
> React 19, y que no exista ya algo en el canon que lo cubra.

## 1. Backend (Composer)

### Núcleo (ya instalado)

| Paquete | Uso |
|---|---|
| `laravel/framework` ^13 | Framework (PHP ^8.3) |
| `inertiajs/inertia-laravel` ^3 | Adaptador Inertia |
| `laravel/tinker` | REPL |

### Transversales (instalar en Fase 0)

| Paquete | Uso | Reglas asociadas |
|---|---|---|
| `spatie/laravel-permission` ^6 | Roles y permisos por empresa | R-AUT-* |
| `spatie/laravel-translatable` | Datos traducibles por registro (JSON) | R-BD-08 |
| `spatie/laravel-activitylog` | Auditoría transversal | R-TRV-02 |
| `laravel/reverb` + `laravel/echo` (front) | Realtime: notificaciones, chat | — |
| `laravel/sanctum` | API tokens (integraciones) | V-19 |
| `barryvdh/laravel-dompdf` | PDF (compatibilidad plantillas v1); evaluar `spatie/laravel-pdf` más adelante | V-05 |
| `maatwebsite/excel` ^3 | Import/export Excel | V-05 |
| `laravel/horizon` (opcional, si Redis) | Panel de colas | R-TRV-01 |

### Calidad (require-dev, Fase 0)

| Paquete | Uso |
|---|---|
| `pestphp/pest` ^4 + `pest-plugin-laravel` | Framework de tests (sustituye a PHPUnit puro del esqueleto) |
| `pestphp/pest-plugin-arch` | Tests de arquitectura ([tooling.md](tooling.md)) |
| `larastan/larastan` | PHPStan para Laravel |
| `laravel/pint` (ya instalado) | Formato de código |
| `barryvdh/laravel-ide-helper` (opcional) | Autocompletado de modelos |

### Reglas de lo que NO se usa

- **Nada de yajra/datatables** (v1): los listados usan el contrato de tabla propio (frontend.md §3).
- **Nada de paquetes de tenancy** (stancl/tenancy...): la tenancy es propia (BD única + scope), decidido en vision §4.5.
- **SEPA**: sin librería externa; se portan los builders propios de v1 (pain.008/pain.001 + XSD) con sus tests.
- **Carrito**: sin `darryldecode/cart` (v1); el carrito del portal es dominio propio (Portal).

## 2. Frontend (npm)

### Núcleo (ya instalado)

| Paquete | Uso |
|---|---|
| `@inertiajs/react` ^3 | SPA sin API propia |
| `tailwindcss` ^4 + `@tailwindcss/vite` | Estilos (tokens en CSS) |
| `vite` ^8 + `laravel-vite-plugin` | Build |

### Transversales (instalar en Fase 0)

| Paquete | Uso | Reglas |
|---|---|---|
| `react`, `react-dom`, `typescript` | React 19 + TS estricto | — |
| `tabulator-tables` (+ tipos) | **Estándar de listados**: motor de `<ServerTable>` (decisión 2026-07-14, sustituye a TanStack de la visión inicial) | frontend.md §3 |
| `react-hook-form` + `zod` + `@hookform/resolvers` | Formularios y validación espejo de FormRequests | playbook §5 |
| shadcn/ui (`radix-ui`, `class-variance-authority`, `clsx`, `tailwind-merge`) | Base del design system (se copia código, no es dependencia runtime única) | frontend.md §2 |
| `lucide-react` | Iconos (única familia permitida) | — |
| `zustand` | Estado global mínimo (carrito, notificaciones) | vision §4.3 |
| `date-fns` | Fechas en cliente (formato es-ES) | R-TRV-06 |
| `laravel-echo` + `pusher-js` | Cliente Reverb | — |
| `i18next` + `react-i18next` | i18n en React (claves compartidas con `lang/*.json`) | R-TRV-05 |
| `sonner` | Toasts (flash messages Inertia) | frontend.md §6 |

### Por módulo (se instalan cuando llegue el módulo)

| Paquete | Módulo / uso |
|---|---|
| Editor WYSIWYG (`@tiptap/react` propuesta) | Kit de formularios: descripciones ricas (productos, docs) |
| Dropzone (`react-dropzone`) | Adjuntos de documentos |
| `@dnd-kit/core` | LineItemsEditor (reordenar líneas), kanban de tareas |
| Gráficas (`recharts` propuesta) | Dashboard/KPIs (Fase 6) |

## 3. Política de versiones

1. Versionado con caret `^` fijando el major; `composer.lock`/`package-lock.json` SIEMPRE
   commiteados.
2. Actualizaciones de dependencias en PRs propios (no mezcladas con features).
3. `composer audit` + `npm audit` en CI; vulnerabilidad alta = bloqueo de merge.
4. Los majors de framework (Laravel, React, Tailwind, Tabulator) se migran como change de
   OpenSpec con su propia propuesta.
