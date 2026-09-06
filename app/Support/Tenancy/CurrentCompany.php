<?php

namespace App\Support\Tenancy;

/**
 * Empresa activa de la request. Única fuente de tenant (R-TEN-03).
 */
final class CurrentCompany
{
    private ?int $id = null;

    public function id(): ?int
    {
        return $this->id;
    }

    public function get(): ?int
    {
        return $this->id;
    }

    public function set(?int $id): void
    {
        $this->id = $id;
    }
}
