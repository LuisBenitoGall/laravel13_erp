## Context

Brief: `docs/prompts/c2026-10-26-form-kit-mvp.md`. Canon `frontend.md` §4 y
`libraries.md` (RHF+zod+shadcn; `date-fns`; WYSIWYG/Dropzone aplazados a “por módulo”).
Existe `Components/ui/input` con maxLength 255. Forms de dominio aún ad-hoc.

## Goals / Non-Goals

**Goals:** kit MVP Form/* + docs + una migración de referencia.

**Non-Goals:** Wysiwyg/Dropzone/LangTabs; FormActions/RecordEditLayout/LineItemsEditor;
Platform console; migrar todos los forms; Vitest suite nueva si no está en canon.

## Decisions

1. **Ubicación**  
   - Controles de dominio form: `resources/js/Components/Form/{Field,Input,Textarea,NumberInput,Select,DatePicker,Checkbox,Switch}.tsx`.  
   - Primativas shadcn faltantes: `Components/ui/*` (label, checkbox, switch, popover,
     textarea…) según necesidad.  
   - `Form/Input` reexporta/envuelve `ui/input` para API homogénea con `Field` (no romper
     R-FE-08 ni tests arch de maxLength).

2. **Field**  
   Props: `name`/`htmlFor`, `label`, `error?`, `help?`, `children`. Usa `Controller` o
   slot + `useFormContext` opcional; i18n de labels en el consumidor (`t(...)`), no
   hardcode en el kit.

3. **NumberInput**  
   `inputMode` decimal; prop `decimals?: number` (contexto venta 3 / coste 4 / contable 2
   documentado en JSDoc; el redondeo de negocio sigue en backend/`lib` — el control solo
   UX). Alternativa descartada: meter `line-calc` aquí.

4. **Select (combobox)**  
   Opciones locales `{ value, label }[]` + filtro de texto client-side (MUST).  
   API opcional `onSearch?: (q: string) => void` / `loading?` para remoto futuro **sin**
   endpoint en este change (local-first).  
   Implementación: Popover + lista filtrable (patrón shadcn Combobox) con radix vía
   copia shadcn; **no** añadir `cmdk` salvo que 20 lo necesite y lo registre en
   `libraries.md` en el mismo PR. Preferencia: sin `cmdk` si el filtro local basta.

5. **DatePicker**  
   MVP: control basado en `<input type="date">` (o equivalente accesible) envuelto en
   Field + parse/format con `date-fns` (canon R-TRV-06; instalar npm si falta).  
   Alternativa descartada: `react-day-picker` en este change (no está en libraries; el
   calendario rico puede llegar después sin bloquear Fase 0).

6. **Checkbox / Switch**  
   Primativas shadcn/radix; wrappers Form con RHF `Controller`.

7. **Migración de referencia**  
   Migrar **un** form: `Partners/Companies/components/CompanyForm.tsx` (campos texto +
   representativo). No Auth completo ni todos los Admin forms.  
   Alternativa descartada: solo Story aislada sin uso real — peor señal para 20.

8. **Docs**  
   Sección en `templates-frontend.md`: ejemplo mínimo RHF+zod+Field+Input+Select.  
   En `frontend.md` §4: marcar kit MVP como implementado / pendientes Wysiwyg etc.

9. **Tests / calidad**  
   - `npm run typecheck` + lint en verde.  
   - Mantener arch/CI de maxLength (Input).  
   - E2E N/A.  
   Sin introducir Vitest/RTL si no están en el proyecto/canon.

10. **Dependencias**  
    Asegurar `date-fns` en package si no está (ya canon). No tiptap/dropzone. Radix
    packages que shadcn copie = OK bajo fila shadcn de libraries.

## Risks / Trade-offs

- **[Trade-off]** Date nativo vs calendar UI — menos fancy, cero deuda de librería.
- **[Riesgo]** Select remoto malentendido como “hay que cablear API ya”. **Mitigación**:
  solo callback opcional; documentado.
- **[Riesgo]** Doble Input (`ui` vs `Form`). **Mitigación**: Form/Input thin wrapper;
  arch sigue apuntando a maxLength en la primitiva usada.

## Migration Plan

1. ui primitives + Form/Field + controles.  
2. Docs templates.  
3. Migrar CompanyForm.  
4. typecheck/lint.  
5. Rollback: componentes aislados; form se puede revertir solo.

## Open Questions

Ninguna bloqueante.

## Excepciones a standards

Ninguna.

## E2E

No aplica.
