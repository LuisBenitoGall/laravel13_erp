---
name: form-layout-polish
description: >-
  MUST activate automatically whenever creating, editing, or reviewing Inertia/React
  form UI in this ERP: Pages Create/Edit, *Form*.tsx, Field/FormHelpScope/HelpTip,
  Switch/Checkbox rows, RecordEditLayout forms, or any layout of labels/inputs.
  Covers responsive column vs full-width field placement, HelpTip alignment
  (tipAlign), fieldKey derive, and visual polish. Also when the user mentions
  maquetación, alineación, columnas, grid, descuadre, or “se ve mal” in a form.
---

# Form layout polish (ERP v2)

Complementa `inertia-react-development` y `docs/standards/frontend.md`. No
sustituye i18n ni Policies: **criterio de layout** de formularios + micro-alineación
HelpTip.

**Obligatorio:** al tocar cualquier vista de formulario, aplicar esta skill de
cabo a rabo (grid + tips + fieldKey). No esperar a que el usuario la nombre.

## 1. Columna completa vs varias en línea (criterio)

Mobile primero: **un campo por línea**. Desde `md:` (tablet+) compactar campos
cortos en 2–3 columnas para no alargar el scroll sin necesidad.

### Ancho completo (siempre 1 columna)

Campos de valor largo o lectura densa:

- Dirección / calle / línea de dirección
- Email (salvo que el diseño pida pareja con teléfono)
- URL, observaciones, descripción, excerpt
- Textarea / Wysiwyg / LangTabs de texto largo
- Selectores “ancho de negocio” (empresa, módulo) si el label+valor es largo
- Cualquier control que con 2 columnas quede ilegible o truncado

Patrón:

```tsx
<Field label={t('direccion')} htmlFor="…-address" fieldKey="address">
  <Input id="…-address" … />
</Field>
```

### Compactos en línea (desktop/tablet)

Campos cortos que caben juntos. Usar `grid` responsive:

```tsx
<div className="grid grid-cols-1 gap-4 md:grid-cols-2">
  {/* 2 campos cortos */}
</div>

<div className="grid grid-cols-1 gap-4 md:grid-cols-3">
  {/* 3 campos muy cortos */}
</div>
```

| Densidad | Breakpoint sugerido | Ejemplos |
|---|---|---|
| 2 en línea | `md:grid-cols-2` | Nombre + Apellidos; NIF + Fecha; Código + Símbolo; Provincia + Población (si cortos) |
| 3 en línea | `md:grid-cols-3` | CP + Ciudad corta + País corto; series de códigos/números muy breves |
| Booleanos en fila | `flex flex-wrap … gap-x-8` | Activo + Perfil público (cada uno en `Field` `w-auto`) |

**Heurística de longitud (contenido esperado del valor):**

- ≤ ~12–20 caracteres típicos (CP, NIF, código ISO, símbolo, número corto) → candidato a compartir fila (2 o 3).
- Nombre de persona / apellido → normalmente **2** en `md:`.
- Email, dirección, textos libres → **1** columna.
- En duda en tablet: preferir `md:grid-cols-2` antes que 3; subir a 3 solo si los tres son claramente cortos.

**No** poner en la misma fila un campo full-width con uno corto “para rellenar hueco”: o todos los de la fila son compactos, o el largo va solo.

### Mobile

- Base del grid: `grid-cols-1` (cada Field apilado).
- No usar `grid-cols-2` sin prefijo `md:` / `lg:` en formularios de ficha.

## 2. Checklist HelpTip / Field (cada form)

1. Envolver el form con `FormHelpScope value="{scope}"`.
2. `fieldKey` explícito si el `id`/`htmlFor` no coincide con el nombre del campo
   (`user-is-public` → `fieldKey="is_public"`).
3. `tipAlign`:
   - Input / Select / DatePicker / Textarea → `start` (default).
   - Switch / Checkbox → `center`.
4. Sin doble label: control dentro de `Field` → no pasar `label` al Switch/Checkbox.
5. HelpTip visible solo si el Content Help Active tiene título, extracto o cuerpo.
6. Booleanos: fila `flex flex-wrap items-start gap-x-8 gap-y-3`; cada uno
   `Field` + `tipAlign="center"` + `className="w-auto"`.
7. Revisar alineación visual label / control / tip antes de cerrar.

## 3. Antipatrones

| Mal | Bien |
|---|---|
| Email y NIF en `md:grid-cols-2` dejando el email aplastado | Email full-width; NIF en fila con otro corto |
| `grid-cols-2` sin breakpoint (rompe móvil) | `grid-cols-1 md:grid-cols-2` |
| Tres campos largos en `md:grid-cols-3` | Uno por línea o rediseñar |
| Checkbox suelto + HelpTip a mano | `Field` + `fieldKey` + `tipAlign="center"` |
| Derive desde `user-is-public` → `users.public` | `fieldKey="is_public"` |

## 4. Referencias

- `Components/Form/Field.tsx` — `tipAlign`, derive HelpTip
- `Components/Form/FormHelpScope.tsx` / `HelpTip.tsx`
- Compactos 2 cols: `Pages/Users/Edit.tsx`, `Pages/Admin/CompanyUsers/Create.tsx`
- Full-width + tips: `Pages/Admin/Companies/components/CompanyForm.tsx`
