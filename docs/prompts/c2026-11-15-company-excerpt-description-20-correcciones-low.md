Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-15-company-excerpt-description
Origen: informe 25-architecture-review (**APPROVED WITH OBSERVATIONS**).
Corrección **LOW** (no hay CRITICAL/HIGH). No reabran diseño.
Contrato: `openspec/changes/c2026-11-15-company-excerpt-description/`
(design D3). Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni specs.
No archives. E2E N/A.

## Contexto

El HIGH de atributos de evento ya está cerrado (`AllowedHtmlSanitizer`
reescribe openings; Feature verde). Queda un LOW cosmético: al hacer
`strip_tags` de tags no allowlist (p. ej. `<script>…</script>`), el
**texto interior** puede quedar en el HTML (`alert(1)`). No es XSS
ejecutable, pero ensucia `description` persistida.

## Qué hacer (LOW)

### 1. Descartar texto huérfano de tags peligrosos (o equivalente)

En `app/Support/Html/AllowedHtmlSanitizer.php`, **antes** del
`strip_tags` allowlist (o en un paso equivalente sin Composer nuevo):

- Eliminar por completo elementos peligrosos **incluyendo su contenido**,
  al menos: `script`, `style`, `iframe`, `object`, `embed`, `noscript`
  (case-insensitive; tags con attrs).
- Regex/DOM razonable; no HTMLPurifier ni deps nuevas.
- Tras eso, mantener el flujo actual: `strip_tags` allowlist + openings
  sin attrs (solo `a` con `href` http(s)/mailto) + vacíos → `null`.

Ejemplo esperado:

- Entrada: `<p>Hola</p><script>alert(1)</script>`
- Salida: contiene `<p>Hola</p>`; **no** contiene `alert(1)`.

No cambies excerpt (texto plano). No toques Wysiwyg/UI/Directorio.

### 2. Feature / assert

Ampliar el test de saneo en `CompanyEditFormTest` (o Unit del sanitizer):

- Tras Update con `<script>alert(1)</script>` en description → el valor
  persistido **no** contiene `alert(1)`.
- Conservar asserts de `onclick` / `javascript:` / `https` href.

## Verificación

```text
php artisan test --compact tests/Feature/Admin/CompanyEditFormTest.php --filter="sanea|excerpt|description"
```

(o suite Edit+Create si tocaste solo el sanitizer compartido)

Pint dirty PHP.

## Fuera de alcance

- Directorio Card/Show.
- Create inputs.
- Toolbar Link / TipTap.
- HTMLPurifier u otra lib.
- Archive / reabrir HIGH.

## Gate de salida

- [ ] Contenido de `script`/`style`/… no queda como texto en el HTML
      saneado.
- [ ] Assert Feature/Unit verde; handlers y `href` seguro siguen OK.
- [ ] Pint OK.
- [ ] Volver a 25 solo si Luis lo pide; si no, seguir a 30-testing.
