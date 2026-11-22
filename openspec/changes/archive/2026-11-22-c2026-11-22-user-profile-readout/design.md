# Design

## Context

La ficha rápida es `UserProfileReadout`, usada en el tab Perfil de
`Pages/Users/Edit` (rutas `/admin/profile` y company-users edit). El payload lo
arma `UserEditPageData`. Ya existen `lib/clipboard.ts` y toast
`email_copiado` en el panel de emails. Brief:
`docs/prompts/c2026-11-22-user-profile-readout.md`.

Sin ficha de módulo nueva. E2E §4: N/A. V-01..V-20 entidad nueva: N/A.

## Goals / Non-Goals

**Goals:**

- Readout sin NIF / fecha / activo / perfil público.
- Emails: solo address + copy (clipboard + toast); sin description/label.
- Teléfonos: mismo markup/comportamiento actual.
- Prop `companies: { id, name }[]`; sección solo si `length > 0`; nombre
  visible; sin `href` navegable (placeholder tipado listo para RO futura).
- Ampliar `UserEditPageData` con companies del user.

**Non-Goals:**

- Formulario Perfil, tab Empresas, vista RO de company, rutas nuevas.

## Decisions

1. **Solo componente + props**  
   Cambios en `UserProfileReadout` y cableado desde `Edit.tsx`. El form del
   tab no se toca. Props `nif`/`birth_date`/`status`/`is_public` dejan de
   usarse en el readout (MAY eliminarse del type del readout para no
   mentir; el `user` del form sigue igual).

2. **Emails**  
   Render: fila flex `address` + botón icono `Copy` (lucide). `copyText` de
   `@/lib/clipboard` + `toast.success(t('email_copiado'))` (clave ya usada
   en `ContactsEmailsPanel`). No mostrar `description`/`label`.

3. **Companies en payload**  
   En `UserEditPageData::build`, cargar vínculos `user_companies` →
   `[{ id, name }]` ordenados por nombre. Misma lista en context profile y
  company (todas las companies vinculadas al user editado).  
   Alternativa descartada: solo company de sesión — el brief pide las
   vinculadas «en caso de haber alguna».

4. **Enlace vacío**  
   UI: `<span>` o elemento no ancla (sin `href`). Prop opcional futura
   `href?: string | null` en el item; hoy siempre `null`/omitido. No inventar
   ruta a `companies.edit` ni `company-directory.show`.

5. **Tests**  
   Feature: `UserEditPageData` / respuesta Inertia de profile o company-users
   edit incluye `companies` con nombres. Opcional: test de componente no
   obligatorio si el payload + revisión manual bastan; preferir assertion
   Inertia props.

## Risks / Trade-offs

- [Visibilidad de otras companies del user ante manager de una sola] →
  Mitigation: alineado al brief; el tab Empresas ya expone vínculos.
- [Props user del readout aún tipan nif/…] → Mitigation: limpiar el type del
  readout para evitar uso accidental.

## Migration Plan

1. Payload companies.  
2. UI readout.  
3. Cableado Edit + i18n sección.  
4. Test props. Sin BD.

## Open Questions

Ninguna bloqueante.
