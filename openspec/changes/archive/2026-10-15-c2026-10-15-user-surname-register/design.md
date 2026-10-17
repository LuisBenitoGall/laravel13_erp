## Context

`c2026-10-09-auth-onboarding` ya entregó el registro en dos pasos. La columna
`users.surname` existe (`nullable`), pero el paso 1 solo pide `name`. Negocio exige
apellidos obligatorios en el alta. No hay ficha de módulo nueva: es un delta sobre
identidad de User / capacidad `auth-registration`.

## Goals / Non-Goals

**Goals:**

- Exigir y persistir `surname` en el registro self-service.
- UI: etiqueta «Apellidos», campo justo debajo de Nombre.
- Validación espejo front/back; tests Feature verdes; ajustar browser de registro si
  rellena el formulario.

**Non-Goals:**

- Migración `NOT NULL` o backfill de usuarios existentes/`null`.
- Pantallas de perfil, edición de User, listados o onboarding de Company.
- Cambios de tenancy, roles o menú.
- Nueva dependencia.

## Decisions

1. **Obligatoriedad solo en aplicación (registro)**  
   Columna sigue `nullable` (semillas / usuarios previos). El FormRequest de registro
   marca `surname` como `required`.  
   _Alternativa descartada:_ `NOT NULL` + backfill — coste y riesgo fuera de alcance.

2. **Naming**  
   Código/BD: `surname`. UI (es): «Apellidos». Añadir al glosario si falta.  
   _Alternativa descartada:_ `last_name` / `apellidos` como columna — el brief ya fijó
   `users.surname`.

3. **Flujo técnico**  
   Extender el pipeline existente sin Actions nuevas:
   `Register.tsx` → `RegisterUserRequest` → `RegisteredUserController` → `RegisterUser`
   → `User` (`$fillable`). Misma ruta `POST /register`.

4. **E2E**  
   `e2e.md` §4 no lista el registro como flujo crítico obligatorio. Sí existe browser
   de registro/onboarding: **ajustar** el test que rellena el formulario para incluir
   `surname`. No crear suite E2E nueva. Feature test cubre el rechazo sin surname.

5. **Vinculaciones V-01..V-20**  
   No aplican (sin entidad de negocio nueva, sin policy/menú/módulo). Declarado aquí.

6. **module-map**  
   Sin cambio de dependencias entre dominios.

## Risks / Trade-offs

- [Usuarios seed/`null` sin surname] → Mitigación: registro nuevo siempre exige valor;
  edición de perfil queda para un change futuro si hace falta completar datos.
- [Factory/tests sin surname] → Mitigación: actualizar factory y payloads de registro
  en la misma implementación.

## Migration Plan

- Deploy: solo código (sin migración). Rollback: revertir el change; columna no se toca.

## Open Questions

- Ninguna (cerrado en explore: apellido obligatorio).
