<?php

use App\Support\Html\AllowedHtmlSanitizer;

beforeEach(function () {
    $this->sanitizer = new AllowedHtmlSanitizer;
});

it('conserva img con src http(s) o path relativo y alt', function () {
    $html = '<p>Foto <img src="/storage/gallery/a.png" alt="Logo"></p>';

    expect($this->sanitizer->sanitize($html))
        ->toContain('<img src="/storage/gallery/a.png" alt="Logo">')
        ->toContain('<p>');
});

it('conserva img self-closing con src https y alt vacío', function () {
    $html = '<p><img src="https://cdn.example.com/x.jpg" alt="" /></p>';

    expect($this->sanitizer->sanitize($html))
        ->toContain('<img src="https://cdn.example.com/x.jpg" alt="">');
});

it('elimina img con src javascript o data', function () {
    $js = $this->sanitizer->sanitize('<p>Safe <img src="javascript:alert(1)" alt="x"></p>');
    $data = $this->sanitizer->sanitize('<p>Safe <img src="data:image/png;base64,xxx" alt="x"></p>');

    expect($js)->toBeString()->not->toContain('<img');
    expect($js)->not->toContain('javascript:');
    expect($data)->toBeString()->not->toContain('<img');
    expect($data)->not->toContain('data:');
});

it('elimina img con src protocol-relative //', function () {
    $clean = $this->sanitizer->sanitize('<p>Safe <img src="//evil.example/x.png" alt="x"></p>');

    expect($clean)->toBeString()->not->toContain('<img')
        ->and($clean)->not->toContain('//evil.example');
});

it('elimina img sin src o con src no path absoluto', function () {
    expect($this->sanitizer->sanitize('<p>Safe <img alt="solo"></p>'))
        ->toBeString()
        ->not->toContain('<img');
    expect($this->sanitizer->sanitize('<p>Safe <img src="relative.png" alt="x"></p>'))
        ->toBeString()
        ->not->toContain('<img');
});

it('conserva tags previos allowlist tras ampliar img', function () {
    $html = '<p>Hola <strong>mundo</strong> <em>y</em></p><ul><li>uno</li></ul><a href="https://example.com">link</a>';

    $clean = $this->sanitizer->sanitize($html);

    expect($clean)->toContain('<strong>mundo</strong>')
        ->and($clean)->toContain('<em>y</em>')
        ->and($clean)->toContain('<ul>')
        ->and($clean)->toContain('<li>')
        ->and($clean)->toContain('<a href="https://example.com">');
});

it('sanitize null y vacío sigue null', function () {
    expect($this->sanitizer->sanitize(null))->toBeNull()
        ->and($this->sanitizer->sanitize(''))->toBeNull()
        ->and($this->sanitizer->sanitize('<p></p>'))->toBeNull();
});
