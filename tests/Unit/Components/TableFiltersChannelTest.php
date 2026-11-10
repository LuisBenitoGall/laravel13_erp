<?php

use Illuminate\Support\Facades\Process;

/**
 * @return array<string, string>
 */
function tableFilterChannelUrls(): array
{
    $result = Process::path(base_path())->run([
        'node',
        '--import',
        './tests/js/register-strip-types.mjs',
        'tests/js/printTableFilterPayload.ts',
    ]);

    expect($result->successful())->toBeTrue($result->errorOutput().$result->output());

    $decoded = json_decode($result->output(), true);

    expect($decoded)->toBeArray();

    /** @var array<string, string> $decoded */
    return $decoded;
}

/**
 * @return array<string, mixed>|null
 */
function tableFilterPayload(string $url): ?array
{
    $query = parse_url($url, PHP_URL_QUERY);
    parse_str(is_string($query) ? $query : '', $params);

    if (! isset($params['filter']) || ! is_string($params['filter'])) {
        return null;
    }

    $payload = json_decode($params['filter'], true);

    return is_array($payload) ? $payload : null;
}

it('envía cabecera y modal en el query filter cuando el modo es merge', function () {
    $urls = tableFilterChannelUrls();

    expect(tableFilterPayload($urls['merge']))->toBe([
        ['field' => 'status', 'type' => '=', 'value' => 'active'],
        ['field' => 'name', 'type' => 'like', 'value' => 'viejo'],
        ['field' => 'name', 'type' => 'like', 'value' => 'acme'],
    ]);
});

it('deja solo los filtros del modal en el query filter cuando el modo es replace', function () {
    $urls = tableFilterChannelUrls();

    expect(tableFilterPayload($urls['replace']))->toBe([
        ['field' => 'name', 'type' => 'like', 'value' => 'acme'],
    ]);
});

it('reenvía el sort activo en la descarga Excel', function () {
    $urls = tableFilterChannelUrls();
    $query = parse_url($urls['exportSorted'], PHP_URL_QUERY);
    parse_str(is_string($query) ? $query : '', $params);

    expect($params['export'] ?? null)->toBe('xlsx')
        ->and($params['sort'] ?? null)->toBe('[{"field":"name","dir":"desc"}]')
        ->and(array_key_exists('page', $params))->toBeFalse();

    $table = (string) file_get_contents(resource_path('js/Components/Table/ServerTable.tsx'));

    expect($table)->toContain('sortRef.current = sort')
        ->and($table)->toContain('sort: sortRef.current');
});

it('reenvía el mismo filter en la descarga Excel', function () {
    $urls = tableFilterChannelUrls();
    $query = parse_url($urls['export'], PHP_URL_QUERY);
    parse_str(is_string($query) ? $query : '', $params);

    expect(tableFilterPayload($urls['export']))->toBe([
        ['field' => 'status', 'type' => '=', 'value' => 'active'],
        ['field' => 'name', 'type' => 'like', 'value' => 'viejo'],
        ['field' => 'name', 'type' => 'like', 'value' => 'acme'],
    ])
        ->and($params['export'] ?? null)->toBe('xlsx')
        ->and(array_key_exists('page', $params))->toBeFalse();
});

it('no añade el modal al query filter mientras advancedFilters sigue undefined', function () {
    $urls = tableFilterChannelUrls();

    expect(tableFilterPayload($urls['headerOnly']))->toBe([
        ['field' => 'status', 'type' => '=', 'value' => 'active'],
        ['field' => 'name', 'type' => 'like', 'value' => 'viejo'],
    ]);
});

it('al reemplazar con una lista vacía el endpoint table no lleva filter', function () {
    $urls = tableFilterChannelUrls();

    expect(tableFilterPayload($urls['replaceEmpty']))->toBeNull()
        ->and($urls['replaceEmpty'])->not->toContain('filter=');
});

it('no manda un filtro del modal con valor vacío', function () {
    $urls = tableFilterChannelUrls();

    expect(tableFilterPayload($urls['droppedEmpty']))->toBe([
        ['field' => 'status', 'type' => '=', 'value' => 'active'],
        ['field' => 'name', 'type' => 'like', 'value' => 'viejo'],
    ]);
});

it('ServerTable y el modal usan el canal filter del modal', function () {
    $table = (string) file_get_contents(resource_path('js/Components/Table/ServerTable.tsx'));
    $modal = (string) file_get_contents(resource_path('js/Components/Table/AdvancedSearchModal.tsx'));

    expect($table)->toContain('tableFiltersForRequest')
        ->and($table)->toContain('buildTableEndpointUrl')
        ->and($table)->toContain('advancedFilterMode')
        ->and($table)->toContain('exportFormat: format')
        ->and($modal)->toContain('onApply(current)');
});
