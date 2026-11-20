<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-20-admin-gallery-crop-ux
Rule: 10-architecture
Summary: UX del modal de crop de Galería — ayuda de uso (pan+zoom) y
  selectores de ratio 1:1 / 4:3 / 16:9; se mantiene `react-easy-crop`.
Goals:
- Texto de ayuda visible en el modal: mover la imagen + zoom; la zona
  clara es lo que se guarda (no se redimensiona la rejilla con asas).
- Botones/selector de aspect ratio: **1:1**, **4:3**, **16:9**.
- Ratio opcional **libre** (aspect del contenedor / sin ratio fijo):
  incluido en la opción 2 debatida; 10 concreta el valor de `aspect`
  si hace falta precisión técnica.
- Seguir con `react-easy-crop` (decisión A); sin cambiar de librería.
NonGoals:
- Sustituir `react-easy-crop` por cropper con marco redimensionable.
- Fase 2–3 Wysiwyg / MediaPicker.
- Cambiar contrato POST `…/copies`, herencia name/alt u original intacto.
- Campos numéricos ancho×alto / `cropSize` manual (opción C descartada).
Skills:
- openspec-propose
- inertia-react-development
Notes:
- Origen: `/opsx-explore`. Luis no podía “usar la rejilla”; causa =
  modelo de la lib (pan+zoom, no resize de caja). Eligió **A** +
  variante **2** (ayuda + ratios).
- i18n es/en/ca para ayuda y labels de ratios.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.
- Tras brief: usuario lanza 10-architecture en sesión nueva.


## ADDED Requirements

### Requirement: Ayuda de uso en modal crop
El modal de recorte de Galería SHALL mostrar ayuda i18n que indique que
el actor mueve la imagen y usa el zoom, y que la zona clara es el área
que se guarda.

#### Scenario: Actor abre recortar
- **WHEN** el actor abre el modal de recorte
- **THEN** ve el texto de ayuda de pan + zoom

### Requirement: Selectores de aspect ratio
El modal SHALL permitir elegir al menos los ratios 1:1, 4:3 y 16:9
(y libre si 10 lo incluye) aplicando el `aspect` correspondiente en
`react-easy-crop` sin cambiar de librería.

#### Scenario: Cambiar a 1:1
- **WHEN** el actor selecciona ratio 1:1
- **THEN** la zona de crop usa aspect 1:1


## MODIFIED Requirements

_(ninguno obligatorio — 10 puede delta sobre admin-gallery Index/crop
si hace falta; no ampliar alcance)_
