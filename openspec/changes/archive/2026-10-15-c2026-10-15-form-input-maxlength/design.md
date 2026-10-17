## Context

Auth usa `<input>` HTML suelto sin `maxLength`. `RegisterUserRequest` ya tiene
`max:255` en name/surname/email; login/forgot/reset validan inline **sin** `max`.
El catálogo `frontend.md` prevé `Components/ui/` (shadcn) y `Form/Field`, pero en repo
casi solo existe `ServerTable`. Brief: default 255 en componente, Auth completo, CI,
rule de agentes; Textarea fuera.

Ficha de módulo / V-01..V-20: **no aplican** (kit transversal UI + Auth, sin entidad de
dominio nueva). module-map: sin cambio.

## Goals / Non-Goals

**Goals:**

- `Components/ui/input.tsx` (shadcn) con default `maxLength={255}`.
- Auth text fields → ese Input; overrides explícitos (p. ej. NIF `32`).
- Backend Auth: todo string de texto con `max` espejo del `maxLength` UI.
- Canon en `frontend.md` + templates; rule Cursor; test Architecture en CI.
- Listo para forms futuros vía default del componente.

**Non-Goals:**

- Textarea / Wysiwyg.
- Kit completo `Form/Field`, RHF+zod en Auth (puede seguir `useForm` Inertia).
- Migrar formularios fuera de Auth (Partners CRUD, etc.) en este change.
- Skill Claude separado (se elige **rule** Cursor; ver Decisions).
- E2E nuevo por `e2e.md` §4 (no es flujo crítico listado); ajustar browser Auth solo si
  el selector/rompe el fill.

## Decisions

1. **Componente = shadcn `Input` en `Components/ui/input.tsx`**  
   Extiende el primitivo shadcn con `maxLength = 255` por defecto (prop overrideable).
   Constante documentada `DEFAULT_INPUT_MAX_LENGTH = 255` en el mismo módulo o
   `lib/form-limits.ts` para un solo sitio en TS.  
   _Descartado:_ parche `maxLength` en cada página Auth; Input ad-hoc sin shadcn.

2. **Ámbito del Input (qué cuenta como “texto”)**  
   Tipos: `text`, `email`, `password`, `search`, `tel`, `url`.  
   **Excluidos:** `checkbox`, `radio`, `hidden`, `file`, `submit`, `button`.  
   El logo (`file`) en OnboardCompany no pasa por este Input.

3. **Auth a migrar**  
   Login, Register, ForgotPassword, ResetPassword, OnboardCompany (campos texto).  
   SelectCompany: sin inputs de texto → sin cambio de Input.  
   NIF: `maxLength={32}` + `max:32` (ya en OnboardCompanyRequest).

4. **Backend: FormRequest dedicados donde hoy hay `validate` inline**  
   Extraer (o añadir `max`) para login, forgot, reset: `email`/`password` con
   `max:255` alineado. Register ya OK; password puede seguir `Password::defaults()` **más**
   tope de longitud coherente (p. ej. `max:255` en la regla string/password según Laravel).  
   _Descartado:_ solo `maxLength` HTML sin tocar server.

5. **CI = Pest Architecture (no ESLint nuevo en este change)**  
   Test que falle si en `resources/js/Pages/Auth/**/*.tsx` aparece `<input` con type de
   texto (o type omitido = text) que no sea el componente `Input` importado del kit;
   y que `input.tsx` declare default 255.  
   _Descartado:_ solo lint sin test en suite `Architecture` (el proyecto ya usa Pest arch).

6. **Agentes = Cursor rule (no skill separado)**  
   `.cursor/rules/` (p. ej. `form-input-maxlength.mdc`) con globs a Pages/Components:
   obligatoriedad de usar `Components/ui/input` para campos texto; default 255; override
   + FormRequest `max` espejo; Textarea fuera de alcance de esta rule.  
   _Descartado:_ solo skill (menos always-on); brief pedía rule **o** skill → rule.

7. **Canon**  
   Añadir regla R-FE (o bullet normativo) en `frontend.md` § formularios + ejemplo en
   `templates-frontend.md`. Esto **amplía** el estándar con el patrón del change (no es
   excepción). Sin sección "Excepciones a standards".

8. **E2E**  
   No aplica task rol 40 nueva. Si un browser Auth rellena por `name`/`id`, debe seguir
   funcionando con el Input del kit (mismos atributos).

## Risks / Trade-offs

- [shadcn no inicializado en repo] → Mitigación: task de añadir `input` (+ deps `cn`/cva
  si faltan) siguiendo `libraries.md` / agent-setup; sin kits alternativos.
- [Falsos positivos del arch test en checkbox/file] → Mitigación: whitelist de types
  excluidos (Decisión 2).
- [Password managers / autofill vs maxLength] → Mitigación: 255 es amplio; no acortar
  passwords por debajo de lo que `Password::defaults()` permita crear.

## Migration Plan

- Deploy solo código + docs + rule + tests. Sin migración BD.
- Rollback: revertir change; Auth vuelve a inputs crudos.

## Open Questions

- Ninguna (cerradas en explore + decisiones de este design).
