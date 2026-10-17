## Context

Runtime i18n ya existe (`c2026-10-15-i18n-runtime`). Quedan ~160 claves con esquema
`dominio.entidad.*` en inglés. Brief + explore: pasar a índices castellanos reutilizables
como v1 `textos.php`. Código de dominio sigue en inglés (glosario); solo cambia la
**clave UI**.

Ficha de módulo / V-01..V-20 / module-map / R-TEN-05: **no aplican**.

## Goals / Non-Goals

**Goals:**

- Convención documentada y aplicada en todo el inventario actual.
- Diccionario más corto por deduplicación de genéricos.
- Estándares (glossary, R-FE-05, rule) alineados.
- CI que impida regresar a claves con puntos / inglés jerárquico.

**Non-Goals:**

- Reimplementar locale/selector/`users.locale`.
- Partir JSON por dominio.
- Portar los ~3900 textos de v1; solo migrar el inventario v2.
- E2E nuevo (`e2e.md` §4); ajustar Browser solo si rompen por cambio de texto visible
  (los valores ES suelen mantenerse).

## Decisions

1. **Forma de la clave**  
   - Castellano, `snake_case`, alfabeto `[a-z0-9_]` (sin tildes ni `ñ` en el índice).  
   - Sin puntos.  
   - Familias: `prefijo` + `_` + resto (`usuario_nuevo`, `empresa_editar`).  
   _Descartado:_ puntos `admin.modules.title`; claves en inglés.

2. **Genéricos vs específicos**  
   - Si el texto visible es el concepto genérico → clave genérica (`nombre`, `guardar`,
     `crear`, `editar`, `email`, `estado`, `slug`, `nif`, `contraseña` → `contrasena`).  
   - Si el texto es compuesto/distinto → clave explícita (`nombre_comercial`,
     `usuario_nombre`, `empresa_nueva`).  
   - Nunca `admin.functionalities.form.name` otra vez.

3. **Vocabulario genérico mínimo (inventario actual)**  
   Partir al menos de: `nombre`, `email`, `guardar`, `crear`, `editar`, `eliminar`,
   `continuar`, `salir`, `entrar`, `idioma`, `estado`, `slug`, `nif`, `rol`, `accion`,
   `contrasena`, `apellidos`, `modulo`, `etiqueta`, `nivel`, `color`, `icono`,
   `autorizar`, `revocar`, `activar`/`apagar` según copy real.  
   20-implementation completa el mapa clave-vieja → clave-nueva en la migración; este
   design fija las reglas, no el diccionario línea a línea.

4. **Familias del inventario actual (orientativas)**  
   - `auth_*` / frases de login-registro-onboarding (o prefijos `login_`, `registro_`,
     `empresa_` según semántica del copy — preferir genérico si el texto es genérico).  
   - `empresa_*` (partners companies, miembros, roles).  
   - `modulo_*` / `funcionalidad_*` (admin catálogo).  
   - `legal_*`, `inicio` / `panel_*`, `bienvenida_*` según copy.  
   Criterio: el prefijo nombra la **familia de UI**, no el path de código PHP.

5. **Valores**  
   - `es`: texto UI castellano (capitalización según diseño actual).  
   - `en` / `ca`: traducción del mismo significado.  
   - Interpolación i18next `{{name}}` se conserva donde ya exista.

6. **Migración mecánica**  
   1. Definir mapa old→new (tabla o script).  
   2. Reescribir los tres JSON (paridad + A–Z).  
   3. Sustituir literales de clave en TSX/PHP.  
   4. Actualizar `scripts/generate-i18n-json.php` o retirarlo si deja de ser fuente.  
   5. Tests verdes.  
   Prohibido dejar claves viejas “por compatibilidad”.

7. **Estándares**  
   - `glossary.md`: tachar/reemplazar la regla de claves i18n en inglés → castellano
     snake_case + genéricos/familias.  
   - `frontend.md` R-FE-05 + `.cursor/rules/i18n.mdc` + ejemplos en templates.  
   - `architecture.md` R-TRV-05: una línea de convención de clave.

8. **CI**  
   Mantener paridad y orden A–Z. Añadir: toda clave MUST matchear `^[a-z0-9_]+$`
   (falla si contiene `.` o mayúsculas).

9. **Housekeeping**  
   Si `c2026-10-15-i18n-runtime` sigue sin archivar, 30-testing lo archiva aparte; este
   change no lo re-propone.

10. **E2E**  
    No aplica task 40 nueva. Browser Auth: reejecutar/ajustar solo si fallan.

## Risks / Trade-offs

- [Choque temporal glossary vs código hasta merge] → Mitigación: standards + código en
  el mismo change.  
- [Duda genérico vs específico en un copy] → Mitigación: regla del brief; si ambigüedad,
  específica con familia; no inventar jerarquía inglesa.  
- [EN de v1 incompleto no aplica] → Solo cuidar en/ca del inventario v2.

## Migration Plan

- Solo código + JSON + docs. Rollback: revertir el change.  
- Sin migración BD.

## Open Questions

- Ninguna bloqueante (cerradas en explore).
