# Design

## Context

Hoy `AuthLayout` centra children en `bg-slate-100`; Login/Register son
forms `max-w-md` con botones `bg-teal-700`. Backend/register/login no
cambian. Referencia conceptual: short CSS sliding auth; **no** clonar
markup/colores del video. Motivación: proposal — Why.

## Goals / Non-Goals

**Goals:** panel deslizante Entrar↔Registrarse; teal ERP; URLs estables;
a11y motion.

**Non-Goals:** onboarding, OAuth, Stripe, slide en Forgot/Reset,
cambios de validación.

## Decisions

### D1 — Arquitectura Inertia

- **Decisión:** una página Inertia `Auth/SlidingAuth` (o equivalente)
  renderizada por LoginController y RegisterController con prop
  `mode: 'login' | 'register'`. Toggle del overlay:
  `router.visit('/login'|'/register', { preserveScroll: true })` para
  sincronizar URL; animación CSS al cambiar `mode`.
- **Alternativa:** dos pages que montan el mismo shell — más drift;
  descartada.
- Formularios: dos `useForm` (login / register) en el mismo componente
  o subcomponentes; POST a `/login` y `/register` sin cambio de rules.

### D2 — Estructura visual

```
┌────────────────── AuthCanvas (min-h-screen) ──────────────────┐
│ LanguageSwitcher                                              │
│   ┌──────────── SlidingCard (max-w-4xl) ─────────────────┐   │
│   │  [Form Login]  [Form Register]   ← capas absolutas    │   │
│   │  [Overlay panel teal] se traslada L↔R                 │   │
│   │   copy i18n + CTA «Registrarse» / «Entrar»            │   │
│   └───────────────────────────────────────────────────────┘   │
└───────────────────────────────────────────────────────────────┘
```

- Desktop: dos mitades; overlay cubre ~50% y se desliza.
- Mobile: stack o overlay a pantalla completa del card (sin dos columnas
  ilegibles); 10/20 elige breakpoint `md:`; reduced-motion = cambio
  instantáneo de modo sin translate.

### D3 — Tokens de marca

- Primario: **teal-700 / teal-800** (botones, overlay, links) — ya en
  producto.
- Superficie form: blanco; texto slate-900/600.
- Fondo canvas: slate-100 o gradiente sutil teal→slate (sin purple).
- Tipografía: la del app (no Inter/Roboto nuevos); display del overlay
  puede usar weight semibold existente.
- CSS variables opcionales en AuthLayout (`--auth-primary`) para no
  hardcodear docenas de clases.

### D4 — AuthLayout

- Forgot/Reset/Onboard/SelectCompany siguen `AuthLayout` estático
  (centrado).
- SlidingAuth puede usar `AuthLayout variant="sliding"` o layout propio
  sin romper el estático.

### D5 — Contenido overlay

- i18n: títulos/subtítulos CTA (p. ej. «¿No tienes cuenta?» /
  «¿Ya tienes cuenta?») — claves nuevas en es/en/ca.
- Sin imágenes stock obligatorias; overlay sólido teal + copy basta
  (económico). Ilustración opcional después.

### D6 — Tests / E2E

- Feature: GET `/login` y `/register` renderizan el mismo component con
  `mode` correcto; assert fuente contiene clase/data-testid del shell.
- Feature login/register existentes siguen verdes.
- E2E: N/A.

### D7 — Excepciones

Ninguna a kernel.

## Risks / Trade-offs

- [Animación vs Inertia visit] → Mitigación: CSS en función de `mode`;
  visit rápido; reduced-motion.
- [Register más alto que Login] → Mitigación: altura min del card /
  scroll interno del form.
- [Clonar short viral] → Mitigación: tokens ERP + copy propio.

## Migration Plan

1. SlidingAuth + controllers mode prop.
2. AuthLayout tokens; i18n.
3. Tests + retirar UI antigua de Login/Register pages.
4. Pint / build front si hace falta.

## Open Questions

_(ninguna)_
