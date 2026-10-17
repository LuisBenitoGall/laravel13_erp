## 1. Datos / naming

- [x] 1.1 Confirmar que no hace falta migración (`users.surname` ya existe nullable)
- [x] 1.2 Añadir `surname` / Apellidos al glosario (`docs/standards/glossary.md`) si falta

## 2. Backend

- [x] 2.1 Incluir `surname` en `$fillable` de `User`
- [x] 2.2 Añadir regla `surname` → `required|string|max:255` en `RegisterUserRequest`
- [x] 2.3 Persistir `surname` en `RegisterUser` (actualizar phpdoc del payload)
- [x] 2.4 Pasar `surname` en `RegisteredUserController` (`safe()->only([...])`)

## 3. Frontend

- [x] 3.1 Añadir campo Apellidos (`surname`) en `Register.tsx` justo debajo de Nombre
- [x] 3.2 Incluir `surname` en `useForm` / payload del POST `/register`

## 4. Tests

- [x] 4.1 Actualizar `RegisterTest`: happy path con `surname` y assert de persistencia
- [x] 4.2 Añadir caso Feature: registro sin `surname` → error de validación, sin User
- [x] 4.3 Actualizar demás posts de registro en Feature que omitan `surname`
- [x] 4.4 Ajustar `RegisterOnboardEntersAppTest` (browser) para rellenar `surname`
- [x] 4.5 R-TEN-05: no aplica (User no es entidad `BelongsToCompany`)
- [x] 4.6 Vinculaciones V-01..V-20: no aplican (declarado en design)

## 5. Calidad

- [x] 5.1 `vendor/bin/pint` / PHPStan en archivos tocados
- [x] 5.2 Suite relevante verde (`RegisterTest` + browser de registro si se ejecuta)
