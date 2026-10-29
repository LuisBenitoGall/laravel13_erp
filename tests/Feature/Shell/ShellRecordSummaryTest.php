<?php

it('RecordSummary expone título, badge e identifier y tone status', function () {
    $path = resource_path('js/Components/Records/RecordSummary.tsx');
    $source = file_get_contents($path);

    expect(file_exists($path))->toBeTrue()
        ->and($source)->toContain('titleKey')
        ->and($source)->toContain('identifier')
        ->and($source)->toContain("type RecordSummaryFieldTone = 'default' | 'muted' | 'status'")
        ->and($source)->toContain("status: 'font-medium text-amber-700'")
        ->and($source)->toContain('data-testid="record-summary"')
        ->and($source)->toContain('data-testid="record-summary-identifier"')
        ->and($source)->not->toContain('totales')
        ->and($source)->not->toContain('Pencil');
});

it('auditRecordSummaryFields cubre created/updated at/by con labelKeys i18n', function () {
    $path = resource_path('js/lib/recordSummary.ts');
    $source = file_get_contents($path);

    expect(file_exists($path))->toBeTrue()
        ->and($source)->toContain('export function auditRecordSummaryFields')
        ->and($source)->toContain("labelKey: 'creado'")
        ->and($source)->toContain("labelKey: 'autor'")
        ->and($source)->toContain("labelKey: 'actualizado'")
        ->and($source)->toContain("labelKey: 'actualizado_por'");
});

it('claves i18n de auditoría existen en es/en/ca', function () {
    foreach (['es', 'en', 'ca'] as $locale) {
        $json = json_decode((string) file_get_contents(lang_path("{$locale}.json")), true);

        expect($json)->toHaveKeys(['creado', 'autor', 'actualizado', 'actualizado_por']);
    }
});
