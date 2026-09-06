<?php

namespace App\Domains\Admin\ValueObjects;

/**
 * Número de documento emitido por NumberingService (R-BD-05).
 */
final readonly class DocumentNumber
{
    public function __construct(
        public string $docType,
        public int $sequence,
        public string $formatted,
    ) {}

    public function __toString(): string
    {
        return $this->formatted;
    }
}
