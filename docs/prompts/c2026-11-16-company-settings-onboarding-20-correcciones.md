Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-16-company-settings-onboarding
Origen: informe 25-architecture-review (**RETURN TO IMPLEMENTATION**).
Hay un **HIGH** bloqueante + **MEDIUM** wizard (+ LOWs opcionales).
No reabran diseño.
Contrato: `openspec/changes/c2026-11-16-company-settings-onboarding/`
(proposal, design D3/D5, specs `company-settings-onboarding`).
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni los deltas de specs.
No archives. E2E N/A (design).

## Contexto

Hub, menú, readiness, banner, dismiss y Onboard→modal están en buen
estado (`CompanySettingsOnboardingTest` 11/11). El fallo bloqueante es
el camino **Mis empresas → store**: se hace
`session.put('company_setup.show_modal', true)` pero **no** se cambia
la company de sesión. `HandleInertiaRequests` calcula
`show_modal = incomplete && flag` sobre la company de **sesión**. Si esa
está completa (caso típico al crear una 2.ª+ company), el modal **no**
aparece pese al flag. Design D3: flash «para esa company»; spec: modal
tras onboarding **o** Mis empresas.

Además (MEDIUM): en wizard, guardar identidad salta a Sedes aunque
falte logo.

## Qué hacer

### 1. HIGH — Modal Mis empresas atado a la company creada (obligatorio)

Archivo principal: `CompanyController::store` (y tests).

Vía mínima alineada con Onboard (sin A/B; no reinventar el hub):

1. Tras `CreateCompany` exitoso en Mis empresas store, cambiar la
   company de sesión a la **recién creada** con el mismo mecanismo que
   Onboard (`SetCompanySession` / patrón existente; **nunca**
   `session('currentCompany')`).
2. Mantener `company_setup.show_modal = true`.
3. Conservar el redirect a Edit de la company creada (comportamiento
   actual de Create), ahora coherente con la sesión.
4. No tocar el flujo Onboard salvo reutilizar el helper de sesión si
   hace falta DRY mínimo.

Resultado esperado: con sesión previa en company **completa**, tras
`POST admin.companies.store` de una company nueva incompleta (p. ej.
sin logo / sin workplace), la siguiente respuesta Inertia (Edit o
visita shell) tiene `company_setup.show_modal === true` y
`company_setup.incomplete === true` evaluados sobre la **nueva**.

Feature (obligatorio) en `CompanySettingsOnboardingTest` (o Create):

- Company de sesión A completa (nif+logo+≥1 workplace Active).
- Store company B incompleta (p. ej. con nif pero sin logo; sin
  workplaces).
- Assert: `session('current_company_id') === B.id`,
  `session('company_setup.show_modal') === true`.
- `GET` dashboard (o Edit B) → Inertia
  `company_setup.show_modal === true` y `incomplete === true`.

No basta asertar solo el flag de sesión (el test actual no caza el bug).

### 2. MEDIUM — Wizard no avance a Sedes sin identidad completa

Archivo: `resources/js/Pages/Admin/CompanySettings/Index.tsx`.

En `submitIdentity` → `onSuccess`:

- Solo `setTab('workplaces')` si el paso identidad queda **done**
  (nif + logo). Tras el PUT, los props `setup` pueden estar stale:
  o bien no avances automáticamente y deja que el usuario/tabs +
  reload Inertia reflejen el estado, o bien avanza solo cuando tras el
  success puedas afirmar nif+logo (p. ej. nif del form + `setup.steps.identity.logo`
  / `company.logo_url` actualizado).
- No avances a Sedes si falta logo.
- Tras subir logo (`CompanyLogoPanel`), no es obligatorio auto-avanzar
  en este fix; sí es obligatorio **no** saltar el logo al guardar el
  formulario de texto.

Si tocas el panel de logo solo para coherencia wizard, mantén el
contrato de upload existente.

Assert UI/Feature razonable: source o comportamiento — como mínimo que
el `onSuccess` no fuerce Sedes sin logo (p. ej. test de fuente o
comentario de regresión en Feature si ya leéis el Index).

### 3. LOW (recomendado, no bloquea el HIGH)

- Quitar o usar `can.update_settings` (hoy se pasa y no se usa).
- Ampliar cobertura: login con company incompleta →
  `show_modal === false` y `incomplete === true` (solo banner), si es
  barato en el mismo Feature.

## Verificación

```text
php artisan test --compact tests/Feature/Admin/CompanySettingsOnboardingTest.php
```

Si tocaste Create/session, añadir el filtro o archivo Create tocado.
Pint dirty PHP. No hace falta suite completa aquí.

## Fuera de alcance

- Soft-gates de negocio / muro sin «Ahora no».
- Pasos futuros (series, SEPA, payment methods, plan contable…).
- Cambiar Configuración SA / Cuenta.
- Auto-añadir módulos al wizard.
- Cambiar readiness (nif+logo+≥1 Active) o quitar logo obligatorio.
- Archive / E2E 40.
- Reescribir artefactos OpenSpec.

## Gate de salida

- [ ] Tras Mis empresas store, sesión = company nueva; modal Inertia
      visible si esa company está incompleta (aunque la previa estuviera
      completa).
- [ ] Feature nuevo/ampliado verde (no solo flag de sesión).
- [ ] Wizard no salta a Sedes si falta logo.
- [ ] Pint OK en PHP tocado.
- [ ] Volver a **25-architecture-review** (no a 30 hasta APPROVED).
