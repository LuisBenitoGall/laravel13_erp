# Auditoría de cimientos Fase 0 — 2026-11-22

Estrategia: híbrido (kernel/tenancy + Security Review en zonas calientes +
señal Pest Feature). Working tree limpio en el momento de la revisión.

## Resumen ejecutivo

Los controles de **perímetro Super Admin**, **IDOR en company-users/profile**
y **bulk con authorize por id** están en buen estado. Los hallazgos accionables
antes de módulos de negocio se concentran en **almacenamiento público de
ficheros**, **login sin status**, **status de user global vs vínculo**, y
**PII en directorio público**.

Señal tests: `CompanyModuleLayers|Login|EnsureCompanyAccount` → 18 passed
(PHP 8.3). Suite Browser falla al arrancar Pest si el `php` activo no tiene
extensión `sockets` (p. ej. PHP 8.5 Laragon) — riesgo de DX/CI, no de
producto.

## Controles OK (no reabrir sin evidencia nueva)

- `Gate::before` Super Admin + unset relations / Teams.
- `SetCurrentCompany` valida `belongsToCompany` y limpia sesión si no.
- `EnsureCompanyAccountIsActive` + whitelist renovación.
- Mis módulos Optional-only + Basic siempre on (checker/kernel alineados).
- Docs de usuario en disco `local` (no URL pública directa).
- Comments en CompanyUserController sobre perímetro post-`Gate::before`.

## Findings priorizados

### Alto

| ID | Hallazgo | Evidencia | Remediación propuesta |
|---|---|---|---|
| F-01 | Imágenes de usuario en disco `public`; flags `public`/`is_public` no protegen GET HTTP a `/storage/...` | `UploadUserImages` → `Storage::disk('public')` | Disco privado + ruta autenticada (como docs), o signed URLs |

### Medio

| ID | Hallazgo | Evidencia | Remediación propuesta |
|---|---|---|---|
| F-02 | Login no rechaza `users.status = false` | `AuthenticatedSessionController::store` / `Auth::attempt` solo email+password | Condición `status` en attempt o post-check + logout |
| F-03 | Desactivar user (fila/bulk) cambia status **global**, afecta otras companies | `UpdateCompanyUserStatus` | Confirmar producto: si debe ser solo vínculo, modelar status por membership; si es global, documentar + UI warning |
| F-04 | Quick view `all-users` expone emails/teléfonos/companies con solo `all-users.view` | `UserQuickViewData` + ruta all-users quick | Reducir payload RO o permiso más estricto / campo masking |
| F-05 | Galería MediaAsset en disco `public` (URL estática sin auth) | `CreateMediaAsset` | Disco privado o signed URLs; alinear a política de visibilidad |
| F-06 | Listado galería no acotado a `CurrentCompany` (todas las companies del user) | `MediaAssetController` | Scope a company de sesión salvo diseño explícito multi-company |

### Bajo / Info

| ID | Hallazgo | Notas |
|---|---|---|
| F-07 | Props `can` del Edit perfil parcialmente decorativas | UI vs policy real — riesgo de botones mentira |
| F-08 | FormRequests con `authorize() === true` | Auth solo en controller — footgun |
| F-09 | Registro público abierto | Info / producto |
| F-10 | `BelongsToCompany` no filtra si `CurrentCompany` es null | OK en HTTP con middleware; cuidado en consolas/jobs |
| F-11 | Pest Browser + PHP sin `sockets` | DX: fijar PHP 8.3+sockets en CI/local para Browser |

## Deuda de producto (no seguridad, sí cimiento)

- Permisos Spatie aún no cableados 1:1 a cada Functionality de dominio futuro.
- E2E críticos de `e2e.md` §4 parcialmente cubiertos (9 browser tests).
- Changes recientes archivados; no hay change abierto de remediación.

## Recomendación de siguiente paso

Un solo change OpenSpec de remediación priorizada, p. ej.
`cYYYY-MM-DD-foundation-hardening`, con tasks ordenadas:

1. F-01 + F-05 (storage)  
2. F-02 (login status)  
3. F-03 (decisión de negocio status global vs vínculo)  
4. F-04 / F-06 (PII / galería scope)  
5. F-07–F-08 (higiene authz) + F-11 (tooling)

No empezar módulos de negocio (ventas/stock/…) hasta cerrar al menos **Alto + Medio de auth/storage** (F-01, F-02, F-05).
