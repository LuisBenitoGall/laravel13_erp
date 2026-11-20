# Proposal

## Why

En Edit de Content Article, al guardar sin URLs de vídeo aparece
«El campo vídeo es obligatorio». El campo es opcional de producto
(array vacío `[]` válido); la regla `required` sobre `video` en Articles
lo bloquea. Hay que quitar la obligatoriedad y dejarlo normativo en el
spec.

## What Changes

- Quitar `video` required en update de Article; `video` queda opcional
  (nullable/array; ítems URL si hay valor; vacío = `[]`).
- Delta `contents-edit`: `video` SHALL NOT ser obligatorio nunca.
- Test Feature: guardar Article sin vídeo (o `video: []`) OK.

## Capabilities

### New Capabilities

_(ninguna)_

### Modified Capabilities

- `contents-edit`: video opcional en update (Article incluido).

## Impact

- `UpdateContentRequest` (+ tests Content).
- Spec + ficha Admin si menciona lo contrario.
