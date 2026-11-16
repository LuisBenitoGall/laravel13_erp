# Design

## Context

Hoy: `OnboardCompany` / `CreateCompany` → roles Basic → dashboard.
Existen Edit company (identidad, logo) y CRUD workplaces. Visión v2 pide
asistente; brief fija shell + pasos reales + modal/banner.

## Goals / Non-Goals

**Goals:** Hub `company-settings`; wizard identidad+workplace; modal +
«Ahora no»; banner; menú; readiness computada.

**Non-Goals:** Soft-gates duros; pasos futuros; muro sin «Ahora no»;
sustituir Configuración SA / Cuenta; auto-añadir módulos al wizard.

**E2E:** valorar en design — flujo crítico onboarding → **sí incluir
tasks E2E ligeras** (Pest browser) si `e2e.md` §4 aplica a auth/onboard;
si no está listado, Feature + “E2E N/A” explícito. **Decisión:** E2E N/A
en este change (cobertura Feature); rol 40 cuando se amplíe §4.

**Excepciones:** ninguna.

## Decisions

### D1 — Authz y menú

- Functionality slug `company-settings` bajo Module `empresas` (Basic).
- Permisos canónicos; sembrar `.view` (y update si el hub guarda) a
  roles básicos tipo manager alineados con Empresas.
- Ruta Index hub: `GET /admin/company-settings` (`admin.company-settings`).
- Label menú: «Configuración de la empresa».

### D2 — Readiness computada (company de sesión)

Incompleta si **no** se cumple todo:

| Paso | Hecho |
|------|--------|
| Identidad | `nif` no vacío (name ya existe al crear) |
| Logo | `logo` no nulo *(incluido en el paso UI; **requerido** para marcar identidad completa — evita banner eterno con solo NIF)* |
| Sede | ≥1 `Workplace` Active de la company |

Progreso = pasos cumplidos / 2 grupos lógicos (Identidad+logo | Sede) o
3 checks (nif, logo, workplace) — **decisión UI:** 2 pasos de wizard
(Identidad incl. logo; Centros de trabajo); progreso 0–2.

Alternativa descartada: solo flags `setup_step_*` en BD.

### D3 — Modal y «Ahora no»

- Tras `CreateCompany` exitoso (onboarding **o** Mis empresas store):
  flash/session `company_setup.show_modal = true` para esa company.
- Modal en `AppLayout` (shared prop) si incompleta **y**
  `show_modal`; CTA → hub (`?wizard=1`); «Ahora no» → POST/endpoint o
  visita que limpia `show_modal` (sesión) y no bloquea.
- No reabrir el modal en cada navegación de la misma sesión tras dismiss.
- Login posterior con company incompleta: **solo banner** (no modal
  spam); el modal vuelve a ofrecerse al **crear** otra company.

### D4 — Banner

- Shared prop `company_setup.incomplete` + `hub_url`.
- Banner en shell (AppLayout) si incompleta; enlace al hub; i18n.
- Desaparece al cumplirse readiness (tras reload/Inertia visit).

### D5 — Hub UX

- Página hub: resumen progreso + tabs/pasos reales (Identidad | Sedes).
- Modo guiado: query `wizard=1` o CTA «Continuar asistente» → stepper
  secuencial; al terminar → hub o dashboard.
- Identidad: reutilizar update company + upload logo (mismas Actions /
  rutas admin existentes o wrappers delgados).
- Sedes: crear workplace mínimo inline **o** enlace a
  `/admin/workplaces/create` con return al hub — **decisión:** enlace a
  Create workplace existente + volver al hub (menos duplicar form);
  listar si ya hay sedes y marcar paso OK.

### D6 — Extensibilidad

- Catálogo de pasos en código (array/registry) filtrado a “implementados”.
- Nuevos pasos: solo con change explícito pedido por Luis (no auto).

### D7 — V-07 / V-08

Sí (Functionality + menú). V-09 no. R-TEN-05: hub solo company sesión;
workplaces ya aislados.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Logo obligatorio molesta | Aceptado en MVP readiness; se puede relajar en change futuro |
| Duplicar forms | Reusar rutas/Actions; hub orquesta |
| Modal tras create vs N companies | Flash solo en el store que acaba de crear |

## Migration Plan

1. Functionality + menú + shared props.
2. Hub + wizard wiring.
3. Flash modal en CreateCompany/Onboard flows.
4. Tests + docs.

## Open Questions

Ninguna (brief cerrado).
