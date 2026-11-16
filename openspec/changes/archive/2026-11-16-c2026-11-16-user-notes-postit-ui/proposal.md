# Proposal

## Why

Los post-its del Dashboard ya listan Notes, pero el aspecto es una tarjeta
ámbar en grid, lejos del post-it clásico de v1 (`#ffc`, ~140×140, rotación,
sombra, fecha con hora). Hay que alinear la UI sin tocar el dominio Note.

## What Changes

- Restyling de post-its en `Dashboard.tsx` (CSS/Tailwind puro).
- Fecha del post-it con **hora** (además del día).
- Layout visual tipo v1: trash abajo-derecha; rotación ligera entre notas.
- Sin dependencia npm nueva.

## Capabilities

### New Capabilities

_(ninguna)_

### Modified Capabilities

- `user-notes`: requisito de post-its en Dashboard — look estilo v1 +
  fecha con hora; sin cambios de create/delete/modelo.

## Impact

- Front: `resources/js/Pages/Dashboard.tsx` (y estilos locales si hace
  falta).
- Sin cambios de API, migraciones, Policy ni Actions.
- Sin entradas nuevas en `libraries.md`.
