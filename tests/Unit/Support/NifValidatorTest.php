<?php

use App\Support\Nif\NifValidator;

it('acepta NIF NIE CIF EIN validos y vacio', function () {
    expect(NifValidator::isValid(null))->toBeTrue()
        ->and(NifValidator::isValid(''))->toBeTrue()
        ->and(NifValidator::isValid('12345678Z'))->toBeTrue()
        ->and(NifValidator::isValid('X1234567L'))->toBeTrue()
        ->and(NifValidator::isValid('12-3456789'))->toBeTrue();
});

it('rechaza formatos invalidos', function () {
    expect(NifValidator::isValid('B12345678'))->toBeFalse()
        ->and(NifValidator::isValid('12345678A'))->toBeFalse()
        ->and(NifValidator::isValid('INVALID'))->toBeFalse();
});
