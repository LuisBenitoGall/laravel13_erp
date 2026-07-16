# E2E — estándar de tests de navegador (Pest 4 browser)

> Documento normativo. Decisión 2026-07-14: los E2E se escriben con **Pest 4 browser
> testing** (Playwright por debajo), integrados en la misma suite PHP — sustituye al
> sandbox Playwright/TS de v1, que existía para esquivar el Node 16 del stack viejo.

## 1. Por qué Pest browser (y no Playwright standalone)

1. **Misma suite, mismo runner**: `composer test` lo ejecuta todo; sin `package.json`
   paralelo, sin storage-state, sin `.env.playwright`.
2. **Factories y tenancy directas**: el test prepara datos con las factories del proyecto
   y `withCurrentCompany()` — sin seeds por API ni acoplamiento a IDs.
3. **Un solo lenguaje de aserciones** para feature y browser tests.

Contras asumidos: plugin más reciente que Playwright puro; la experiencia previa en
Playwright TS aplica solo en parte (los conceptos sí: selectores accesibles, trazas,
determinismo).

## 2. Instalación (Fase 0, con el resto del canon)

```
composer require pestphp/pest-plugin-browser --dev
npm install playwright --save-dev
npx playwright install chromium
```

Entorno Laragon/Windows (lecciones de v1 que siguen vigentes):
- Playwright requiere **Node ≥ 18**: en Laragon, Menú → Node.js → v22, y reabrir la
  terminal para recargar el PATH.
- **Nunca `npm install -g <pkg>` con cwd dentro del repo**: npm 10/11 puede tocar
  `package.json`/lock silenciosamente. Hacerlo desde `$env:TEMP` y revisar `git status`.

## 3. Estructura y convenciones

```
tests/Browser/<Dominio>/<Flujo>Test.php    # ej. Sales/OrderToInvoiceTest.php
```

- Datos SIEMPRE por factory en el propio test (nunca depender de datos preexistentes);
  base de datos de test con `RefreshDatabase`/transacciones como el resto de la suite.
- Autenticación con el helper del proyecto (usuario + empresa activa + permisos), no
  rellenando el login en cada test (el login tiene SU test propio).
- **Selectores accesibles primero** (rol/label/texto — lección de v1); si un elemento no
  es seleccionable de forma robusta, añadir `data-testid` puntual en el componente React
  (los componentes del catálogo — ServerTable, kit de formularios, ActionBar — deben
  exponer `data-testid` de serie precisamente para esto).
- Deterministas e independientes: cada test crea lo suyo; prohibido el orden implícito
  entre tests; screenshot/traza solo en fallo.
- Un flujo por fichero; ideal < 30s por test.

## 4. Qué se cubre con E2E (y qué no)

E2E cubre **flujos críticos de usuario completos**, no CRUDs (eso ya lo cubren los
feature tests del playbook §7):

- Login + cambio de empresa activa (aislamiento visual de tenant).
- Documento completo: presupuesto → pedido → albarán → factura (happy path).
- LineItemsEditor: alta de líneas con teclado, descuentos, totales en vivo.
- Permisos en UI: usuario sin permiso no ve la acción Y recibe 403 si fuerza la URL.
- Portal cliente: catálogo → carrito → checkout → pedido borrador (cuando exista).

Regla: cada change que toque un flujo de estos añade/ajusta su E2E (lo decide
10-architecture en las tasks; lo verifica 30-testing).

## 5. Entornos

- Local (Laragon) y CI: navegador headless contra la app de test. En CI, los browser
  tests son un stage separado de la suite rápida (pueden ser más lentos).
- **Prohibido apuntar E2E a producción** de ningún tenant. Cuando exista un entorno
  preview, solo flujos de lectura salvo datos marcados y purgables (patrón v1:
  marcar registros generados y comando `e2e:purge` idempotente).
