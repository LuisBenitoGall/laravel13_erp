## Context

El change `frontend-bootstrap` (archivado) dejó Inertia+React+TS+Tailwind operativos.
Aún no existen `app/Support/`, Pest, PHPStan/Pint configs, ni CI. Las piezas de este
change ya están especificadas en `templates-backend.md` §12 y `tooling.md` §1–§4;
`libraries.md` canoniza los paquetes. Secuencia decidida en explore: este change **antes**
del de Admin (`admin-catalog-permissions`), que usará el generador.

Estado verificado al proponer: sin `app/Support/`, sin workflows CI, sin `phpstan.neon`/
`pint.json`; `laravel/pint` ya en `composer.json`; Pest aún no instalado.

## Goals / Non-Goals

**Goals:**
- Pest + plugins laravel/arch; piezas §12; `EnsureModuleIsActive`; generador + stubs;
  backstop completitud; arch tests iniciales; PHPStan 6 + Pint; workflow CI; helper
  `withCurrentCompany()`.

**Non-Goals:**
- Entidades/módulos de negocio (`Module`, `Functionality`, Spatie Teams, `Company`, auth
  de producto) — change posterior `admin-catalog-permissions`.
- Tabla `companies` real: fixtures desechables para tenancy.
- Entidad de ejemplo permanente del generador: solo scratch en tests, con limpieza.
- Ejecución remota real del CI (solo el fichero de workflow).
- Frontend de negocio / pantallas reales más allá de lo que generen los stubs.
- `pest-plugin-browser` / E2E Pest browser (stage CI diferido — ver Decisions).

## Decisions

1. **`NumberingService` en `app/Domains/Admin/Services`** (ubicación canónica de
   `templates-backend.md` §12 / R-BD-05), con migración mínima de `document_numbers` y
   VO/retorno `DocumentNumber`. **No** es el módulo Admin completo: no hay `Module`/
   menú/UI. Alternativa descartada: meterlo en `app/Support` — violaría la ubicación
   normativa y mezclaría un servicio de dominio en Support (R-ARQ-02 al revés).

2. **Fixtures de tenancy, no tabla `companies`**: modelos/migraciones de prueba bajo
   `tests/` (o namespace de testing) con `company_id` sintético. Alternativa descartada:
   crear `companies` ahora — invade el Non-Goal de Admin.

3. **`EnsureModuleIsActive` con resolver inyectable**: el middleware llama a un puerto
   (p. ej. `ModuleActivationChecker`) con implementación stub/null-safe hasta que exista
   `Module`/`CompanyModules`. Tests del middleware usan un fake. Alternativa descartada:
   hardcodear consulta a tablas que aún no existen.

4. **Auto-provisión Functionality↔permisos en el generador**: el código se implementa
   según `tooling.md` §1; si las clases `Functionality` / Spatie `Permission` no existen,
   el generador omite ese paso con aviso claro (no falla la generación de ficheros). La
   verificación E2E de provisión queda para `admin-catalog-permissions`. Alternativa
   descartada: exigir Admin primero — contradice la secuenciación acordada.

5. **Validación del generador solo vía tests Artisan scratch + cleanup** — sin dejar
   entidad permanente (brief).

6. **CI workflow según `tooling.md` §4 steps 1–4 y 6**; el step 5 (Pest browser) queda
   como job `e2e` marcado `if: false` o comentario TODO hasta instalar
   `pest-plugin-browser` en un change de e2e. Alternativa descartada: instalar browser
   aquí — fuera del brief y de los Goals de install.

7. **Arch tests "vacuos" OK**: con cero modelos de dominio, las reglas sobre
   `App\Domains` pasan; fixtures de test se listan en `ignoring([...])` donde aplique
   (p. ej. BelongsToCompany en modelos de prueba fuera de Domains).

8. **Smokes no-arch** (policies 403 / i18n es↔en) de `tooling.md` §2: **diferidos** al
   change de Admin/auth — no hay rutas `auth` ni `lang` de negocio todavía. Este change
   cubre solo el bloque `arch(...)` listado como "reglas mínimas iniciales".

9. **Nombre de change**: `cYYYY-MM-DD-<slug>` — el CLI OpenSpec exige que el nombre
   empiece por letra; la `c` es marcador de change y la fecha sigue siendo obligatoria.
   El brief/prompt usa el mismo identificador.

## Risks / Trade-offs

- **[Riesgo]** `NumberingService` / `TableBuilder` solo con casos sintéticos → validación
  e2e real llega con Admin/documentos. **Mitigación**: tests de concurrencia FOR UPDATE y
  contrato TableRequest whitelist documentados; no fingir cobertura de negocio.
- **[Riesgo]** Generador genera frontend stubs sin ServerTable real del catálogo →
  stubs mínimos alineados a `templates-frontend.md`, no inventar componentes.
  **Mitigación**: stubs = templates; sin dependencias UI nuevas.
- **[Riesgo]** Auto-provisión desactivada hasta Admin → fácil olvidarla. **Mitigación**:
  aviso en generador + task explícita en Admin para activar/verificar el camino.
- **[Riesgo]** PHPStan nivel 6 sobre código nuevo puede exigir ajustes de tipos en
  Support. **Mitigación**: tipado estricto desde el primer commit de Support; verde
  obligatorio antes de cerrar.

## Migration Plan

No hay datos de producción. Orden de apply sugerido: Pest → Support/tenancy →
NumberingService → Table* → middlewares → generador/stubs → backstop → arch →
PHPStan/Pint → CI. Rollback = revertir el change (sin estado persistente de negocio;
`document_numbers` es tabla vacía de infraestructura).

## Open Questions

Ninguna bloqueante: las decisiones de secuenciación, fixtures y alcance CI/browser
quedaron cerradas en el brief/explore. Pendiente solo revisión humana (Luis) del DoR
antes de `/opsx-apply`.
