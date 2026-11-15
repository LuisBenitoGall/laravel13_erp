Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-15-company-excerpt-description
Origen: informe 25-architecture-review (**RETURN TO IMPLEMENTATION**).
Hay un **HIGH** bloqueante (+ LOW de cobertura). No reabran diseño.
Contrato: `openspec/changes/c2026-11-15-company-excerpt-description/`
(proposal, design D3, specs `company-edit` / `form-kit`).
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni los deltas de specs.
No archives. E2E N/A.

## Contexto

Campos `excerpt`/`description`, Edit Info + `Form/Wysiwyg` (tiptap) y
Create sin inputs están bien. El fallo es `AllowedHtmlSanitizer`: en
PHP 8.4 `strip_tags($html, $allowlist)` **conserva atributos** de los
tags permitidos. Solo se reescriben openings `<a …>`; queda XSS vía
p. ej. `<p onclick="…">` / `<strong onmouseover="…">`.

Evidencia 25: tras `sanitize()`, esos handlers salen intactos.
Design D3 exige allowlist de tags + `a` solo con `href` http(s)/mailto;
mitigar XSS al persistir **sin** paquete Composer nuevo.

## Qué hacer

### 1. HIGH — Endurecer `AllowedHtmlSanitizer` (obligatorio)

Archivo: `app/Support/Html/AllowedHtmlSanitizer.php` (y tests).

Reglas (sin A/B; sin HTMLPurifier/Composer nuevo):

1. Tags permitidos (como ahora): `p`, `br`, `strong`, `em`, `ul`, `ol`,
   `li`, `h2`, `h3`, `a`.
2. **Ningún** tag allowlist puede conservar atributos arbitrarios
   (`onclick`, `onerror`, `style`, `class`, etc.).
3. Única excepción de atributo: en `<a>`, solo `href` si el valor, tras
   decode, empieza por `http://`, `https://` o `mailto:` (como hoy).
   Cualquier otro `href` (p. ej. `javascript:`) → `<a>` sin attrs o
   strip del enlace según la lógica actual que ya neutraliza
   `javascript:`.
4. Contenido de `<script>` / tags no allowlist: no debe quedar HTML
   ejecutable (como hoy con strip de tag).

Vía mínima sugerida (puedes ajustar implementación si cumple lo mismo):

- Tras `strip_tags` allowlist, **reescribir openings** de cada tag
  permitido: tags sin attrs → `<p>`, `<strong>`, …; solo `<a>` puede
  llevar `href` seguro.
- O parser DOMDocument / regex equivalente **sin** nueva dependencia.
- Cerrar tags / HTML malformado: comportamiento razonable; no hace falta
  un HTML perfecto, sí **cero** atributos peligrosos.

Mantén el contrato de vacíos → `null` (`''`, `<p></p>`, `<p><br></p>`).

`UpdateCompany` sigue usando este sanitizer en `description` por locale;
no cambies excerpt (texto plano).

### 2. LOW — Feature de event handlers

Ampliar `tests/Feature/Admin/CompanyEditFormTest.php` (o unit del
sanitizer si el proyecto ya tiene tests Unit de Support):

- PUT description con `<p onclick="alert(1)">hola</p>` y/o
  `<strong onmouseover="x()">x</strong>` → persistido **sin**
  `onclick` / `onmouseover`.
- Conservar asserts actuales: `<script>` fuera; `javascript:` fuera;
  `https://` en `href` permitido.

Opcional unit directo de `AllowedHtmlSanitizer` si acelera el fail-fast;
no sustituye el Feature de Update si ya existe el happy path ahí.

## Verificación

```text
php artisan test --compact tests/Feature/Admin/CompanyEditFormTest.php tests/Feature/Admin/CompanyCreateFormTest.php
```

Pint dirty PHP (`vendor/bin/pint --dirty --format agent`).

Comprobación manual rápida (tinker o test):  
`sanitize('<p onclick="alert(1)">hola</p>')` no contiene `onclick`.

## Fuera de alcance

- Mostrar excerpt/description en Directorio Card/Show.
- Inputs en Create.
- Añadir botón Link a la toolbar Wysiwyg.
- Instalar HTMLPurifier / otra lib Composer.
- Reescribir TipTap / CompanyForm salvo si el sanitizer exige un ajuste
  mínimo de tipado.
- Archive / 30-testing / E2E.

## Gate de salida

- [ ] `AllowedHtmlSanitizer` elimina atributos de evento (y cualquier
      attr no autorizado) en tags allowlist; `a` solo `href` seguro.
- [ ] Feature (o Unit+Feature) cubre `onclick`/`onmouseover`; suite
      Edit/Create verde.
- [ ] Pint OK.
- [ ] Volver a **25-architecture-review** (no archivar aquí).
