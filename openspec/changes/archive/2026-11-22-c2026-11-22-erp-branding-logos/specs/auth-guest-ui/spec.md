# Spec Delta

## MODIFIED Requirements

### Requirement: Panel deslizante Login / Register
El sistema SHALL presentar Login y Register fuera del Admin como un
único panel con transición deslizante entre modos Entrar y Registrarse,
usando la marca y colores del ERP (primary Coopmercio `#10A37F` y tokens
de marca asociados). Las rutas `/login` y `/register` SHALL seguir siendo
válidas y determinar el modo inicial. SHALL respetar
`prefers-reduced-motion` (transición reducida o ausente).

#### Scenario: Abrir login
- **WHEN** el visitante abre `/login`
- **THEN** ve el panel en modo Entrar con opción de pasar a Registrarse
  mediante la transición del panel

#### Scenario: Abrir register
- **WHEN** el visitante abre `/register`
- **THEN** ve el panel en modo Registrarse con opción de pasar a Entrar
  mediante la transición del panel

#### Scenario: Enviar formularios
- **WHEN** el visitante completa y envía Entrar o Registrarse
- **THEN** se procesa igual que hoy vía las rutas de auth existentes

#### Scenario: Reduced motion
- **WHEN** el usuario tiene preferencia de movimiento reducido
- **THEN** el cambio de modo no depende de una animación de
  desplazamiento obligatoria

#### Scenario: Color de marca en panel
- **WHEN** el visitante ve el panel deslizante Login/Register
- **THEN** el acento cromático del panel usa el primary de marca
  Coopmercio (`#10A37F`), no el teal genérico previo
