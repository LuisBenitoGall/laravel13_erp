Usa el agente @.cursor/agents/agent-40-playwright.md guiado por
docs/agents/40-e2e.md, la rule `.cursor/rules/40-e2e.mdc` y `docs/standards/e2e.md`.

Change: c2026-10-29-server-table-reference
Origen: informe 30-testing (gate de archive bloqueado).
Contrato: `openspec/changes/c2026-10-29-server-table-reference/` (tasks.md §6.5).
Si este prompt y los artefactos chocan, **mandan los artefactos**.

## Contexto

- Task **6.5**: E2E **nuevo** = N/A (listado CRUD; Feature 6.1–6.3). Rol 40 =
  `npm run build` + suite Browser en verde, aserciones alineadas al chrome actual
  (dashboard sin «Inicio»; Index Módulos sin subtítulo antiguo; toolbar visible;
  columna Acciones).
- 30 ejecutó Unit+Feature+Architecture en verde (184 tests). La suite Browser
  falló **antes de abrir la app**: cliente Pest/Playwright
  (`Playwright was just installed or updated` / `Assertion error` en
  `pest-plugin-browser` → `Playwright\Client.php`). Duración ~2 s, 0 aserciones.
- Un pase anterior de 40 dejó Browser en verde; el entorno actual lo ha roto.
  Tu trabajo es **reparar el runner y confirmar verdes**, no inventar un E2E nuevo
  del CRUD de Módulos.

## Qué SÍ haces

1. Diagnostica por qué Pest browser no arranca Playwright (plugin ^4, paquete npm
   `playwright`, Chromium, `ext-sockets`, Node ≥ 18, `npm run build`).
2. Alinea el toolchain con `docs/standards/e2e.md` §2. Preferir PHP 8.3 + Pest 4
   (canon). Si usas otro `php.exe` de Laragon, documenta cuál y por qué.
3. Deja verde: `php artisan test --compact --testsuite=Browser` (los 10 tests
   actuales bajo `tests/Browser/Auth/`).
4. Si hace falta un ajuste mínimo de aserciones al chrome del change (texto
   Dashboard, toolbar «Mostrar/ocultar columnas», «Acciones» en Modules Index),
   hazlo en los Browser existentes. **No** crees sandbox Playwright/TS.
5. Marca/confirma task 6.5 en `tasks.md` solo cuando Browser esté verde.
6. Hand-off explícito a 30-testing: suite Browser OK (o bloqueado por X).

## Qué NO haces

- No implementas dominio ni cambias proposal/design/deltas de specs.
- No archives el change (30).
- No escribes Feature/Unit/Arch tests.
- No migras otros Index ni añades E2E de CRUD Modules (6.5 = N/A).
- No hagas `npm install -g` con cwd dentro del repo.
- No instales Pest browser v5 (exige PHP 8.4 / Pest 5).
- No apuntes E2E a producción.

## Credenciales y secretos — PREGUNTA, no inventes

Los Browser Auth crean usuarios con **factories** (email/password del propio test;
no necesitan el Super Admin real de `.env`).

**Antes de asumir o inventar**, pregunta a Luis y espera respuesta si te falta
cualquiera de esto para arrancar o autenticar:

- Usuario / contraseña de la app local (solo si un flujo no puede usar factory y
  hace falta login real en `laravel13_erp.test`).
- `APP_URL` / host virtual de Laragon distinto del que ves.
- Credenciales de BD local si el runner de Browser no usa el sqlite `:memory:` de
  `phpunit.xml` y falla la conexión.
- Cualquier token, `.env` o secreto que no esté en el repo.

No leas ni pegues secretos en el informe. No inventes passwords «por defecto»
fuera de las factories del test. Si sin esa respuesta no puedes cerrar → hand-off
bloqueado: «faltan credenciales X; pedidas a Luis».

## Gate

- [ ] Causa del fallo Playwright diagnosticada y documentada (1 párrafo).
- [ ] `npm run build` hecho si el front lo requiere.
- [ ] `php artisan test --compact --testsuite=Browser` **verde**.
- [ ] Sin `playwright.config.ts` / `tests/e2e/*.spec.ts` nuevos.
- [ ] Task 6.5 coherente con el resultado.
- [ ] Hand-off a 30 (listo para re-verificar) o bloqueo A/B / credenciales.

## Orden sugerido

1. Reproducir el error Browser; capturar el mensaje exacto.
2. Reparar runner (playwright npm + `npx playwright install chromium`, sockets,
   Node). Si necesitas credenciales o un dato de entorno no documentado → **pregunta**.
3. Re-ejecutar solo Browser hasta verde.
4. Si un test falla por aserción de chrome (no por runner), alinear al UI actual
   del change.
5. Informar a 30: verdes + comandos usados (ruta de `php.exe` incluida).
